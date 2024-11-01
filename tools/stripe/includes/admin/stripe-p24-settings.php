<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$webhook_url = WC_Stripe_Helper::get_webhook_url();

return apply_filters( 'wc_stripe_p24_settings',
	array(
		'geo_target' => array(
			'description' => __( 'Relevant Payer Geography: Poland', 'ultimo-woomultipay' ),
			'type'        => 'title',
		),
		'activation' => array(
			'description' => __( 'Must be activated from your Stripe Dashboard Settings <a href="https://dashboard.stripe.com/account/payments/settings" target="_blank">here</a>', 'ultimo-woomultipay' ),
			'type'   => 'title',
		),
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'ultimo-woomultipay' ),
			'label'       => __( 'Enable Stripe P24', 'ultimo-woomultipay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title' => array(
			'title'       => __( 'Title', 'ultimo-woomultipay' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'ultimo-woomultipay' ),
			'default'     => __( 'Przelewy24 (P24)', 'ultimo-woomultipay' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'ultimo-woomultipay' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'ultimo-woomultipay' ),
			'default'     => __( 'You will be redirected to P24.', 'ultimo-woomultipay' ),
			'desc_tip'    => true,
		),
		'webhook' => array(
			'title'       => __( 'Webhook Endpoints', 'ultimo-woomultipay' ),
			'type'        => 'title',
			/* translators: webhook URL */
			'description' => sprintf( __( 'You must add the webhook endpoint <strong style="background-color:#ddd;">&nbsp;&nbsp;%s&nbsp;&nbsp;</strong> to your Stripe Account Settings <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Here</a> so you can receive notifications on the charge statuses.', 'ultimo-woomultipay' ), $webhook_url ),
		),
	)
);
