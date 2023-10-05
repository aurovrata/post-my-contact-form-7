<?php
/**
 * Display Form 2 Post tab settings in CF7 editor page.
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
// action.
$is_new_mapping = true;
switch ( $post_mapper->get( 'map' ) ) {
	case 'draft':
		$is_new_mapping = true;
		break;
	case 'publish':
		$is_new_mapping = false;
		break;
}
$mapped_post_type = $post_mapper->get( 'type' );
$source           = $post_mapper->get( 'type_source' );
$post_name        = '';
$class_factory    = '';
$system_factory   = ' display-none';
switch ( $source ) {
	case 'factory':
		$post_name = $post_mapper->get( 'plural_name' );
		break;
	case 'system':
		$post_obj       = get_post_type_object( $mapped_post_type );
		$post_name      = $post_obj->labels->name;
		$class_factory  = ' display-none';
		$system_factory = '';
		break;
}
?>
<h1>
	<?php echo esc_html( __( 'Save submissions as ', 'post-my-contact-form-7' ) ); ?>
	<span id="custom-post-title"><?php echo esc_html( $post_name ); ?>&colon;&nbsp;
		<code><?php echo esc_html( $mapped_post_type ); ?></code>
	</span>
</h1>
<!-- $form = get_post($cf7_post_id); ?> -->
<input type="hidden" id="c2p-cf7-key" value="<?php echo esc_attr( $post_mapper->cf7_key ); ?>"/>
<input type="hidden" id="c2p-mapping-changed" name="c2p_mapping_changes" value="0"/>
<input type="hidden" id="c2p-active-tab" name="c2p_active_tab" value="0"/>
<input type="hidden" id="c2p-mapping-status" name="mapped_post_map" value="<?php echo esc_attr( $post_mapper->get( 'map' ) ); ?>"/>
<input type="hidden" name="mapped_post_default" value="<?php echo esc_attr( $post_mapper->get( 'default' ) ); ?>"/>
<input name="mapped_post_type"  id="mapped-post-type" value="<?php echo esc_attr( $post_mapper->get( 'type' ) ); ?>" type="hidden">

<?php wp_nonce_field( 'cf7_2_post_mapping', 'cf7_2_post_nonce', false, true ); ?>

<div id="c2p-factory-post">
	<div class="c2p-title-header">
		<h2><?php echo esc_html( __( 'Map form to...', 'post-my-contact-form-7' ) ); ?></h2>
		<div class="toggle toggle-light"></div>
	</div>
	<p>
		<?php echo esc_html( __( 'You can either map your form to a custom post or an existing post type.', 'post-my-contact-form-7' ) ); ?>
	</p>
	<div>
		<label class="post_type_labels" for="post-type-source"><?php echo esc_html( __( 'Post Type:', 'post-my-contact-form-7' ) ); ?></label>
		<span id="post-type-display">
			<select name="mapped_post_type_source" id="post-type-source" class="select-hybrid" >
				<option value="factory" <?php echo esc_html( ( 'factory' === $source ) ? ' selected="true"' : '' ); ?>><?php echo esc_html( __( 'New Post', 'post-my-contact-form-7' ) ); ?></option>
				<option value="system" <?php echo esc_html( ( 'system' === $source ) ? ' selected="true"' : '' ); ?>><?php echo esc_html( __( 'Existing Post', 'post-my-contact-form-7' ) ); ?></option>
			</select>
		</span>
	</div>
	<div id="post-type-exists"<?php echo esc_html( ( 'system' === $source ) ? '' : ' class="display-none"' ); ?>>
		<label class="post_type_labels" for="system-post-type"><?php echo esc_html( __( 'Select a Post', 'post-my-contact-form-7' ) ); ?></label>
		<select id="system-post-type" class="select-hybrid" name="system_post_type" >
			<?php
			echo wp_kses( $factory->get_system_posts_options( $post_mapper->get( 'type' ) ), $factory::$allowed_html );
			?>
		</select>
	</div>
	<div id="post-type-select" <?php echo esc_html( ( 'system' === $source ) ? ' class="display-none"' : '' ); ?>>
		<label for="custom-post-type" class="post-type-labels"><?php echo esc_html( __( 'Post type', 'post-my-contact-form-7' ) ); ?>
			<input name="custom_post_type"  id="custom-post-type" value="<?php echo esc_attr( $post_mapper->get( 'type' ) ); ?>" type="text"/>
		</label>
		<label for="mapped_post_singular_name" class="post_type_labels"><?php echo esc_html( __( 'Singular name', 'post-my-contact-form-7' ) ); ?>
			<input name="mapped_post_singular_name"   id="post_singular_name" value="<?php echo esc_attr( $post_mapper->get( 'singular_name' ) ); ?>" type="text"/>
		</label>
		<label for="post-plural-name" class="post_type_labels"><?php echo esc_html( __( 'Plural name', 'post-my-contact-form-7' ) ); ?>
			<input name="mapped_post_plural_name"  id="post-plural-name" value="<?php echo esc_attr( $post_mapper->get( 'plural_name' ) ); ?>" type="text"/>
		</label>
		<p class="post-type-display">
			<?php echo esc_html( __( 'Attributes', 'post-my-contact-form-7' ) ); ?>
		</p>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'hierarchical', 'checked="checked"' ) ); ?> name="mapped_post_hierarchical" value="true" class="c2cpt-attribute"/> hierarchical
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'public', 'checked="checked"' ) ); ?> name="mapped_post_public" value="true" class="c2cpt-attribute"/>public
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'show_ui', 'checked="checked"' ) ); ?> name="mapped_post_show_ui" value="true" class="c2cpt-attribute"/>show_ui
		</label>
		<label class="post_type_cb_labels">
			<input id="menu-position-checkbox" type="checkbox" <?php echo esc_html( $post_mapper->is( 'show_in_menu', 'checked="checked"' ) ); ?> name="mapped_post_show_in_menu" value="true" class="c2cpt-attribute"/>show_in_menu
		</label>
		<div id="menu-position">
			<label class="post_type_cb_labels">menu_position
				<input style="width:45px;" type="number" value="<?php echo esc_html( $post_mapper->get( 'menu_position' ) ); ?>" size="3" name="mapped_post_menu_position" class="c2cpt-attribute"/>
			</label>
		</div>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'show_in_admin_bar', 'checked="checked"' ) ); ?> name="mapped_post_show_in_admin_bar"  value="true" class="c2cpt-attribute"/>show_in_admin_bar
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'show_in_nav_menus', 'checked="checked"' ) ); ?> name="mapped_post_show_in_nav_menus" value="true" class="c2cpt-attribute"/>show_in_nav_menus
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'can_export', 'checked="checked"' ) ); ?> name="mapped_post_can_export" value="true" class="c2cpt-attribute"/>can_export
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'has_archive', 'checked="checked"' ) ); ?> name="mapped_post_has_archive" value="true" class="c2cpt-attribute"/>has_archive
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'exclude_from_search', 'checked="checked"' ) ); ?> name="mapped_post_exclude_from_search" value="true" class="c2cpt-attribute"/>exclude_from_search
		</label>
		<label class="post_type_cb_labels">
			<input type="checkbox" <?php echo esc_html( $post_mapper->is( 'publicly_queryable', 'checked="checked"' ) ); ?> name="mapped_post_publicly_queryable" value="true" class="c2cpt-attribute"/>publicly_queryable
		</label>
		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: link to codex documentation */
					__( 'To understand how to parametrise your custom post, please read the WordPress post registration <a href="%s">documentation</a>.', 'post-my-contact-form-7' ),
					'https://developer.wordpress.org/reference/functions/register_post_type/#parameter-detail-information'
				),
				array(
					'a' => array( 'href' => array() ),
				)
			);
			?>
		</p>
	</div><!-- end post-type-select -->
