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
?>
<fieldset class="inline-edit-col-left">
	<div class="inline-edit-col">
		<label><span class="title"><?php echo esc_html( __( 'CF7 >> Post', 'post-my-contact-form-7' ) ); ?></span><input class="cf72post-submit" type="checkbox" name="cf7_2_post_submit"/><?php echo esc_html( __( 'Form Submitted', 'post-my-contact-form-7' ) ); ?></label>
			<p><em><?php echo esc_html( __( 'Uncheck to reload post in form', 'post-my-contact-form-7' ) ); ?></em></p>
	</div>
</fieldset>
