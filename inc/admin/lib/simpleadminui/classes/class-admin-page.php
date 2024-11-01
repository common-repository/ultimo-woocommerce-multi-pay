<?php
/**
 *	Prepare a sinlge menu or submenu page with meta boxes and settings
 */
namespace UwooMP\AdminPage;

if ( ! class_exists( 'Admin_Page' ) ) :

class Admin_Page {

	private $page,
			$page_hook,
			$page_title,
			$menu_title,
			$capabilities,
			$slug,
			$priority,
			$icon,
			$default_columns,
			$body_content,
			$parent_sluf,
			$sortable,
			$collapsable,
			$contains_media,
			$tabs,
			$help_section;

	public function __construct( array $args ) {

		// Setup
		$this->slug = $args['slug'];
		$this->page_title = $args['page_title'];
		$this->menu_title = $args['menu_title'];
		$this->capabilities = $args['capabilities'];
		$this->priority = $args['priority'];
		$this->icon = $args['icon'];
		$this->default_columns = $args['default_columns'];
		$this->body_content = $args['body_content'];
		$this->parent_slug = $args['parent_slug'];
		$this->sortable = $args['sortable'];
		$this->collapsable = $args['collapsable'];
		$this->contains_media = $args['contains_media'];
		$this->tabs = $args['tabs'];
		$this->help_section = $args['help_section'];

		$this->hooks();

		require_once 'class-register-meta-boxes.php';

		// Register page's meta boxes
		$meta_boxes = new \UwooMP\RegisterMetaBoxes\Register_Meta_Boxes( $this->slug );
	}

