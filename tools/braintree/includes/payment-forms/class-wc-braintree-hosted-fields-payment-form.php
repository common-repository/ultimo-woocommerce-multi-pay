<?php
/**
 * WooCommerce Braintree Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woothemes.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway/Payment-Form/Hosted-Fields
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Hosted Fields Payment Form
 *
 * @since 3.0.0
 */
class WC_Braintree_Hosted_Fields_Payment_Form extends WC_Braintree_Payment_Form {


	/**
	 * Override the standard payment method HTML to add a nonce to the associated
	 * token so it can be used for 3D Secure verification. This is added as a
	 * data-nonce attribute.
	 *
	 * @since 3.0.0
	 * @param \WC_Braintree_Payment_Method $token payment token
	 * @return string saved payment method HTML
	 */
	protected function get_saved_payment_method_html( $token ) {

		$html = parent::get_saved_payment_method_html( $token );

		if ( $this->get_gateway()->is_3d_secure_enabled() && in_array( $token->get_card_type(), array( 'visa', 'mc' ) ) && ! SV_WC_Helper::str_exists( $html, 'data-nonce' ) ) {

			if ( $nonce = $this->get_gateway()->get_3d_secure_nonce_for_token( $token ) ) {

				$html = str_replace( 'name="wc-braintree-credit-card-payment-token"', 'name="wc-braintree-credit-card-payment-token" data-nonce="' . $nonce . '"', $html );
			}
		}

		return $html;
	}


	/**
	 * Override the default form fields to add hosted field specific classes
	 *
	 * @since 3.0.0
	 * @return array credit card form fields
	 */
	protected function get_credit_card_fields() {

		$fields = parent::get_credit_card_fields();

		foreach ( array( 'card-number', 'card-expiry', 'card-csc' ) as $field_key ) {

			if ( isset( $fields[ $field_key ] ) ) {

				// parent div classes - contains both the label and hosted field container div
				$fields[ $field_key ]['class'] = array_merge( $fields[ $field_key ]['class'], array( "wc-braintree-hosted-field-{$field_key}-parent", 'wc-braintree-hosted-field-parent' ) );

				// hosted field container classes - contains the iframe element
				$fields[ $field_key ]['input_class'] = array_merge( $fields[ $field_key ]['input_class'], array( "wc-braintree-hosted-field-{$field_key}", 'wc-braintree-hosted-field' ) );
			}
		}

		// adjust expiry date label
		$fields['card-expiry']['label'] = esc_html__( 'Expiration (MMYY)', 'ultimo-woomultipay' );

		return $fields;
	}


	/**
	 * Render a custom payment field. This essentially replaces <input> types
	 * usually generated by woocommerce_form_field() with <div>s that are required
	 * by Braintree's hosted field implementation
	 *
	 * @since 3.0.0
	 * @param array $field
	 */
	public function render_payment_field( $field ) {

		?>
		<div class="form-row <?php echo implode( ' ', array_map( 'sanitize_html_class', $field['class'] ) ); ?>">
			<label for="<?php echo esc_attr( $field['id'] ) . '-hosted'; ?>"><?php echo esc_html( $field['label'] ); if ( $field['required'] ) : ?><abbr class="required" title="required">&nbsp;*</abbr><?php endif; ?></label>
			<div id="<?php echo esc_attr( $field['id'] ) . '-hosted'; ?>" class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $field['input_class'] ) ); ?>"></div>
		</div>
		<?php
	}


	/**
	 * Return the JS params passed to the the payment form handler script
	 *
	 * @since 3.0.0
	 * @see WC_Braintree_Payment_Form::get_payment_form_handler_js_params()
	 * @return array
	 */
	protected function get_payment_form_handler_js_params() {

		return array(
			'csc_required' => $this->get_gateway()->is_csc_required(),
			'threeds'      => array(
				'enabled'                         => is_add_payment_method_page() ? false : $this->get_gateway()->is_3d_secure_enabled(),
				'liability_shift_always_required' => $this->get_gateway()->is_3d_secure_liability_shift_always_required(),
				'failure_message'                 => __( 'We cannot process your order with the payment information that you provided. Please use an alternate payment method.', 'ultimo-woomultipay' ),
			),
			'hosted_fields_styles' => $this->get_hosted_fields_styles(),
		);
	}


	/**
	 * Get the hosted fields styles in an associative array. These are used
	 * by the Braintree SDK to apply to the inputs inside the hosted field iframes.
	 *
	 * @link https://developers.braintreepayments.com/guides/hosted-fields/styling/javascript/v2
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_hosted_fields_styles() {

		$styles = array(
			'input' => array(
				'font-size' => '1.3em',
			)
		);

		/**
		 * Braintree Credit Card Hosted Fields Styles Filter.
		 *
		 * Allow actors to set the styles used for the hosted fields. See the link
		 * above for the exact format required.
		 *
		 * @since 3.0.0
		 * @param array $styles
		 * @param \WC_Braintree_Hosted_Fields_Payment_Form $this instance
		 * @return array
		 */
		return apply_filters( 'wc_' . $this->get_gateway()->get_id() . '_hosted_fields_styles', $styles, $this );
	}


	/**
	 * Render hidden inputs for the handling 3D Secure transactions:
	 *
	 * 1) the order total, which is needed during the verification process client-side.
	 * While this can be modified client-side, the order total is later verified server-side.
	 *
	 * 2) a flag to indicate client-side verification passed and server-side validations
	 * should be performed
	 *
	 * @since 3.0.0
	 */
	public function render_payment_fields() {

		if ( $this->get_gateway()->is_3d_secure_enabled() ) {
			?>
				<input type="hidden" name="wc_braintree_credit_card_3d_secure_order_total" value="<?php echo esc_attr( SV_WC_Helper::number_format( WC()->cart->total, 2 ) ); ?>" />
				<input type="hidden" name="wc_braintree_credit_card_3d_secure_verified" value="0" />
			<?php
		}

		parent::render_payment_fields();
	}


}
