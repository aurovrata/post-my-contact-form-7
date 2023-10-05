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

if ( ! empty( $mapped_fields ) ) {
	foreach ( $mapped_fields as $cf7_field => $post_field ) {
		?>
<div class="cf72post-field">
	<label><?php echo esc_html( $cf7_field ); ?></label>
		<?php
		if ( false && current_user_can( 'edit_others_posts', $post->ID ) ) :
			?>
		<input type="text" class="field-value" name="<?php echo esc_attr( $post_field ); ?>" value="<?php echo esc_attr( get_post_meta( $post->ID, $post_field, true ) ); ?>" />
			<?php
		else :
			$value = get_post_meta( $post->ID, $post_field, true );
			if ( is_array( $value ) ) {
				echo '<div>';
				// wpg_debug($value, $cf7_field).
				cf72post_output_array_field( $value );
				echo '</div>';
			} else {
				cf72post_output_field( $value );
			}
	endif;
		?>
	</div>
		<?php
	}
}
/**
 * Echo fields spans
 *
 * @since 1.0.0
 * @param string $value field value.
 */
function cf72post_output_field( $value ) {
	echo '<span class="field-value">' . esc_html( $value ) . '</span>';
}
/**
 * Echo fields spans
 *
 * @since 1.0.0
 * @param mixed $value field value or array of values.
 */
function cf72post_output_array_field( $value ) {
	if ( is_array( reset( $value ) ) ) {
		foreach ( $value as $r => $row ) {
			cf72post_output_array_field( $row, '</br>' );
		}
	} else {
		cf72post_output_field( implode( ',', $value ) );
	}
}
