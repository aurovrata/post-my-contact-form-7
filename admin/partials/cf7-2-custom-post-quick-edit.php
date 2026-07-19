<?php
/**
 * Display quickedit options in table of wpcf7 form posts.
 *
 * @since 5.3.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Only proceed if user can edit posts, allow fine grain control via filter.
$capability = apply_filters( 'cf7_2_post_mapping_capability', 'manage_options' );
if ( ! current_user_can( $capability ) ) {
	return;
}

// Add nonce for security: c2p_nonce is already added prior to calling this file.
?>
<fieldset class="inline-edit-col-left">
	<legend class="inline-edit-legend"><?php esc_html_e( 'CF7 to Post Mapping', 'post-my-contact-form-7' ); ?></legend>
	<div class="inline-edit-col">
		<label>
			<span class="title"><?php esc_html_e( 'CF7 >> Post', 'post-my-contact-form-7' ); ?></span>
			<input 
				class="cf72post-submit" 
				type="checkbox" 
				name="cf7_2_post_submit" 
				value="1"
				id="cf72post-submit-checkbox"
			/>
			<?php esc_html_e( 'Form Submitted', 'post-my-contact-form-7' ); ?>
		</label>
		<p>
			<em><?php esc_html_e( 'Uncheck to reload post in form', 'post-my-contact-form-7' ); ?></em>
		</p>
	</div>
</fieldset>