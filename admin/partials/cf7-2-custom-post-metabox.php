<?php
/**
 * Display quickedit options in table of wpcf7 form posts.
 *
 * @link       https://profiles.wordpress.org/aurovrata/
 * @since      5.3.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ensure required variables exist.
$mapped_fields = isset( $mapped_fields ) ? $mapped_fields : array();
$post          = isset( $post ) ? $post : get_post();

if ( empty( $post ) ) {
	return;
}

// Add nonce for security: added prior to calling this file in the admin class.

if ( is_array( $mapped_fields ) ) {
	foreach ( $mapped_fields as $cf7_field => $post_field ) {
		if( 0 === strpos( $cf7_field, 'cf7_2_post_filter-' ) ) continue; //skip filter mappings.
		?>
		<div class="cf72post-field">
			<label for="cf72post-field-<?php echo esc_attr( $post_field ); ?>" class="cf72post-field-label">
				<?php echo esc_html( $cf7_field ); ?>
			</label>
			<div class="field-value-display">
				<?php
				$value = get_post_meta( $post->ID, $post_field, true );
				cf72post_render_meta_field_value( $value, $post_field );
				?>
			</div>
		</div>
		<?php
	}
}

/**
 * Render a single meta field value.
 *
 * @since 5.3.0
 *
 * @param mixed  $value      The field value to render.
 * @param string $field_name The field name (optional for context).
 * @return void
 */
function cf72post_render_meta_field_value( $value, $field_name = '' ) {
	if ( is_array( $value ) ) {
		cf72post_render_array_field( $value, $field_name );
	} else {
		cf72post_render_single_field( $value );
	}
}

/**
 * Render a single field value as a span.
 *
 * @since 5.3.0
 *
 * @param string $value The field value.
 * @return void
 */
function cf72post_render_single_field( $value ) {
	echo '<span class="field-value">' . esc_html( $value ) . '</span>';
}

/**
 * Render an array field value recursively.
 *
 * @since 5.3.0
 *
 * @param mixed  $value      The field value (may be array or string).
 * @param string $field_name The field name (optional for context).
 * @param string $separator  The separator for array values.
 * @return void
 */
function cf72post_render_array_field( $value, $field_name = '', $separator = ', ' ) {
	if ( ! is_array( $value ) ) {
		cf72post_render_single_field( $value );
		return;
	}

	// Check if this is a multi-dimensional array.
	$first_element = reset( $value );
	if ( is_array( $first_element ) ) {
		// Multi-dimensional array - render as nested list.
		echo '<ul class="cf72post-nested-field">';
		foreach ( $value as $index => $row ) {
			echo '<li class="cf72post-nested-item">';
			cf72post_render_array_field( $row, $field_name . "[{$index}]" );
			echo '</li>';
		}
		echo '</ul>';
	} else {
		// Single dimension array - render as comma-separated list.
		$escaped_values = array_map( 'esc_html', $value );
		echo '<span class="field-value-array">' . esc_html( implode( $separator, $escaped_values ) ) . '</span>';
	}
}

/**
 * Backward compatibility function for older code.
 *
 * @since 1.0.0
 * @deprecated 5.3.0 Use cf72post_render_meta_field_value() instead.
 *
 * @param string $value The field value.
 * @return void
 */
function cf72post_output_field( $value ) {
	_deprecated_function( __FUNCTION__, '5.3.0', 'cf72post_render_single_field' );
	cf72post_render_single_field( $value );
}

/**
 * Backward compatibility function for older code.
 *
 * @since 1.0.0
 * @deprecated 5.3.0 Use cf72post_render_array_field() instead.
 *
 * @param mixed $value The field value or array of values.
 * @return void
 */
function cf72post_output_array_field( $value ) {
	_deprecated_function( __FUNCTION__, '5.3.0', 'cf72post_render_array_field' );
	cf72post_render_array_field( $value );
}