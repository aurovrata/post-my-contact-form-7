<?php
/**
 * Display post meta fields mapping.
 *
 * @link       https://profiles.wordpress.org/aurovrata/
 * @since      5.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

$select_form_fields = '<select %4$s name="cf7_2_post_map_meta_value%1$s" value="%2$s" class="field-options post-options select-hybrid">
  <option class="default-option" selected="true" value="">' . esc_html( __( 'Select a form field', 'post-my-contact-form-7' ) ) . '</option>
  <option class="filter-option" value="cf7_2_post_filter%3$s%1$s">' . esc_html( __( 'Hook with a filter', 'post-my-contact-form-7' ) ) . '</option>
</select>';
// %1 - post-field name.
// %2 - form-field name.
// %3 - post type.
// %4 - disabled attr.

$mapped_fields = $post_mapper->get_mapped_meta_fields();
foreach ( $mapped_fields as $cf7_field => $post_field ) :
	?>
	<li>
	<div class="post-meta-field cf7-2-post-field">
		<div class="post-field-name">
		<?php
		if ( 'system' === $source ) {
			echo wp_kses( $factory->get_metafield_menu( $post_mapper->get( 'type' ), $post_field );
		} else {
			echo $post_mapper->get_metafield_input( $post_field );
		}
		?>
		</div>
		<?php
		// display the meta-field's form field dropdown.
		echo sprintf( $select_form_fields, esc_attr( "-$post_field" ), esc_attr( $cf7_field ), esc_attr( "-{$post_mapper->get('type')}" ), '' );
		?>
		<span class="dashicons dashicons-remove remove-field"></span>
	</div><span class="cf7-post-msg"></span>
	</li>
	<?php endforeach; ?>
	<li class="default-meta-field">
	<div class="post-meta-field cf7-2-post-field">
		<span class="spinner meta-label"></span>
		<div class="post-field-name">
		<?php
		if ( 'system' == $source ) {
			echo $factory->get_metafield_menu( $post_mapper->get( 'type' ), '' );
		} else {
			echo $post_mapper->get_metafield_input( '' );
		}
		?>
		</div>
		<?php echo sprintf( $select_form_fields, '-meta_key_1', '', '', 'disabled="true"' ); // display the form field selevt. ?>
		<span class="dashicons dashicons-insert add-more-field"></span>
	</div>
</li>
