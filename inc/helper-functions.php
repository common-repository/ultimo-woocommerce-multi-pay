<?php
/**
 *	Helper functions
 *	@package Ultimo WooMultiPay
 *	@author Ultimo Cms Booster
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	WooCommerce dependencies
 */
if ( ! class_exists( 'WC_Dependencies' ) ) {
	require_once 'class-wc-dependencies.php';
}

/**
 *	WC Detection
 *	@return (boolean) True if WooCommerce is active, else false
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_Dependencies::woocommerce_active_check();
	}
}

/**
 *	Get the UwooMP settings
 *	@return (array) Plugin settings or empty array
 */
function uwoomp_get_settings() {

	$options = get_option( 'uwoomp' );

	if ( ! is_array( $options )  ) {
		$options = array();
	}
	
	return $options;
}

/**
 *	Get the tools settings
 *	@return (array) Tools settings or empty array
 */
function uwoomp_get_tools_settings() {

	$options = uwoomp_get_settings();

	if ( $options['tools'] ) {
		return $options['tools'];
	} else {
		return array();
	}
}