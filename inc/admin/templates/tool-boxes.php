<?php
/**
 *	Form for enabling UwooMP tools
 *
 *	@package Ultimo WooMultiPay
 *	@author Ultimo Cms Booster
 *	@since 1.0
 */

$count = 0;

do_action( "uwoomp_settings_mb_start_{$args['args']['key']}" );

?>

<p class="description"><?php echo $args['args']['section']['section_description']; ?></p>
<?php // Make sure the tools array is always posted, even when none are selected; this is unset when processing ?>
<input type="hidden" name="uwoomp[tools][triggered]" value="1">
<div class="container">
	<div class="col-md-12">
		<div class="row">
		<?php

		/**
		 *	Output a checkbox for each tool
		 */
		foreach ( $args['args']['section']['section_tools'] as $tool ) {

				// Assemble the key for proper saving
				$key = 'uwoomp[tools][' . $tool['key'] . ']';

				// Whether option is enabled
				if ( isset( $this->settings['tools'][$tool['key']] ) && intval( $this->settings['tools'][$tool['key']] ) === 1 ) {
					$value = 1;
				} else {
					$value = 0;
				}

				// CSS classes
				if ( $count === 0 || 0 === $count % 3 ) {
					$classes = 'one-third first';
				} else {
					$classes = 'one-third';
				}
			?>
			<div class="col-sm-6 col-md-4">
				<div class="thumbnail <?php echo $count > 2 ? 'locked' : '';?>">
					<?php
						echo '<span class="settings-icon"><img src="' . plugins_url( '/../../assets/img/multipay'.$count.'.png', dirname(__FILE__)) . '" ></span> ';
					?>
					<div id="<?php echo $tool['key']; ?>" class="caption <?php echo $classes; ?>">
						<h3><?php echo $tool['title']; ?></h3>

						<div class="button-switch">
							<input type="checkbox" id="switch-orange" class="switch <?php echo $count > 1 ? 'locked' : '';?>" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php checked( intval( $value ), 1 ); ?>/>
							<label for="switch-orange" class="lbl-on" ></label>
							<label for="switch-orange" class="lbl-off"></label>
						</div>
					</div>
				</div>
			</div>
			<?php

			$count++;

			// Clear after every three and last
			if ( 0 === $count % 3 || $count == sizeof( $args['args']['section']['section_tools'] ) ) {
				echo '<br class="clear">';
			}
		}

		do_action( "uwoomp_settings_mb_end_{$args['args']['key']}" );
		?>
		</div>
	</div>
</div>