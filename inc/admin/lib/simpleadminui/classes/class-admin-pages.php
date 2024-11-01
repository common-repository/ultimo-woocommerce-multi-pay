<?php
/**
 *	Register menu/submenu pages
 */
namespace UwooMP\AdminPages;

require_once 'class-admin-page.php';

if ( ! class_exists( 'Admin_Pages' ) ) :

class Admin_Pages {

	public static $registered_pages;
	
	/**
	 *	@param (array) $pages - Pages to create
	 */
	public function __construct( array $pages ) {

		// Prepare each admin page
		foreach ( $pages as $slug => $setup ) {

			// Required
			$page_title = $setup['page_title'];
			$menu_title = $setup['menu_title'];

			// Optional
			$priority = isset( $setup['priority'] ) ? $setup['priority'] : 99;
			$capabilities = isset( $setup['capabilities'] ) ? $setup['capabilities'] : 'manage_options';
			$icon = isset( $setup['icon'] ) ? $setup['icon'] : '';
			$default_columns = isset( $setup['default_columns'] ) ? $setup['default_columns'] : '';
			$body_content = isset( $setup['body_content'] ) ? $setup['body_content'] : '__return_true';
			$parent_slug = isset( $setup['parent_slug'] ) ? $setup['parent_slug'] : '';
			$sortable = isset( $setup['sortable'] ) ? $setup['sortable'] : true;
			$collapsable = isset( $setup['collapsable'] ) ? $setup['collapsable'] : true;
			$contains_media = isset( $setup['contains_media'] ) ? $setup['contains_media'] : true;
			$tabs = isset( $setup['tabs'] ) ? $setup['tabs'] : array();
			$help_section = ! empty( $setup['help_section'] ) ? $setup['help_section'] : array();

			self::$registered_pages[$slug] = new \UwooMP\AdminPage\Admin_Page([
				'slug' => $slug,
				'page_title' => $page_title,
				'menu_title' => $menu_title,
				'capabilities' => $capabilities,
				'priority' => $priority,
				'icon' => $icon,
				'default_columns' => $default_columns,
				'body_content' => $body_content,
				'parent_slug' => $parent_slug,
				'sortable' => $sortable,
				'collapsable' => $collapsable,
				'contains_media' => $contains_media,
				'tabs' => $tabs,
				'help_section' => $help_section
			]);
		}
	}

	/**
	 *	Get all registered pages
	 */
	public static function get_registered_pages() {
		return self::$registered_pages;
	}

	/**
	 *	Get a single registered page
	 */
	public static function get_registered_page( $slug ) {
		return isset( self::$registered_pages[$slug] ) ? self::$registered_pages[$slug] : null;
	}

}

endif;