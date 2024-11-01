<?php
/**
 * Plugin Name: Woocommerce Multi Pay Master
 * Plugin URI: https://easycmspro.com/item/woocommerce-multipay/
 * Description: Best plugin to make your Woocommerce powered shop easily integrated with various Payment Gateways like authorize.net AIM, Braintree and stripe
 * Version: 1.0
 * Author: Easycmspro
 * Author URI: https://easycmspro.com/
 * Text Domain: ultimo-woomultipay
 * Domain Path: /languages/
 *
 * WC tested up to: 5.x
 *
 * License: GPL 2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ultimo_WooMultiPay' ) ) :

    class Ultimo_WooMultiPay {

	private static $instance;

	public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Ultimo_WooMultiPay ) ) {
			
			self::$instance = new Ultimo_WooMultiPay;

			self::$instance->constants();
			self::$instance->includes();
			self::$instance->hooks();
		}

		return self::$instance;
	}
        /**
	 *	Constants
	 */
	public function constants() {

		// Plugin version
		if ( ! defined( 'ULTIMO_WOOMULTIPAY_VERSION' ) ) {
			define( 'ULTIMO_WOOMULTIPAY_VERSION', '1.0' );
		}

		// Database version
		if ( ! defined( 'ULTIMO_WOOMULTIPAY_DATABASE_VERSION' ) ) {
			define( 'ULTIMO_WOOMULTIPAY_DATABASE_VERSION', '1.0.0' );
		}

		// Plugin file
		if ( ! defined( 'UWOOMP_PLUGIN_FILE' ) ) {
			define( 'UWOOMP_PLUGIN_FILE', __FILE__ );
		}

		// Plugin basename
		if ( ! defined( 'UWOOMP_PLUGIN_BASENAME' ) ) {
			define( 'UWOOMP_PLUGIN_BASENAME', plugin_basename( UWOOMP_PLUGIN_FILE ) );
		}

		// Plugin directory path
		if ( ! defined( 'UWOOMP_PLUGIN_DIR_PATH' ) ) {
			define( 'UWOOMP_PLUGIN_DIR_PATH', trailingslashit( plugin_dir_path( UWOOMP_PLUGIN_FILE )  ) );
		}

		// Plugin directory URL
		if ( ! defined( 'UWOOMP_PLUGIN_DIR_URL' ) ) {
			define( 'UWOOMP_PLUGIN_DIR_URL', trailingslashit( plugin_dir_url( UWOOMP_PLUGIN_FILE )  ) );
		}

		// Settings page URL
		if ( ! defined( 'UWOOMP_SETTINGS_PAGE_URL' ) ) {
			define( 'UWOOMP_SETTINGS_PAGE_URL', add_query_arg( 'page', 'uwoomp', admin_url( 'admin.php' ) ) );
		}

		// Admin settings directory path
		if ( ! defined( 'UWOOMP_SETTINGS_DIR' ) ) {
			define( 'UWOOMP_SETTINGS_DIR', UWOOMP_PLUGIN_DIR_PATH . 'inc/admin/' );
		}

		// Modules directory URL
		if ( ! defined( 'UWOOMP_TOOLS_URL' ) ) {
			define( 'UWOOMP_TOOLS_URL', UWOOMP_PLUGIN_DIR_URL . 'tools/' );
		}

		// Modules directory path
		if ( ! defined( 'UWOOMP_TOOLS_DIR' ) ) {
			define( 'UWOOMP_TOOLS_DIR', UWOOMP_PLUGIN_DIR_PATH . 'tools/' );
		}

		// SV framework file
		if ( ! defined( 'SV_WC_FRAMEWORK_FILE' ) ) {
			define( 'SV_WC_FRAMEWORK_FILE', UWOOMP_PLUGIN_DIR_PATH . 'tools/woocommerce/class-sv-wc-framework-bootstrap.php' );
		}

		// UwooE website - tools page
		if ( ! defined( 'UW_TOOLS_WEBSITE_PAGE' ) ) {
			define( 'UW_TOOLS_WEBSITE_PAGE', 'https://easycmspro.com/item/woocommerce-multipay/' );
		}
	}
        /**
	 *	Include PHP files
	 */
	public function includes() {

		// Admin includes
		include_once UWOOMP_SETTINGS_DIR . 'admin-page.php';
		include_once UWOOMP_SETTINGS_DIR . 'admin-notices.php';

		// Helper functions
		include_once UWOOMP_PLUGIN_DIR_PATH . 'inc/helper-functions.php';

		// Database update
		include_once UWOOMP_PLUGIN_DIR_PATH . 'inc/class-database-update.php';

		$options = uwoomp_get_settings();

		// Exit if no options
		if ( ! $options || ! isset( $options['tools'] ) ) {
			return;
		}
                // Module files
		foreach ( uwoomp_get_tool_sections() as $section ) {

			foreach ( $section['section_tools'] as $tool ) {

				// Check if tool is in enabled tools array
				$key = array_key_exists( $tool['key'], $options['tools'] ) ? intval( $options['tools'][$tool['key']] ) : '';

				if ( $key === 1 ) {
					include_once UWOOMP_TOOLS_DIR . $tool['include_path'];
				}
			}
		}
	}

	/**
	 *	Action/filter hooks
	 */
	public function hooks() {

		register_activation_hook( UWOOMP_PLUGIN_FILE, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'loaded' ) );

		add_filter( 'plugin_action_links_' . UWOOMP_PLUGIN_BASENAME, array( $this, 'action_links' ) );
		
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_links' ), 10, 2 );
	}

	/**
	 *	Check to see if WooCommerce is active, and initialize the options in database
	 */
	public function activate() {

		// Deactivate and die if WooCommerce is not active
		if ( ! is_woocommerce_active() ) {
			deactivate_plugins( UWOOMP_PLUGIN_BASENAME );
			wp_die( __( 'Whoops! Ultimo Woocommerce Multipay requires you to install and activate WooCommerce first.', 'ultimo-woomultipay' ) );
		}

		// Current plugin settings, and default settings for new installs
		$options = uwoomp_get_settings();
		$options = is_array( $options ) ? $options : array();
		$initial_options = array( 'db_version' => ULTIMO_WOOMULTIPAY_DATABASE_VERSION );

		// Add option with initial data for fresh installs
		if ( ! isset( $options['db_version'] ) ) {
			update_option( 'uwoomp', array_merge( $options, $initial_options ) );
		}
	}

	/**
	 *	Load plugin text domain
	 */
	public function loaded() {

		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'ultimo-woomultipay' );
		
		unload_textdomain( 'ultimo-woomultipay' );
		load_textdomain( 'ultimo-woomultipay', WP_LANG_DIR . '/ultimo-woomultipay/ultimo-woomultipay-' . $locale . '.mo' );
		load_plugin_textdomain( 'ultimo-woomultipay', false, dirname( __FILE__ ) . '/languages' );
	}

	/**
	 *	Plugin action links
	 */
	public function action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', UWOOMP_SETTINGS_PAGE_URL, __( 'Settings', 'ultimo-woomultipay' ) );
		return $links;
	}

	/**
	 *	Plugin info row links
	 */
	public function plugin_row_links( $links, $file ) {

		if ( $file == UWOOMP_PLUGIN_BASENAME ) {

			$links[] = sprintf( '<a href="https://easycmspro.com/item/woocommerce-multipay/" target="_blank">%s</a>', __( 'Support', 'ultimo-woomultipay' ) );
		}

		return $links;
	}
}

endif;

/**
 *	Main function
 *	@return object Ultimo_WooMultiPay instance
 */
function Ultimo_WooMultiPay() {
	return Ultimo_WooMultiPay::instance();
}

Ultimo_WooMultiPay();
