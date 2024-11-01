<?php

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	Tool sections
 *	@package Ultimo WooMultiPay
 *	@author Ultimo Cms Booster
 *
 *	@return array
 */
function uwoomp_get_tool_sections() {

	$sections = array(

		'payment_gateways' => array( # Payment gateways

			'section_title' => __( 'Payment Method Tools', 'ultimo-woomultipay' ),
			'section_description' => __( 'Integrate payment gateways to accept payment through various providers, such as Stripe and Braintree.', 'ultimo-woomultipay' ),
			'section_tools' => array(
				array(

					'title' => __( 'Authorize.net AIM', 'ultimo-woomultipay' ),
					'key' => 'authorize_net_aim',
					'include_path' => '/authorize-net-aim/woocommerce-gateway-authorize-net-aim.php',
				),
				array(

					'title' => __( 'Braintree', 'ultimo-woomultipay' ),
					'key' => 'braintree',
					'include_path' => '/braintree/woocommerce-gateway-braintree.php',
				),
				array(

					'title' => __( 'Stripe', 'ultimo-woomultipay' ),
					'key' => 'stripe',
					'include_path' => '/stripe/woocommerce-gateway-stripe.php',
				),
				array(

					'title' => __( '2Checkout', 'ultimo-woomultipay' ),
					'key' => '2checkout',
					'include_path' => '/2checkout/gateway-2checkout.php',
				),
				array(

					'title' => __( 'Account Funds', 'ultimo-woomultipay' ),
					'key' => 'account_funds',
					'include_path' => '/account-funds/woocommerce-account-funds.php',
				),
				array(

					'title' => __( 'Deposits', 'ultimo-woomultipay' ),
					'key' => 'deposits',
					'include_path' => '/deposits/woocommmerce-deposits.php',
				),
				array(

					'title' => __( 'Intuit Quickbooks', 'ultimo-woomultipay' ),
					'key' => 'qbms',
					'include_path' => '/intuit-qbms/woocommerce-gateway-intuit-qbms.php',
				),
				array(

					'title' => __( 'PayPal Digital Goods', 'ultimo-woomultipay' ),
					'key' => 'paypal_digital_goods',
					'include_path' => '/paypal-digital-goods/gateway-paypal-digital-goods.php',
				),
				array(

					'title' => __( 'PayPal Express', 'ultimo-woomultipay' ),
					'key' => 'paypal_express',
					'include_path' => '/paypal-express/woocommerce-gateway-paypal-express.php',
				),
				array(

					'title' => __( 'PayPal Pro', 'ultimo-woomultipay' ),
					'key' => 'paypal_pro',
					'include_path' => '/paypal-pro/woocommerce-gateway-paypal-pro.php',
				),
				array(

					'title' => __( 'Purchase Order', 'ultimo-woomultipay' ),
					'key' => 'purchase_order',
					'include_path' => '/purchase-order/woocommerce-gateway-purchase-order.php',
				),
			)
		),
		);

	return $sections;
}

/**
 *	Get a list of just the tools, without sections
 *
 *	@return array
 */
function uwoomp_get_all_tools() {

	$all_tools = array();
	$sections = uwoomp_get_tool_sections();

	foreach ( $sections as $section ) {
		foreach ( $section['section_tools'] as $section_tool ) {
			$all_tools[$section_tool['key']] = $section_tool['title'];
		}
	}

	return $all_tools;
}