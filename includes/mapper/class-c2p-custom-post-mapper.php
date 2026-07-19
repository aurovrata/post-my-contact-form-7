<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * admin-facing side of the site and the admin area.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 */

/**
 * Include dependencies.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-c2p-post-mapper.php';

/**
 * Class to handle mapping form -> system post.
 *
 * @since 5.0.0
 */
class C2P_Custom_Post_Mapper extends C2P_Post_Mapper {
	/**
	 * Class contructor.
	 *
	 * @since    1.0.0
	 * @param string                   $cf7_id form id.
	 * @param CF72Post_Mapping_Factory $factory mapper factory pbject.
	 */
	public function __construct( $cf7_id, $factory ) {
		$this->cf7_post_id                    = $cf7_id;
		self::$factory                        = $factory;
		$this->post_properties['type_source'] = 'factory';
	}
	/**
	 * Initialises a default custom post for a new form mapper.
	 *
	 * @since 5.0.0
	 * @param String $cf7_key form slug key.
	 * @param String $singular_name post singular name.
	 * @param String $plural_name post plural name.
	 */
	public function init_default( $cf7_key, $singular_name, $plural_name ) {
		$this->cf7_key = $cf7_key;
		// set some default values.
		$this->post_properties            = array(
			'hierarchical'        => false, // like post.
			'public'              => true, // visible on front-end.
			'show_ui'             => true, // visible in admin dashboard.
			'show_in_menu'        => true, // visible in admin menu.
			'menu_position'       => 5, // position in admin menu.
			'show_in_admin_bar'   => false, // visible in admin bar menu.
			'show_in_nav_menus'   => false, // available in navigation menu.
			'can_export'          => true, // can be exported.
			'has_archive'         => true, // can be archived.
			'exclude_from_search' => true, // cannot be searched from front-end.
			'publicly_queryable'  => false, // can be queried from front-end.
		);
		$this->post_properties['default'] = 0;
		$post_type                        = $this->cf7_key;
		$this->post_properties['map']     = 'draft';
		$this->post_properties['type']    = $post_type;
		if ( strlen( $post_type ) > 20 ) { // fix pho notice for post registration.
			$this->post_properties['type'] = substr( $post_type, 0, 20 );
		}
		$this->post_properties['version']       = CF7_2_POST_VERSION;
		$this->post_properties['singular_name'] = $singular_name;
		$this->post_properties['plural_name']   = $plural_name;
		$this->post_properties['type_source']   = 'factory';
		$this->post_properties['cf7_title']     = $singular_name;

		$this->post_properties['taxonomy'] = array();
		// supports.
		$this->post_properties['supports'] = array(
			'title',
			'editor',
			'excerpt',
			'author',
			'thumbnail',
			'revisions',
			'custom-fields',
		);
		// filter the support capabilities for more user customisation.
		$this->post_properties['supports'] = apply_filters( 'cf7_2_post_supports_' . $post_type, $this->post_properties['supports'] );
		// capabilities.
		$reference    = array(
			'edit_post'          => '',
			'edit_posts'         => '',
			'edit_others_posts'  => '',
			'publish_posts'      => '',
			'read_post'          => '',
			'read_private_posts' => '',
			'delete_post'        => '',
		);
		$capabilities = array_filter( apply_filters( 'cf7_2_post_capabilities_' . $post_type, $reference ) );
		$diff         = array_diff_key( $reference, $capabilities );
		if ( empty( $diff ) ) {
			$this->post_properties['capabilities'] = $capabilities;
			$this->post_properties['map_meta_cap'] = true;
		} else { // some keys are not set, so capabilities will not work.
			// set to defaul post capabilities.
			$this->post_properties['capability_type'] = 'post';
		}
	}
	/**
	 * Set custom properties from $_POST admin submission page.
	 *
	 * @since 5.0.0
	 */
	protected function set_post_properties() {

		// initial supports, set the rest from the admin $_POST.
		$this->post_properties['supports'] = array( 'custom-fields' );
		// make sure the arrays are initialised.
		$len_mapped_post = strlen( 'mapped_post_' );
		$properties      = $this->get_mapped_fields( 'mapped_post_' );
		// properties of factory post.
		foreach ( $properties as $prop => $value ) {
			switch ( $prop ) {
				case 'menu_position': // properties with values.
				case 'type':
				case 'type_source':
				case 'singular_name':
				case 'plural_name':
				case 'default':
				case 'map':
					$this->post_properties[ $prop ] = $value;
					break;
				default: // properties with boolean, unchked are blank and skipped.
					$this->post_properties[ $prop ] = true;
					break;
			}
		}
		// let's save the properties if this is a factory mapping.
		if ( isset( $this->post_properties['default'] ) && $this->post_properties['default'] ) { // default mapping.
			$form  = get_post( $this->cf7_post_id );
			$name  = $form->post_title;
			$names = $name;
			if ( 's' !== substr( $names, -1 ) ) {
				$names .= 's';
			}
			$this->post_properties['singular_name'] = $name;
			$this->post_properties['plural_name']   = $names;
			$this->post_properties['type']          = $this->cf7_key;
			if ( strlen( $this->cf7_key ) > 20 ) { // fix pho notice for post registration.
				$this->post_properties['type'] = substr( $this->cf7_key, 0, 20 );
			}
			$this->post_properties['default'] = 0;
		}
		// let's get the capabilities.
		$reference    = array(
			'edit_post'          => '',
			'edit_posts'         => '',
			'edit_others_posts'  => '',
			'publish_posts'      => '',
			'read_post'          => '',
			'read_private_posts' => '',
			'delete_post'        => '',
		);
		$capabilities = array_filter( apply_filters( 'cf7_2_post_capabilities_' . $this->post_properties['type'], $reference ) );
		$diff         = array_diff_key( $reference, $capabilities );
		if ( empty( $diff ) ) {
			$this->post_properties['capabilities'] = $capabilities;
			$this->post_properties['map_meta_cap'] = true;
		} else { // some keys are not set, so capabilities will not work
			// set to defaul post capabilities.
			$this->post_properties['capability_type'] = 'post';
		}
		// enable support for selected post fields.
		$fields = $this->get_mapped_fields( 'cf7_2_post_map-' );
		foreach ( $fields as $cf7_field => $post_field ) {
			// keep track of custom post type support.
			switch ( $post_field ) {
				case 'title':
				case 'excerpt':
				case 'author':
				case 'thumbnail':
				case 'editor':
					$this->post_properties['supports'][] = $post_field;
					break;
				default:
					break;
			}
		}

		// set flush rules flag. @since 3.8.2.
		if ( $this->get( 'public' ) || $this->get( 'publicly_queryable' ) ) {
			$this->flush_permalink_rules = true;
		}
		return true;
	}
	/**
	 * Setup default mapping.
	 *
	 * @since 5.0.0
	 * @param Array $args array of key=>value arguments.
	 */
	public function init_default_mapping( $args ) {
		$this->post_properties['default'] = 1; // flag this as a default mapping.
		if ( isset( $args['post'] ) ) {
			foreach ( $args['post'] as $pf => $ff ) {
				$this->post_map_fields[ $ff ] = $pf;
			}
		}
		if ( isset( $args['meta'] ) ) {
			foreach ( $args['meta'] as $pf => $ff ) {
				$this->post_map_meta_fields[ $ff ] = $pf;
			}
		}
	}

}