</div>
<h2><?php echo esc_html( __( 'Map form fields to default post fields', 'post-my-contact-form-7' ) ); ?></h2>
<div id="c2p-mapped-fields">
	<ul id="c2p-default-post-fields">
	<?php
		$post_fields = array(
			'title'     => __( 'Post title', 'post-my-contact-form-7' ),
			'editor'    => __( 'Post Content', 'post-my-contact-form-7' ),
			'excerpt'   => __( 'Post Excerpt', 'post-my-contact-form-7' ),
			'thumbnail' => __( 'Featured image', 'post-my-contact-form-7' ),
			'slug'      => __( 'Post slug', 'post-my-contact-form-7' ),
			'author'    => __( 'Post author', 'post-my-contact-form-7' ),
		);
		foreach ( $post_fields as $fid => $l ) {
			echo sprintf(
				'<li id="c2p-%2$s">
					<div class="cf7-2-post-field">
						<label class="cf7-2-post-map-labels" for="cf7-2-%2$s"><strong>%1$s</strong></label>
						<select id="cf7-2-%2$s" value="%3$s" name="cf7_2_post_map-%2$s" class="field-options post-options select-hybrid">
							<option class="default-option" value="">' . esc_html( __( 'Select a form field', 'post-my-contact-form-7' ) ) . '</option>
							<option class="filter-option" value="cf7_2_post_filter-%4$s-%2$s">' . esc_html( __( 'Hook with a filter', 'post-my-contact-form-7' ) ) . '</option>
						</select>
					</div><span class="cf7-post-msg"></span>
				</li>',
				esc_html( $l ), // %1 - Label.
				esc_attr( $fid ), // %2 - field id/name.
				esc_attr( $post_mapper->get_mapped_form_field( $fid ) ), // %3 - mapped form field.
				esc_attr( $mapped_post_type ), // %4 - mapped post type.
			);
		}
		?>
	</ul>
	<h2><?php echo esc_html( __( 'Map form fields to post meta-fields', 'post-my-contact-form-7' ) ); ?></h2>
	<ul id="c2p-post-meta-fields">
		<?php require_once 'cf7-2-post-field-metabox.php'; ?>
	</ul>
	<?php echo wp_kses( $factory->get_all_metafield_menus(), $factory::$allowed_html ); ?>
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: link post meta fields documentation page */
				__( 'Custom fields can be used to add extra metadata to a post that you can <a href="%1$s">use in your theme</a>', 'post-my-contact-form-7' ),
				'https://codex.wordpress.org/Using_Custom_Fields'
			),
			array(
				'a' => array( 'href' => array() ),
			),
		);
		?>
		.</p>
	<h2><?php echo esc_html( __( 'Map form fields to post taxonomy', 'post-my-contact-form-7' ) ); ?></h2>
	<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: checkbox/radio/select formm fields */
			__( 'Only %1$s form fields can be mapped to a taxonomy, create the field with empty options and the plugin will populate the field with the taxonomy terms it is mapped to.', 'post-my-contact-form-7' ),
			'<strong>checkbox|radio|select</strong>'
		),
		array( 'strong' => array() ),
	);
	?>
	</p>
	<ul id="c2p-tax-notes">
		<li>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: use class:hybrid-select | [checkbox|radio|select] form fields | link to hybriddropdown plugin page */
			__( 'You can now use the %1$s in your %2$s field tag, to convert your field into a %3$s field', 'post-my-contact-form-7' ),
			'<strong>class:hybrid-select</strong>',
			'<em>[checkbox|radio|select]</em>',
			'<a href="https://aurovrata.github.io/hybrid-html-dropdown/">Hybrid Dropdown</a>'
		),
		array(
			'strong' => array(),
			'em'     => array(),
			'a'      => array( 'href' => array() ),
		),
	);
	?>
		</li>
		<li>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: link select2 documentation */
			__( 'You can now use the %1$s in your %2$s field tag, to convert your field into a %3$s field', 'post-my-contact-form-7' ),
			'<strong>class:js-select2</strong>',
			'<em>[select]</em>',
			'<a href="https://select2.org/">Select2</a>'
		),
		array(
			'strong' => array(),
			'em'     => array(),
			'a'      => array( 'href' => array() ),
		),
	);
	?>
		</li>
		<li>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: link to youtube video */
				__( 'For more details see this <a href="%1$s">YouTube tutorial</a>.', 'post-my-contact-form-7' ),
				'https://www.youtube.com/watch?v=9lK9eHFhGPk&list=PLblJwjs_dFBtQzwPMoMFf-vsXkhuKHKhV'
			),
			array(
				'a' => array( 'href' => array() ),
			),
		);
		?>
		</li>
	</ul>
	<ul id="c2p-taxonomy-fields">
		<?php require_once 'cf7-2-post-taxonomy-metabox.php'; ?>
	</ul>
</div>

<div>
	<?php
	if ( ! defined( 'CF7_GRID_VERSION' ) ) {
		$closed = '';
		echo '<h2>' . esc_html( __( 'Hooks & Filters to customise the mapping', 'post-my-contact-form-7' ) ) . '</h2>';
		include_once 'cf7-2-post-helper-metabox.php';
	}
	?>
</div>