	/**
	 *	Run hooks
	 */
	public function hooks() {

		// Add the page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Add JavaScript
		if ( $this->contains_media === true ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );
		}
	}

	/**
	 *	Add the appropriate page (top or sub)
	 *	Add the appropriate callbacks to the page hooks (load-{suffix} and admin_footer-{suffix})
	 */
	public function add_page(){

		// Add the page
		if ( $this->parent_slug ) {
			$this->page_hook = add_submenu_page( $this->parent_slug, $this->page_title, $this->menu_title, $this->capabilities, $this->slug, array( $this, 'render_page'), $this->priority );
		} else {
			$this->page_hook = add_menu_page( $this->page_title, $this->menu_title, $this->capabilities, $this->slug, array( $this, 'render_page' ), $this->icon, $this->priority );
		}

		// Get the screen object
		$this->page = \WP_Screen::get( $this->page_hook );

		// Add callbacks for this page
		add_action( "load-{$this->page_hook}", array( $this, 'admin_load_page' ), 5 );
		add_action( "admin_footer-{$this->page_hook}", array( $this, 'admin_footer' ) );

		/*// Register page's help tabs
		if ( is_array( $this->help_section ) && isset( $this->help_section['tabs'] ) ) {
			$sidebar = isset( $this->help_section['sidebar'] ) && ! empty( $this->help_section['sidebar'] ) ? $this->help_section['sidebar'] : array();
			new \UwooMP\HelpTabs\Help_Tabs( $this->page, $this->help_section['tabs'], $sidebar );
		}*/
	}

	/**
	 *	Add JS files for media uploads (if enabled)
	 */
	public function enqueue_admin_js() {
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' ); // Provides all the functions needed to upload, validate and give format to files.
		wp_enqueue_script( 'thickbox' ); // Responsible for managing the modal window.
		wp_enqueue_style( 'thickbox' ); // Provides the styles needed for this window.
	}

	/**
	 *	jQuery to initialize meta boxes and media uploads (if enabled); runs on admin_footer-{suffix}
	 */
	public function admin_footer(){ ?>
		
		<script>

			jQuery(document).ready( function($) {

				<?php if ( $this->sortable === false ) : // Sortable disabled ?>

				$('.meta-box-sortables').sortable({
					disabled: true
				});

				$('.postbox .hndle').css('cursor', 'pointer');

				<?php endif; ?>

				<?php if ( $this->collapsable === true ) : // Collapsing enabled (default) ?>

				postboxes.add_postbox_toggles(pagenow);

				<?php else : ?>

				$('.postbox .hndle').css('cursor', 'default');
				$('.handlediv.button-link').css({
					cursor: 'default',
					display: 'none'
				});

				<?php endif; ?>
			});

		</script>

	<?php if ( $this->contains_media === true ) : // Add media uploader (default) ?>

		<script>
			
			jQuery(document).ready(function($) {

				// Uploading files
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = 10; // Set this

				$('.media-uploader').live('click', function(event){

					event.preventDefault();

					var file_frame;
					var button = $(this);
					var id = button.attr('id').replace('_button', '');

					// If the media frame already exists, reopen it.
					if (file_frame) {

						// Set the post ID to what we want
						// file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
						
						// Open frame
						file_frame.open();

						alert(id);
						
						return;

					} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						// wp.media.model.settings.post.id = set_to_post_id;
					}

					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({

						title: button.data('uploader-title'),
						button: {
							text: button.data('uploader-button-text'),
						},
						multiple: false  // Set to true to allow multiple files to be selected
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {

						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();

						// Do something with attachment.id and/or attachment.url here
						$("#" + id).val(attachment.url);

						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
					});

					// Finally, open the modal
					file_frame.open();
				});

				// Restore the main ID when the add media button is pressed
				$('a.add_media').on('click', function() {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});

		</script>

	<?php endif;

	}


	/*
	 *	Add meta boxes, screen options and enqueues the postbox.js script.   
	 */
	public function admin_load_page(){

		// Do the meta boxes
		do_action( "add_meta_boxes_{$this->page_hook}", null );
		do_action( 'add_meta_boxes', $this->page_hook, null );

		// One or two column layout option
		add_screen_option( 'layout_columns', array(
			'max' => 2,
			'default' => $this->default_columns
		) );

		// Handle meta boxes
		wp_enqueue_script( 'postbox' ); 
	}


	/**
	 *	Renders the settings page
	 */
	public function render_page(){ ?>

		 <div class="wrap" id="wc_multipay">

		 	<?php do_action( "uwoomp_settings_page_top" ); ?>

			<h1>
				<span class="woo-customizer-logo"><img src="<?php echo plugins_url( '/../../../../assets/img/logo.jpg', dirname(__FILE__));?>"/></span> <?php echo esc_html( $this->page_title );?>
				<?php do_action( 'uwoomp_settings_page_title_action' ); ?>
			</h1>

			<?php do_action( "uwoomp_settings_page_before_tool_settings" ); ?>

			<?php $this->render_tabs( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ); ?>

			<form name="<?php echo $this->slug; ?>_admin_form" id="<?php echo $this->slug; ?>_admin_form" action="?page=<?php echo $this->slug; ?>&save-settings=true" method="post">
				
				<?php wp_nonce_field( $this->slug . '_admin_nonce', $this->slug . '_admin_nonce' );

				// Used to save closed metaboxes and their order
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

				<div id="poststuff">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>"> 

					 	<?php do_action( "uwoomp_settings_page_body_start" ); ?>

						<?php if ( is_callable( $this->body_content ) ) : ?>

							<div id="post-body-content" class="postbox-container">
								<?php call_user_func( $this->body_content ); ?>
								<?php do_action( "uwoomp_settings_page_after_body_content" ); ?>
							</div>

						<?php endif; ?>

						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( '', 'normal', null );  ?>
							<?php do_meta_boxes( '', 'advanced', null ); ?>
						</div>

						<?php do_action( "uwoomp_settings_page_body_end" ); ?>	     					

					 </div> <!-- #post-body -->
				
				</div> <!-- #poststuff -->

	      	</form>
			<div id="confirmation_alert" style="display:none;">
				<div class="popup-content">
					<div class="input-field">
						<label id="alert_message" class="lbl-message"></label>
					</div>
					<div class="input-field">
						<input type="button" class="btn" name="btn_confirm" id="btn_confirm" onclick="confirm_submission()" value="<?php echo __('Confirm', 'ultimo-woomultipay');?>"/>
						<img id="confrim_success" src="" style="display:none;"/>
					</div>
				</div>
			</div>
			<div id="locked_alert" style="display:none;">
				<div class="popup-content locked">
					<div class="input-field">
						<label id="alert_message" class="lbl-message">This feature is available in pro version. Please upgrade to pro version to unlock features.</label>
					</div>
					<div class="input-field">
						<a class="btn" href="https://easycmspro.com/item/woocommerce-multipay/" target="_blank"><?php echo __('Upgrade Now', 'ultimo-woomultipay');?></a>
						<a class="btn btn-cancel" href="javascript:void(0)" onclick="close_poup();"><?php echo __('Not Now', 'ultimo-woomultipay');?></a>
					</div>
				</div>
			</div>
			<div class="support-section">
				<button type="button" onclick="support_popup()" class="btn btn-support" style="display:block;" align="center"><?php echo __('Contact Support', 'ultimo-woomultipay');?></button>
				<div id="contactsupport" style="display:none;">
					<div class="popup-content support-form">
						<!-- Begin Mailchimp Signup Form -->
						<link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
						<div id="mc_embed_signup" class="support-form">
							<form action="https://ultimocmsbooster.us20.list-manage.com/subscribe/post?u=7243c7eda31cdc78043732822&amp;id=bc0d24553c" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
								<div id="mc_embed_signup_scroll">
									<h2><?php echo __('Get In Touch with Us!', 'ultimo-woomultipay');?></h2>
									<div class="mc-field-group">
										<label for="mce-EMAIL"><?php echo __('Email Address', 'ultimo-woomultipay');?> </label>
										<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
									</div>
									<div class="mc-field-group">
										<label for="mce-MESSAGE"><?php echo __('Message', 'ultimo-woomultipay');?> </label>
										<textarea name="MESSAGE" class="" id="mce-MESSAGE"></textarea>
									</div>
									<div id="mce-responses" class="clear">
										<div class="response" id="mce-error-response" style="display:none"></div>
										<div class="response" id="mce-success-response" style="display:none"></div>
									</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
									<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_7243c7eda31cdc78043732822_bc0d24553c" tabindex="-1" value=""></div>
									<div class="clear"><input type="submit" value="<?php echo __('Send', 'ultimo-woomultipay');?>" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
								</div>
							</form>
						</div>
						<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[5]='MESSAGE';ftypes[5]='text';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
						<!--End mc_embed_signup-->
					</div>
				</div>
			</div>
			<?php
			add_thickbox();
			?>
			<script type="text/javascript">
				function support_popup()
				{
					tb_show('', '#TB_inline?height=550&width=auto&unvq=true&inlineId=contactsupport');
				}
				jQuery("input.switch").click(function(){
					if(jQuery(this).hasClass('locked')){
						jQuery(this).addClass("unchecked");
						jQuery(this).prop("checked", false);
						tb_show('', '#TB_inline?height=340&width=auto&unvq=true&inlineId=locked_alert');
					}
					else
					{
						if(jQuery(this).prop("checked") == true)
						{
							var message = "<?php echo __( 'Click confirm to activate this tool.', 'ultimo-woomultipay' );?>";
							jQuery(this).addClass("unchecked");
						}
						else
						{
							var message = "<?php echo __( 'Click confirm to deactivate this tool.', 'ultimo-woomultipay' );?>";
							jQuery(this).addClass("checked");
						}
						jQuery("#alert_message").html(message);
						tb_show('', '#TB_inline?height=auto&width=auto&unvq=true&inlineId=confirmation_alert');
					}
					jQuery("#TB_window").addClass("modules-popup");
				});
				jQuery(window).bind('tb_unload', function () {
					jQuery("input.switch").each(function(){
						//alert(jQuery(this).attr("class"))
						if(jQuery(this).hasClass("checked"))
						{
							jQuery(this).prop("checked", true);
						}
						else if(jQuery(this).hasClass("unchecked"))
						{
							jQuery(this).prop("checked", false);
						}
					});
				});
				function confirm_submission()
				{
					jQuery("#uwoomp_admin_form").submit();
				}
				function close_poup()
				{
					jQuery('#TB_closeWindowButton').click();
				}
			</script>
			<?php do_action( "uwoomp_settings_page_after_tool_settings" ); ?>

		 </div><!-- .wrap -->

		<?php
	}

	/**
	 *	Renders the tabs
	 */
	public function render_tabs( $current = '' ) {

		if ( ! empty( $this->tabs ) ) : ?>

			<?php do_action( "uwoomp_settings_page_before_tabs" ); ?>

			<h2 class="nav-tab-wrapper">

				<?php foreach ( $this->tabs as $key => $tab ) : ?>

					<?php $active = ( $key == $current ) ? ' nav-tab-active' : ''; ?>

					<a href="<?php echo add_query_arg( 'tab', $key, UWOOMP_SETTINGS_PAGE_URL ); ?>" class="nav-tab<?php echo $active; ?>"><?php echo $tab; ?></a>

				<?php endforeach; ?>

			</h2>

			<?php do_action( "uwoomp_settings_page_after_tabs" ); ?>

		<?php endif;
	}

	/**
	 *	Get $this->page
	 */
	public function get_page_object() {
		return $this->page;
	}

	/**
	 *	Rertieve $this->slug
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 *	Rertieve $this->tabs
	 */
	public function get_tabs() {
		return $this->tabs;
	}

	/**
	 *	Retrieve $this->parent_slug
	 */
	public function get_hook() {
		return $this->parent_slug;
	}

	/**
	 *	Retrieve $this->page_title
	 */
	public function get_page_title() {
		return $this->page_title;
	}

	/**
	 *	Retrieve $this->menu_title
	 */
	public function get_menu_title() {
		return $this->menu_title;
	}

	/**
	 *	Retrieve $this->icon
	 */
	public function get_icon() {
		return $this->icon;
	}

	/**
	 *	Retrieve $this->capabilities
	 */
	public function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 *	Retrieve $this->priority
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 *	Get it all!
	 */
	public function get_it_all() {
		return array(
			'page_object' => $this->get_page_object(),
			'slug' => $this->get_slug(),
			'tabs' => $this->get_tabs(),
			'hook' => $this->get_hook(),
			'page_title' => $this->get_page_title(),
			'menu_title' => $this->get_menu_title(),
			'icon' => $this->get_icon(),
			'capabilities' => $this->get_capabilities(),
			'priority' => $this->get_priority(),
		);
	}

}

endif;