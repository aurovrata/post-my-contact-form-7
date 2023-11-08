<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$mapped_taxonomy = $post_mapper->get_mapped_taxonomy();
foreach ( $mapped_taxonomy as $cf7_field => $post_taxonomy ) :
	$taxmy = $post_mapper->get_taxonomy( $post_taxonomy );
	$val   = $cf7_field;
	if ( strpos( $cf7_field, 'cf7_2_post_filter-' ) === 0 ) {
		$val = '';
	}
	?>
	<li>
		<div class="custom-taxonomy-field cf7-2-post-field<?php echo empty( $val ) ? ' hooked' : ''; ?>">
			<label class="taxonomy-label-field cf7-2-post-map-labels">
			<span class="taxonomy-name">
				<strong><?php echo esc_html( $taxmy['name'] ); ?></strong>
			</span>&nbsp;
			(<span class="enabled link-button edit-taxonomy"><?php echo esc_html( __( 'Edit', 'post-my-contact-form-7' ) ); ?></span>)
			</label>
			<select class="select-hybrid field-options taxonomy-options" name="cf7_2_post_map_taxonomy_value-<?php echo esc_attr( $post_taxonomy ); ?>/<?php echo esc_attr( $val ); ?>" value="<?php echo esc_attr( $cf7_field ); ?>">
			<option class="default-option" selected="true" value="">
				<?php echo esc_html( __( 'Select a form field', 'post-my-contact-form-7' ) ); ?>
			</option>
			<option class="filter-option" value="cf7_2_post_filter-<?php echo esc_attr( $post_taxonomy ); ?>">
				<?php echo esc_html( __( 'Hook with a filter', 'post-my-contact-form-7' ) ); ?>
			</option>
			</select>
			<span class="dashicons dashicons-remove remove-field"></span>
			<span class="php-filter-button"></span>
		</div>
		<span class="cf7-post-msg"></span>
		<div class="custom-taxonomy-input-fields display-none">
			<p>
			<?php echo esc_html( __( 'Choose a taxonomy, in blue are existing public taxonomies', 'post-my-contact-form-7' ) ); ?>
			</p>
			<?php echo wp_kses( $post_mapper->get_taxonomy_listing( $post_taxonomy ), CF72Post_Mapping_Factory::$allowed_html ); ?>
			<label for="cf7_2_post_map_taxonomy_names-<?php echo esc_attr( $post_taxonomy ); ?>">
			<strong><?php echo esc_html( __( 'Plural Name', 'post-my-contact-form-7' ) ); ?></strong>
			</label>
			<?php $readonly = ( 'system' === $taxmy['source'] ) ? 'true' : 'false'; ?>
			<input class="c2p-tax-labels plural-name" type="text" readonly="<?php echo esc_attr( $readonly ); ?>" name="cf7_2_post_map_taxonomy_names-<?php echo esc_attr( $post_taxonomy ); ?>" value="<?php echo esc_attr( $taxmy['name'] ); ?>" />
			<label for="cf7_2_post_map_taxonomy_name-<?php echo esc_attr( $post_taxonomy ); ?>">
			<strong><?php echo esc_html( __( 'Singular Name', 'post-my-contact-form-7' ) ); ?></strong>
			</label>
			<input class="c2p-tax-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo esc_attr( $post_taxonomy ); ?>" readonly="<?php echo esc_attr( $readonly ); ?>" value="<?php echo esc_attr( $taxmy['singular_name'] ); ?>">
			<label for="cf7_2_post_map_taxonomy_slug-<?php echo esc_attr( $post_taxonomy ); ?>">
			<strong><?php echo esc_html( __( 'Slug', 'post-my-contact-form-7' ) ); ?></strong>
			</label>
			<input class="c2p-tax-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo esc_attr( $post_taxonomy ); ?>" readonly="<?php echo esc_attr( $readonly ); ?>" value="<?php echo esc_attr( $post_taxonomy ); ?>" />
			<input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo esc_attr( $post_taxonomy ); ?>" value="<?php echo esc_attr( $taxmy['source'] ); ?>" />
			<button class="button-link close-details">
			<span class="screen-reader-text">
				<?php echo esc_html( __( 'Toggle panel: Taxonomy details', 'post-my-contact-form-7' ) ); ?>
			</span>
			<span class="focus button save-taxonomy">
				<?php echo esc_html( __( 'Save', 'post-my-contact-form-7' ) ); ?>
			</span>
			</button>
		</div>
	</li>

