<?php
/**
 *	Process the settings as they are saved.
 *
 *	@package Ultimo WooProType
 *	@author Ultimo Cms Booster
 *	@since 1.0
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UwooMP_Process_Settings' ) ) :

class UwooMP_Process_Settings {

	public function __construct() {

		$this->hooks();
	}

	/**
	 *	Run
	 */
	public function hooks() {

		add_action( 'admin_init', array( $this, 'process_settings' ) );
	}

	/**
	 *	Process the settings
	 */
	public function process_settings() {

		// Query arg not set or not correct
		if ( ! isset( $_POST['uwoomp'] ) ) {
			return;
		}

		// No administrative privileges
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->no_permission_error();
		}

		// Required HTTP referer
		$required_referer = add_query_arg( 'page', 'uwoomp', parse_url( UWOOMP_SETTINGS_PAGE_URL, PHP_URL_PATH ) );

		if ( ! isset( $_POST['uwoomp_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['uwoomp_admin_nonce'])), 'uwoomp_admin_nonce' ) ) {
			$this->no_permission_error();
		}

		// Get our settings
		$options = uwoomp_get_settings();

		// Module settings were updated
		if ( is_array($_POST['uwoomp']['tools']) ) {

			unset( $_POST['uwoomp']['tools']['triggered'] );

			$new = array();
			$cleaned_tools_values = array();
			$tools_array = isset( $_POST['uwoomp']['tools'] ) ? wp_unslash($_POST['uwoomp']['tools']) : array();
			$all_tools = array_map( 'sanitize_text_field', $tools_array );
			// Modules are enabled, so sanitize the input values
			if ( !empty($all_tools) ) {
				foreach ( (array) $all_tools as $key => $val ) {
					$tool_name = sanitize_text_field($key);
					$cleaned_tools_values[$tool_name] = $val ? 1 : '';
				}
			}

			/**
			 *	Array of enabled tools or empty array
			 */
			$new['tools'] = $cleaned_tools_values;

			// Merge new tools array with other settings, and update option
			update_option( 'uwoomp', array_merge( $options, $new ) );

			// Redirect
			$this->save_redirect();
		}
	}

	/**
	 *	Error message
	 */
	private function no_permission_error() {
		wp_die( __( 'Error.', 'ultimo-woomultipay' ) );
	}

	/**
	 *	Redirect
	 */
	private function save_redirect() {
		wp_redirect( add_query_arg( array(
			'settings-updated' => 'true'
		), UWOOMP_SETTINGS_PAGE_URL ) );
		exit;
	}
}

endif;

new UwooMP_Process_Settings;