<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$webhook_url = WC_Stripe_Helper::get_webhook_url();

return apply_filters( 'wc_stripe_bitcoin_settings',
	array(
		'geo_target' => array(
			'description' => __( 'Relevant Payer Geography: Global', 'ultimo-woomultipay' ),
			'type'        => 'title',
		),
		'guide' => array(
			'description' => __( '<a href="https://stripe.com/payments/payment-methods-guide#bitcoin" target="_blank">Payment Method Guide</a>', 'ultimo-woomultipay' ),
			'type'        => 'title',
		),
		'activation' => array(
			'description' => __( 'Must be activated from your Stripe Dashboard Settings <a href="https://dashboard.stripe.com/account/payments/settings" target="_blank">here</a>', 'ultimo-woomultipay' ),
			'type'   => 'title',
		),
		'enabled' => array(
			'title'       => __( 'Enable/Disable', 'ultimo-woomultipay' ),
			'label'       => __( 'Enable Stripe Bitcoin', 'ultimo-woomultipay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title' => array(
			'title'       => __( 'Title', 'ultimo-woomultipay' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'ultimo-woomultipay' ),
			'default'     => __( 'Bitcoin', 'ultimo-woomultipay' ),
			'desc_tip'    => true,
		),
		'description' => array(
			'title'       => __( 'Description', 'ultimo-woomultipay' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'ultimo-woomultipay' ),
			'default'     => __( 'Bitcoin payment information will be provided when you place the order.', 'ultimo-woomultipay' ),
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