<?php endforeach; // ENDFOREACH $mapped_taxonomy as $cf7_field => $post_taxonomy. ?>
<?php
	/**
	 * Default new taxonomy slug
	 */
	$taxonomy_slug = sanitize_title( $post_mapper->get( 'singular_name' ) ) . '_categories';
?>
<li>
	<div class="custom-taxonomy-field cf7-2-post-field">
	<label class="taxonomy-label-field cf7-2-post-map-labels">
		<span class="taxonomy-name">
		<strong><?php echo esc_html( __( 'New Categories', 'post-my-contact-form-7' ) ); ?></strong>
		</span>&nbsp;(<span class="link-button edit-taxonomy disabled"><?php echo esc_html( __( 'Edit', 'post-my-contact-form-7' ) ); ?></span>)
	</label>
	<select disabled="true" class="field-options taxonomy-options" name="cf7_2_post_map_taxonomy_value-<?php echo esc_attr( $taxonomy_slug ); ?>" value="">
		<option class="default-option" selected="true" value="">
		<?php echo esc_html( __( 'Select a form field', 'post-my-contact-form-7' ) ); ?>
		</option>
		<option class="filter-option" value="cf7_2_post_filter-<?php echo esc_attr( $taxonomy_slug ); ?>">
		<?php echo esc_html( __( 'Hook with a filter', 'post-my-contact-form-7' ) ); ?>
		</option>
	</select>
	<span class="dashicons dashicons-insert add-more-field"></span>
	<span class="php-filter-button"></span>
	</div>
	<span class="cf7-post-msg"></span>
	<div class="display-none custom-taxonomy-input-fields">
	<p>
		<?php echo esc_html( __( 'Choose a taxonomy, in <em>blue</em> are existing public taxonomies', 'post-my-contact-form-7' ) ); ?>
	</p>
	<?php echo wp_kses( $post_mapper->get_taxonomy_listing(), CF72Post_Mapping_Factory::$allowed_html ); ?>
	<label for="cf7_2_post_map_taxonomy_names-<?php echo esc_attr( $taxonomy_slug ); ?>">
		<strong><?php echo esc_html( __( 'Plural Name', 'post-my-contact-form-7' ) ); ?></strong>
	</label>
	<input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo esc_attr( $taxonomy_slug ); ?>" disabled="disabled" value="factory"/>
	<input disabled="disabled" class="c2p-tax-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?php echo esc_attr( $taxonomy_slug ); ?>" value="<?php echo esc_attr( __( 'New Categories', 'post-my-contact-form-7' ) ); ?>">
	<label for="cf7_2_post_map_taxonomy_name-<?php echo esc_attr( $taxonomy_slug ); ?>">
		<strong><?php echo esc_html( __( 'Singular Name', 'post-my-contact-form-7' ) ); ?></strong>
	</label>
	<input disabled="disabled" class="c2p-tax-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo esc_attr( $taxonomy_slug ); ?>" value="New Category">
	<label for="cf7_2_post_map_taxonomy_slug-<?php echo esc_attr( $taxonomy_slug ); ?>">
		<strong><?php echo esc_html( __( 'Slug', 'post-my-contact-form-7' ) ); ?></strong>
	</label>
	<input disabled="disabled" class="c2p-tax-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo esc_attr( $taxonomy_slug ); ?>" value="<?php echo esc_attr( $taxonomy_slug ); ?>" />
	<button class="button-link close-details">
		<span class="focus button save-taxonomy">
			<?php echo esc_html( __( 'Save', 'post-my-contact-form-7' ) ); ?>
		</span>
	</button>
	</div>
</li>
