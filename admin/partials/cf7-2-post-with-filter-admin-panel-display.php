<?php
/**
 * Display C2P admin tab for filtered mappings.
 *
 * @link       https://profiles.wordpress.org/aurovrata/
 * @since      5.4.3
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$cf7_key_flat = str_replace( '-', '_', $cf7_key );
?>
<h1><?php echo esc_html( __( 'Save submissions as ', 'post-my-contact-form-7' ) ); ?><span id="custom-post-title">filter</span></h1>
<!-- $form = get_post($cf7_post_id); ?> -->
<input type="hidden" id="c2p-cf7-key" value="<?php echo esc_attr( $cf7_key ); ?>"/>
<input type="hidden" id="c2p-mapping-changed" name="c2p_mapping_changes" value="0"/>
<input type="hidden" id="c2p-active-tab" name="c2p_active_tab" value="0"/>
<input type="hidden" id="c2p-mapping-status" name="mapped_post_map" value="<?php echo esc_attr( $status ); ?>"/>
<input type="hidden" name="mapped_post_default" value="0"/>
<input type="hidden" name="mapped_post_type_source" value="filter"/>

<?php wp_nonce_field( 'cf7_2_post_mapping', 'cf7_2_post_nonce', false, true ); ?>

<div id="c2p-factory-post">
	<div class="c2p-title-header">
		<h2><?php echo esc_html( __( 'Map form to...', 'post-my-contact-form-7' ) ); ?></h2>
		<div class="toggle toggle-light"></div>
	</div>
		<p>
			<?php echo esc_html( __( "This form's submissions saved using an action callback, hook ", 'post-my-contact-form-7' ) ); ?>
			<span id="c2p-filter-clipboard" class="cf7-post-msg animate-color">
				<a class="code" data-clipboard-text="add_action('cf7_2_post_save_submission','c2p_save_<?php echo esc_attr( $cf7_key_flat ); ?>',10,3);<?php echo PHP_EOL; ?>function c2p_save_<?php echo esc_html( $cf7_key_flat ); ?>($form_key, $form_data, $form_files){<?php echo PHP_EOL; ?>  //$form_key is the unique form key.<?php echo PHP_EOL; ?>  // $form_data is the submitted form data.<?php echo PHP_EOL; ?>  //$form_files are submitted files if any.<?php echo PHP_EOL; ?>}" href="javascript:void(0);">cf7_2_post_save_submission</a>
				<span class="popup"><?php echo esc_html( __( 'Click to copy!', 'post-my-contact-form-7' ) ); ?>
					<span>
						<?php echo esc_html( __( 'Paste helper code into your theme functions.php file.', 'post-my-contact-form-7' ) ); ?>
					</span>
				</span>
			</span>
		</p>
</div>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		new Clipboard(document.querySelector('#c2p-filter-clipboard a.code'));
	})
	})( jQuery )
	</script>
	<div class="c2p-filter">
	<?php
	if ( ! defined( 'CF7_GRID_VERSION' ) ) {
		$closed = '';
		echo '<h2>' . esc_html( __( 'Hooks & Filters to customise the mapping', 'post-my-contact-form-7' ) ) . '</h2>';
		include_once 'cf7-2-post-helper-metabox.php';
	}
	?>
</div>
