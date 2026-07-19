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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Ensure variables are defined.
$source        = isset( $source ) ? $source : 'custom';
$post_mapper   = isset( $post_mapper ) ? $post_mapper : null;
$factory       = isset( $factory ) ? $factory : null;

if ( empty( $post_mapper ) || empty( $factory ) ) {
	return;
}

$mapped_fields = $post_mapper->get_mapped_meta_fields();
foreach ( $mapped_fields as $cf7_field => $post_field ) :
	?>
	<li>
		<div class="post-meta-field cf7-2-post-field">
			<div class="post-field-name">
				<?php if ( 'system' === $source ) : ?>
					<?php echo wp_kses( $factory->get_metafield_menu( $post_mapper->get( 'type' ), $post_field ), $factory::$allowed_html ); ?>
				<?php else : ?>
					<input 
						name="cf7_2_post_map_meta-<?php echo esc_attr( $post_field ); ?>" 
						class="cf7-2-post-map-labels" 
						type="text" 
						value="<?php echo esc_attr( $post_field ); ?>"
					/>
				<?php endif; ?>
			</div>

			<?php
			// Display the meta-field's form field dropdown.
			$select_name  = 'cf7_2_post_map_meta_value-' . $post_field;
			$filter_value = 'cf7_2_post_filter-' . $post_mapper->get( 'type' ) . '-' . $post_field;
			$disabled     = '';
			?>
			<select 
				name="<?php echo esc_attr( $select_name ); ?>" 
				data-c2p-ff="<?php echo esc_attr( $cf7_field ); ?>" 
				class="field-options post-options select-hybrid"
				<?php echo esc_attr( $disabled ); ?>
			>
				<option class="default-option" selected value="">
					<?php echo esc_html__( 'Select a form field', 'post-my-contact-form-7' ); ?>
				</option>
				<option class="filter-option" value="<?php echo esc_attr( $filter_value ); ?>">
					<?php echo esc_html__( 'Hook with a filter', 'post-my-contact-form-7' ); ?>
				</option>
			</select>

			<span class="dashicons dashicons-remove remove-field"></span>
		</div>
		<span class="cf7-post-msg"></span>
	</li>
<?php endforeach; ?>

<li class="default-meta-field">
	<div class="post-meta-field cf7-2-post-field">
		<span class="spinner meta-label"></span>
		<div class="post-field-name">
			<?php if ( 'system' === $source ) : ?>
				<?php echo wp_kses( $factory->get_metafield_menu( $post_mapper->get( 'type' ), '' ), $factory::$allowed_html ); ?>
			<?php else : ?>
				<input 
					disabled 
					name="cf7_2_post_map_meta-meta_key_1" 
					class="cf7-2-post-map-labels" 
					type="text" 
					value="meta_key_1"
				/>
			<?php endif; ?>
		</div>

		<?php
		// Display the form field select for new meta field.
		$select_name  = 'cf7_2_post_map_meta_value-meta_key_1';
		$filter_value = 'cf7_2_post_filter-' . $post_mapper->get( 'type' ) . '-meta_key_1';
		?>
		<select 
			disabled 
			name="<?php echo esc_attr( $select_name ); ?>" 
			data-c2p-ff="" 
			class="field-options post-options select-hybrid"
		>
			<option class="default-option" selected value="">
				<?php echo esc_html__( 'Select a form field', 'post-my-contact-form-7' ); ?>
			</option>
			<option class="filter-option" value="<?php echo esc_attr( $filter_value ); ?>">
				<?php echo esc_html__( 'Hook with a filter', 'post-my-contact-form-7' ); ?>
			</option>
		</select>

		<span class="dashicons dashicons-insert add-more-field"></span>
	</div>
</li>