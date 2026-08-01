<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 */

// Include dependencies with conditional loading.
require_once plugin_dir_path( __FILE__ ) . 'mapper/class-c2p-custom-post-mapper.php';
require_once plugin_dir_path( __FILE__ ) . 'mapper/class-c2p-system-post-mapper.php';

/**
 * Factory class for handling mapping functionality.
 *
 * @since 1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class CF72Post_Mapping_Factory {

	/**
	 * Constant for nonce action string.
	 *
	 * @since 5.7.0
	 * @var string
	 */
	const NONCE_ACTION = 'post_my_cf7_form';

	/**
	 * Cache of C2P_Post_Mapper objects for loaded forms.
	 *
	 * @since 5.0.0
	 * @var array
	 */
	protected $post_mappers;

	/**
	 * Track post types mapped to create and add dashboard functionality.
	 *
	 * @since 5.0.0
	 * @var array
	 */
	protected static $mapped_post_types;

	/**
	 * Factory object instance.
	 *
	 * @since 1.0.0
	 * @var CF72Post_Mapping_Factory
	 */
	protected static $factory;

	/**
	 * Allowed HTML for wp_kses function validation.
	 *
	 * @since 5.7.0
	 * @var array
	 */
	public static $allowed_html = array(
		'input'  => array(
			'id'       => array(),
			'name'     => array(),
			'value'    => array(),
			'class'    => array(),
			'type'     => array(),
			'disabled' => array( 'true', 'false' ),
		),
		'select' => array(
			'id'       => array(),
			'name'     => array(),
			'value'    => array(),
			'class'    => array(),
			'disabled' => array( 'true', 'false' ),
		),
		'option' => array(
			'value'    => array(),
			'class'    => array(),
			'selected' => array( 'true', 'false' ),
		),
		'div'    => array(
			'id'    => array(),
			'class' => array(),
		),
	);

	/**
	 * Constructor - protected for singleton pattern.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->post_mappers = array();
		if ( is_admin() ) {
			$this->get_system_posts(); // Only used in dashboard.
		}
	}

	/**
	 * Create a nonce for mapped forms.
	 *
	 * @since 5.7.0
	 * @return string Nonce value.
	 */
	public static function noncify() {
		return wp_create_nonce( self::NONCE_ACTION );
	}

	/**
	 * Check nonce at submission.
	 *
	 * @since 5.7.0
	 * @return bool True if nonce is valid.
	 */
	public static function is_nonce_valid() {
		return isset( $_POST['_c2p_nonce'] ) && 
			wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), self::NONCE_ACTION );
	}

	/**
	 * Factory singleton object getter.
	 *
	 * @since 1.0.0
	 * @return CF72Post_Mapping_Factory
	 */
	public static function get_factory() {
		if ( ! isset( self::$factory ) ) {
			self::$factory = new self();
		}
		return self::$factory;
	}

	/**
	 * Get system posts for admin display.
	 *
	 * @since 5.0.0
	 * @return array|bool Associative array of system post_types => post label.
	 */
	protected function get_system_posts() {
		if ( ! is_admin() ) {
			return false;
		}

		$args       = array( 'show_ui' => true );
		$post_types = get_post_types( $args, 'objects', 'and' );
		$display    = array();

		$excluded_post_types = array( 'wp_block', 'wpcf7_contact_form' );

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type->name, $excluded_post_types, true ) ) {
				continue;
			}
			$display[ $post_type->name ] = $post_type->label;
		}

		/**
		 * Add/remove system posts to which to map forms.
		 *
		 * @since 2.0.0
		 * @param array $display List of system posts to display.
		 * @return array Post-type => label key value pairs.
		 */
		return apply_filters( 'cf7_2_post_display_system_posts', $display );
	}

	/**
	 * Get system post types as <option> elements.
	 *
	 * @since 1.3.0
	 * @param string $selected Selected post type.
	 * @return string HTML list of <option> elements.
	 */
	public function get_system_posts_options( $selected ) {
		$system_pt = $this->get_system_posts();
		if ( ! isset( $system_pt[ $selected ] ) ) {
			$selected = 'post';
		}

		$html = '';
		foreach ( $system_pt as $post_type => $post_label ) {
			$select = selected( $selected, $post_type, false );
			$html  .= sprintf(
				'<option value="%s"%s>%s (%s)</option>',
				esc_attr( $post_type ),
				$select,
				esc_html( $post_label ),
				esc_html( $post_type )
			);
			$html  .= PHP_EOL;
		}
		return $html;
	}

	/**
	 * Get a mapper object for a CF7 form.
	 *
	 * @since 5.0.0
	 * @param int $cf7_post_id CF7 post ID.
	 * @return C2P_Post_Mapper A mapper object.
	 */
	public function get_post_mapper( $cf7_post_id ) {
		// Handle new form (ID = 0).
		if ( 0 === $cf7_post_id ) {
			return $this->create_default_mapper();
		}

		// Return cached mapper if exists.
		if ( isset( $this->post_mappers[ $cf7_post_id ] ) ) {
			return $this->post_mappers[ $cf7_post_id ];
		}

		// Check if CF7 form already has a mapping.
		$post_type = get_post_meta( $cf7_post_id, '_cf7_2_post-type', true );
		
		if ( empty( $post_type ) ) {
			return $this->create_new_mapper( $cf7_post_id );
		}

		return $this->load_existing_mapper( $cf7_post_id, $post_type );
	}

	/**
	 * Create a default mapper for new forms.
	 *
	 * @since 5.3.0
	 * @return C2P_Custom_Post_Mapper
	 */
	private function create_default_mapper() {
		$map   = $this->get_default_mapping();
		$mapper = new C2P_Custom_Post_Mapper( 0, $this );
		$mapper->init_default( $map['type'], $map['name'], $map['names'] );
		$mapper->init_default_mapping( $map );
		return $mapper;
	}

	/**
	 * Create a new mapper for a form without mapping.
	 *
	 * @since 5.3.0
	 * @param int $cf7_post_id CF7 post ID.
	 * @return C2P_Custom_Post_Mapper
	 */
	private function create_new_mapper( $cf7_post_id ) {
		$form = get_post( $cf7_post_id );
		
		$plural_name   = isset( $form ) ? $form->post_title : 'Undefined';
		$singular_name = $plural_name;
		if ( 's' !== substr( $plural_name, -1 ) ) {
			$plural_name .= 's';
		}
		$slug = isset( $form ) ? $form->post_name : 'undefined';

		$mapper = new C2P_Custom_Post_Mapper( $cf7_post_id, $this );
		$mapper->cf7_key = $post_type;
		$mapper->init_default( $slug, $singular_name, $plural_name );
		
		return $mapper;
	}

	/**
	 * Load an existing mapper from database.
	 *
	 * @since 5.3.0
	 * @param int    $cf7_post_id CF7 post ID.
	 * @param string $post_type   Post type.
	 * @return C2P_Post_Mapper
	 */
	private function load_existing_mapper( $cf7_post_id, $post_type ) {
		$post_type_source = get_post_meta( $cf7_post_id, '_cf7_2_post-type_source', true );
		
		// Determine mapper type.
		switch ( $post_type_source ) {
			case 'system':
				$mapper = new C2P_System_Post_Mapper( $cf7_post_id, $this );
				break;
			case 'factory':
			default:
				$mapper = new C2P_Custom_Post_Mapper( $cf7_post_id, $this );
				break;
		}

		$form = get_post( $cf7_post_id );
		$mapper->load_post_mapping( $form->post_name );

		// Load form terms if available.
		$terms = wp_get_post_terms( $cf7_post_id, 'wpcf7_type', array( 'fields' => 'id=>slug' ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$mapper->form_terms = $terms;
		}

		return $mapper;
	}

	/**
	 * Get default mapping configuration.
	 *
	 * @since 5.0.0
	 * @return array Mapping arguments.
	 */
	protected function get_default_mapping() {
		return array(
			'type'  => apply_filters( 'c2p_default_new_form_post_type_mapping', 'contact_form' ),
			'name'  => apply_filters( 'c2p_default_new_form_post_name_mapping', 'Contact Form' ),
			'names' => apply_filters( 'c2p_default_new_form_post_names_mapping', 'Contact Forms' ),
			'post'  => apply_filters(
				'c2p_default_new_form_post_mapping',
				array(
					'title'   => 'your-name',
					'excerpt' => 'your-subject',
					'editor'  => 'your-message',
				)
			),
			'meta'  => apply_filters(
				'c2p_default_new_form_postmeta_mapping',
				array(
					'contact-email' => 'your-email',
				)
			),
		);
	}

	/**
	 * Track mappers in cache.
	 *
	 * @since 5.0.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 */
	public function register( $mapper ) {
		$this->post_mappers[ $mapper->form_id() ] = $mapper;
	}

	/**
	 * Store the mapping in the CF7 post.
	 *
	 * @since 5.0.0
	 * @param int $post_id Post ID.
	 * @return bool True if successful.
	 */
	public function save( $post_id ) {
		// Verify nonce.
		if ( ! isset( $_POST['cf7_2_post_nonce'] ) || 
			 ! wp_verify_nonce( sanitize_key( $_POST['cf7_2_post_nonce'] ), 'cf7_2_post_mapping' ) ) {
			wpg_debug( 'ERROR saving mapping, invalid nonce' );
			return false;
		}

		// Verify user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wpg_debug( 'ERROR saving mapping, insufficient permissions' );
			return false;
		}

		if ( ! isset( $_POST['mapped_post_type_source'] ) ) {
			wpg_debug( 'ERROR: mapped_post_type_source missing, unable to save.' );
			return false;
		}

		$source = sanitize_key( $_POST['mapped_post_type_source'] );
		$mapper = $this->create_mapper_by_source( $post_id, $source );

		if ( ! $mapper || ! $mapper instanceof C2P_Post_Mapper ) {
			wpg_debug( 'ERROR: Unable to determine mapped_post_type_source while saving' );
			return false;
		}

		return $mapper->save_mapping();
	}

	/**
	 * Create mapper by source type.
	 *
	 * @since 5.3.0
	 * @param int    $post_id Post ID.
	 * @param string $source  Source type.
	 * @return C2P_Post_Mapper|null
	 */
	private function create_mapper_by_source( $post_id, $source ) {
		switch ( $source ) {
			case 'system':
				return new C2P_System_Post_Mapper( $post_id, $this );
			case 'factory':
				return new C2P_Custom_Post_Mapper( $post_id, $this );
			default:
				return null;
		}
	}

	/**
	 * Get the CF7 post ID.
	 *
	 * @since 1.0.0
	 * @return int CF7 form post ID.
	 */
	public function get_cf7_post_id() {
		return $this->cf7_post_id;
	}

	/**
	 * Register Custom Post Type based on CF7 mapped properties.
	 *
	 * @since 1.0.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 */
	protected function create_cf7_post_type( C2P_Post_Mapper $mapper ) {
		// Register custom taxonomy.
		$this->register_custom_taxonomies( $mapper );

		// Register custom post type.
		$args = $this->build_post_type_args( $mapper );
		register_post_type( $mapper->post_properties['type'], $args );

		// Link taxonomy and post.
		$this->link_taxonomies_to_post( $mapper );
	}

	/**
	 * Register custom taxonomies for mapper.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 */
	private function register_custom_taxonomies( C2P_Post_Mapper $mapper ) {
		if ( empty( $mapper->post_properties['taxonomy'] ) ) {
			return;
		}

		foreach ( $mapper->post_properties['taxonomy'] as $taxonomy_slug ) {
			if ( 'system' === $mapper->taxonomy_properties[ $taxonomy_slug ]['source'] ) {
				continue;
			}

			$taxonomy = $this->build_taxonomy_args( $mapper, $taxonomy_slug );
			$this->register_custom_taxonomy( $taxonomy, $mapper );
		}
	}

	/**
	 * Build taxonomy arguments.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper        Mapper object.
	 * @param string          $taxonomy_slug Taxonomy slug.
	 * @return array Taxonomy arguments.
	 */
	private function build_taxonomy_args( C2P_Post_Mapper $mapper, $taxonomy_slug ) {
		$taxonomy = array_merge(
			array(
				'hierarchical'       => true,
				'public'             => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_nav_menus'  => true,
				'show_tagcloud'      => true,
				'show_in_quick_edit' => true,
				'menu_name'          => $mapper->taxonomy_properties[ $taxonomy_slug ]['name'],
				'description'        => '',
			),
			$mapper->taxonomy_properties[ $taxonomy_slug ]
		);

		return apply_filters(
			'cf7_2_post_filter_taxonomy_registration-' . $taxonomy_slug,
			$taxonomy
		);
	}

	/**
	 * Build post type arguments.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 * @return array Post type arguments.
	 */
	private function build_post_type_args( C2P_Post_Mapper $mapper ) {
		$labels = $this->build_post_type_labels( $mapper );
		
		// Ensure author is supported.
		if ( ! in_array( 'author', $mapper->post_properties['supports'], true ) ) {
			$mapper->post_properties['supports'][] = 'author';
		}

		$args = array(
			'label'               => $mapper->post_properties['singular_name'],
			'description'         => 'Post for CF7 Form ' . $mapper->post_properties['cf7_title'],
			'labels'              => $labels,
			'supports'            => apply_filters( 
				'cf7_2_post_supports_' . $mapper->post_properties['type'], 
				$mapper->post_properties['supports'] 
			),
			'taxonomies'          => $mapper->post_properties['taxonomy'],
			'hierarchical'        => ! empty( $mapper->post_properties['hierarchical'] ),
			'public'              => ! empty( $mapper->post_properties['public'] ),
			'show_ui'             => ! empty( $mapper->post_properties['show_ui'] ),
			'show_in_menu'        => ! empty( $mapper->post_properties['show_in_menu'] ),
			'menu_position'       => $mapper->post_properties['menu_position'],
			'show_in_admin_bar'   => ! empty( $mapper->post_properties['show_in_admin_bar'] ),
			'show_in_nav_menus'   => ! empty( $mapper->post_properties['show_in_nav_menus'] ),
			'can_export'          => ! empty( $mapper->post_properties['can_export'] ),
			'has_archive'         => ! empty( $mapper->post_properties['has_archive'] ),
			'exclude_from_search' => ! empty( $mapper->post_properties['exclude_from_search'] ),
			'publicly_queryable'  => ! empty( $mapper->post_properties['publicly_queryable'] ),
		);

		// Add capabilities if available.
		$capabilities = $this->get_post_capabilities( $mapper );
		if ( ! empty( $capabilities ) ) {
			$args['capabilities'] = $capabilities;
			$args['map_meta_cap'] = true;
		} else {
			$args['capability_type'] = 'post';
		}

		return apply_filters( 'cf7_2_post_register_post_' . $mapper->post_properties['type'], $args );
	}

	/**
	 * Build post type labels.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 * @return array Labels.
	 */
	private function build_post_type_labels( C2P_Post_Mapper $mapper ) {
		$singular = $mapper->post_properties['singular_name'];
		$plural   = $mapper->post_properties['plural_name'];

		return array(
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => $singular . ' Archives',
			'parent_item_colon'     => 'Parent ' . $singular . ':',
			'all_items'             => 'All ' . $plural,
			'add_new_item'          => 'Add New ' . $singular,
			'add_new'               => 'Add New',
			'new_item'              => 'New ' . $singular,
			'edit_item'             => 'Edit ' . $singular,
			'update_item'           => 'Update ' . $singular,
			'view_item'             => 'View ' . $singular,
			'search_items'          => 'Search ' . $singular,
			'not_found'             => 'Not found',
			'not_found_in_trash'    => 'Not found in Trash',
			'featured_image'        => 'Featured Image',
			'set_featured_image'    => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image'    => 'Use as featured image',
			'insert_into_item'      => 'Insert into ' . $singular,
			'uploaded_to_this_item' => 'Uploaded to this ' . $singular,
			'items_list'            => $plural . ' list',
			'items_list_navigation' => $plural . ' list navigation',
			'filter_items_list'     => 'Filter ' . $plural . ' list',
		);
	}

	/**
	 * Get post capabilities.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 * @return array Capabilities.
	 */
	private function get_post_capabilities( C2P_Post_Mapper $mapper ) {
		$reference = array(
			'edit_post'          => '',
			'edit_posts'         => '',
			'edit_others_posts'  => '',
			'publish_posts'      => '',
			'read_post'          => '',
			'read_private_posts' => '',
			'delete_post'        => '',
		);

		$capabilities = array_filter(
			apply_filters( 'cf7_2_post_capabilities_' . $mapper->post_properties['type'], $reference )
		);

		$diff = array_diff_key( $reference, $capabilities );
		return empty( $diff ) ? $capabilities : array();
	}

	/**
	 * Link taxonomies to post type.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 */
	private function link_taxonomies_to_post( C2P_Post_Mapper $mapper ) {
		foreach ( $mapper->post_properties['taxonomy'] as $taxonomy_slug ) {
			register_taxonomy_for_object_type( $taxonomy_slug, $mapper->post_properties['type'] );
		}
	}

	/**
	 * Get mapped post types.
	 *
	 * @since 3.4.0
	 * @return array $cf7_post_id => array($post_type => [factory|system|filter]).
	 */
	public static function get_mapped_post_types() {
		if ( isset( self::$mapped_post_types ) ) {
			return self::$mapped_post_types;
		}

		global $wpdb;
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id AS ID, pm.meta_value AS origin, pt.meta_value as type 
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->postmeta} pt ON pt.post_id = pm.post_id 
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
				AND pt.meta_key = %s
				AND p.post_status = %s",
				'_cf7_2_post-type_source',
				'_cf7_2_post-type',
				'publish'
			)
		);

		self::$mapped_post_types = array();
		foreach ( $results as $post ) {
			if ( 'filter' === $post->origin ) {
				continue;
			}
			self::$mapped_post_types[ $post->ID ] = array( $post->type => $post->origin );
		}

		return self::$mapped_post_types;
	}

	/**
	 * Check if a post type is mapped.
	 *
	 * @since 3.4.0
	 * @param string $post_type Post type to check.
	 * @param string $source    Optional source filter.
	 * @return int|bool Form post ID or false.
	 */
	public function is_mapped_post_types( $post_type, $source = null ) {
		if ( ! isset( self::$mapped_post_types ) ) {
			self::get_mapped_post_types();
		}

		foreach ( self::$mapped_post_types as $post_id => $type_data ) {
			$ptype = key( $type_data );
			if ( $post_type !== $ptype ) {
				continue;
			}

			if ( empty( $source ) || $source === $type_data[ $ptype ] ) {
				return $post_id;
			}
		}

		return false;
	}

	/**
	 * Update mapped post types when status changes.
	 *
	 * @since 3.4.0
	 * @param int    $cf7_post_id Form post ID.
	 * @param string $status      Mapping status.
	 */
	public static function update_mapped_post_types( $cf7_post_id, $status = 'delete' ) {
		switch ( $status ) {
			case 'delete':
				unset( self::$mapped_post_types[ $cf7_post_id ] );
				break;
			case 'publish':
				update_post_meta( $cf7_post_id, '_cf7_2_post-map', $status );
				$type   = get_post_meta( $cf7_post_id, '_cf7_2_post-type', true );
				$source = get_post_meta( $cf7_post_id, '_cf7_2_post-type_source', true );
				self::$mapped_post_types[ $cf7_post_id ] = array( $type, $source );
				break;
			case 'draft':
				update_post_meta( $cf7_post_id, '_cf7_2_post-map', $status );
				unset( self::$mapped_post_types[ $cf7_post_id ] );
				break;
		}
	}

	/**
	 * Dynamically register custom posts.
	 * Hooks 'init' action.
	 *
	 * @since 1.0.0
	 */
	public function register_cf7_post_maps() {
		$cf7_post_ids = self::get_mapped_post_types();
		$unique_posts = array();

		foreach ( $cf7_post_ids as $pid => $type ) {
			$post_type = key( $type );
			$mapper    = $this->get_post_mapper( $pid );

			$is_system = $this->register_mapped_post( $mapper, $post_type, $type );
			
			// Notify other plugins.
			do_action(
				'cf72post_register_mapped_post',
				$post_type,
				$is_system,
				$mapper->cf7_key,
				$pid,
				isset( $unique_posts[ $post_type ] )
			);

			// Add save filter for new post types.
			if ( ! isset( $unique_posts[ $post_type ] ) ) {
				$this->add_save_post_filter( $post_type );
			}

			$unique_posts[ $post_type ] = 1;
		}
	}

	/**
	 * Register a mapped post type.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper    Mapper object.
	 * @param string          $post_type Post type.
	 * @param array           $type_data Type data.
	 * @return bool True if system post.
	 */
	private function register_mapped_post( C2P_Post_Mapper $mapper, $post_type, $type_data ) {
		$is_system = true;

		switch ( $type_data[ $post_type ] ) {
			case 'factory':
				$this->create_cf7_post_type( $mapper );
				
				// Flush permalink rules if needed.
				if ( $mapper->flush_permalink_rules ) {
					flush_rewrite_rules();
					update_post_meta( $mapper->form_id(), '_cf7_2_post_flush_rewrite_rules', false );
					$mapper->flush_permalink_rules = false;
				}
				$is_system = false;
				break;

			case 'system':
				// Link system taxonomy.
				$taxonomies = get_post_meta( $mapper->form_id(), '_cf7_2_post-taxonomy', true );
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
					foreach ( $taxonomies as $taxonomy_slug ) {
						register_taxonomy_for_object_type( $taxonomy_slug, $post_type );
					}
				}
				break;
		}

		return $is_system;
	}

	/**
	 * Add save post filter for new post types.
	 *
	 * @since 5.3.0
	 * @param string $post_type Post type.
	 */
	private function add_save_post_filter( $post_type ) {
		add_action(
			'save_post_' . $post_type,
			function( $post_id, $post, $update ) {
				if ( $update ) {
					return $post_id;
				}
				$cf7_flag = get_post_meta( $post_id, '_cf7_2_post_form_submitted', true );
				if ( empty( $cf7_flag ) ) {
					update_post_meta( $post_id, '_cf7_2_post_form_submitted', 'yes' );
				}
				return $post_id;
			},
			10,
			3
		);
	}

	/**
	 * Check if a form mapping is published.
	 *
	 * @since 2.0.0
	 * @param int $cf7_post_id Form post ID.
	 * @return bool True if mapped.
	 */
	public function is_mapped( $cf7_post_id ) {
		$map = get_post_meta( $cf7_post_id, '_cf7_2_post-map', true );
		return in_array( $map, array( 'draft', 'publish' ), true );
	}

	/**
	 * Check if a form mapping is live.
	 *
	 * @since 2.0.0
	 * @param int $cf7_post_id Form ID.
	 * @return bool True if mapping is live and accepting submissions.
	 */
	public function is_live( $cf7_post_id ) {
		$map = get_post_meta( $cf7_post_id, '_cf7_2_post-map', true );
		
		if ( 'publish' === $map ) {
			return true;
		}

		$cf7_key = cf7sg_get_form_key( $cf7_post_id );
		return apply_filters( 'cf7_2_post_save_draft_mapping', false, $cf7_key );
	}

	/**
	 * Check if mapping uses a filter.
	 *
	 * @since 5.4.3
	 * @param int $cf7_post_id Form ID.
	 * @return bool True if mapped using a filter.
	 */
	public function is_filter( $cf7_post_id ) {
		return 'filter' === get_post_meta( $cf7_post_id, '_cf7_2_post-type_source', true );
	}

	/**
	 * Get form values for pre-filling.
	 *
	 * @since 1.3.0
	 * @param int    $form_id        Form ID.
	 * @param int    $cf7_2_post_id  Specific post ID.
	 * @return array Form field => value pairs.
	 */
	public function get_form_values( $form_id, $cf7_2_post_id = '' ) {
		$mapper = $this->get_post_mapper( $form_id );
		$mapper->load_form_fields();

		$post = $this->get_user_post( $mapper, $cf7_2_post_id );
		$load_saved_values = ! empty( $post );

		$field_and_values = $this->load_post_field_values( $mapper, $post, $load_saved_values );
		$field_and_values = $this->load_meta_field_values( $mapper, $post, $load_saved_values, $field_and_values );
		$field_and_values = $this->load_taxonomy_values( $mapper, $post, $load_saved_values, $field_and_values );
		$field_and_values = $this->load_unmapped_field_values( $mapper, $field_and_values );

		/**
		 * Filter form values.
		 *
		 * @since 1.3.0
		 * @param array  $field_and_values Field values.
		 * @param int    $cf7_post_id      Form ID.
		 * @param string $post_type        Post type.
		 * @param string $cf7_key          Form key.
		 * @param object $post             Post object.
		 * @param array  $cf7_form_fields  Form fields.
		 * @return array Filtered field values.
		 */
		$field_and_values = apply_filters(
			'cf7_2_post_form_values',
			$field_and_values,
			$mapper->cf7_post_id,
			$mapper->post_properties['type'],
			$mapper->cf7_key,
			$post,
			$mapper->get_cf7_form_fields()
		);

		return $field_and_values;
	}

	/**
	 * Get user post for form pre-filling.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper         Mapper object.
	 * @param int             $cf7_2_post_id  Specific post ID.
	 * @return object|null Post object.
	 */
	private function get_user_post( $mapper, $cf7_2_post_id ) {
		$args = array(
			'posts_per_page' => 1,
			'post_type'      => $mapper->post_properties['type'],
			'post_status'    => 'any',
		);

		if ( ! empty( $cf7_2_post_id ) ) {
			$args['post__in'] = array( $cf7_2_post_id );
		}

		// Filter by submission value for newer versions.
		if ( version_compare( CF7_2_POST_VERSION, $mapper->post_properties['version'], '>=' ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_cf7_2_post_form_submitted',
					'value'   => 'no',
					'compare' => 'LIKE',
				),
			);
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$args['author'] = $user->ID;
		} else {
			$args = array();
		}

		$args = apply_filters(
			'cf7_2_post_filter_user_draft_form_query',
			$args,
			$mapper->post_properties['type'],
			$mapper->cf7_key
		);

		if ( empty( $args ) ) {
			return null;
		}

		$posts = get_posts( $args );
		return ! empty( $posts ) ? $posts[0] : null;
	}

	/**
	 * Load post field values.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper            Mapper object.
	 * @param object|null     $post              Post object.
	 * @param bool            $load_saved_values Whether to load saved values.
	 * @return array Field values.
	 */
	private function load_post_field_values( $mapper, $post, $load_saved_values ) {
		$field_and_values = array();

		if ( $load_saved_values && $post ) {
			/** @since  */
			$transient = wp_hash('cf7_2_post_' . $post->ID, 'nonce'); // setup a unique key as a transient.
			set_transient( $transient, $post->ID, DAY_IN_SECONDS ); // store the post ID for 24 hours.
			$field_and_values['map_post_id'] = $transient; //pass the transient key to the form.
			wp_reset_postdata();
		}

		$field_map = array(
			'title'   => 'post_title',
			'author'  => 'post_author',
			'excerpt' => 'post_excerpt',
			'editor'  => 'post_content',
			'slug'    => 'post_name',
		);

		foreach ( $mapper->get_post_map_fields() as $form_field => $post_field ) {
			if ( $this->is_filtered_field( $form_field ) ) {
				continue;
			}

			$post_value = '';
			$post_key   = isset( $field_map[ $post_field ] ) ? $field_map[ $post_field ] : '';

			if ( $load_saved_values && ! empty( $post_key ) && $post ) {
				$post_value = $post->{$post_key};
			} else {
				$post_value = apply_filters(
					'cf7_2_post_filter_cf7_field_value',
					$post_value,
					$mapper->cf7_post_id,
					$form_field,
					$mapper->cf7_key,
					$mapper->form_terms
				);
			}

			if ( ! empty( $post_value ) ) {
				$field_and_values[ $form_field ] = $post_value;
			}
		}

		return $field_and_values;
	}

	/**
	 * Load meta field values.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper            Mapper object.
	 * @param object|null     $post              Post object.
	 * @param bool            $load_saved_values Whether to load saved values.
	 * @param array           $field_and_values  Existing field values.
	 * @return array Updated field values.
	 */
	private function load_meta_field_values( $mapper, $post, $load_saved_values, $field_and_values ) {
		foreach ( $mapper->get_post_map_meta_fields() as $form_field => $post_field ) {
			if ( $this->is_filtered_field( $form_field ) ) {
				continue;
			}
			$post_value = '';
			if( $mapper->get_form_field_type($form_field) === 'file' ) {
				if( false === apply_filters( 'c2p_prefill_form_file_field', false, $form_field, $mapper->get_cf7_key() ) ) {
					continue; //don't prefill file fields by default.
				}
			}

			if ( $load_saved_values && $post ) {
				$post_value = get_post_meta( $post->ID, $post_field, true );
			} else {
				$post_value = apply_filters(
					'cf7_2_post_filter_cf7_field_value',
					$post_value,
					$mapper->cf7_post_id,
					$form_field,
					$mapper->cf7_key,
					$mapper->form_terms
				);
			}

			if ( ! empty( $post_value ) ) {
				$field_and_values[ $form_field ] = $post_value;
			}
		}

		return $field_and_values;
	}

	/**
	 * Load unmapped field values.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper           Mapper object.
	 * @param array           $field_and_values Existing field values.
	 * @return array Updated field values.
	 */
	private function load_unmapped_field_values( $mapper, $field_and_values ) {
		$cf7_form_fields = $mapper->get_cf7_form_fields();
		
		$unmapped_fields = array_diff_key( //remove mapped fields.
			$cf7_form_fields,
			$mapper->get_post_map_meta_fields(),
			$mapper->get_post_map_fields(),
			$mapper->get_post_map_taxonomy()
		);

		foreach ( $unmapped_fields as $form_field => $type ) {
			if ( 'submit' === $type ) {
				continue;
			}

			$post_value = apply_filters(
				'cf7_2_post_filter_cf7_field_value',
				'',
				$mapper->cf7_post_id,
				$form_field,
				$mapper->cf7_key,
				$mapper->form_terms
			);

			if ( ! empty( $post_value ) ) {
				$field_and_values[ $form_field ] = $post_value;
			}
		}

		return $field_and_values;
	}

	/**
	 * Load taxonomy values.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper            Mapper object.
	 * @param object|null     $post              Post object.
	 * @param bool            $load_saved_values Whether to load saved values.
	 * @param array           $field_and_values  Existing field values.
	 * @return array Updated field values.
	 */
	private function load_taxonomy_values( $mapper, $post, $load_saved_values, $field_and_values ) {
		$cf7_form_fields = $mapper->get_cf7_form_fields();

		foreach ( $mapper->get_post_map_taxonomy() as $form_field => $taxonomy ) {
			if ( $this->is_filtered_field( $form_field ) ) {
				continue;
			}

			$terms_id = array();

			if ( $load_saved_values && $post ) {
				$terms = get_the_terms( $post, $taxonomy );
				$terms_id = ! empty( $terms ) ? wp_list_pluck( $terms, 'term_id' ) : array();
			} else {
				$terms_id = apply_filters(
					'cf7_2_post_filter_cf7_taxonomy_terms',
					$terms_id,
					$mapper->cf7_post_id,
					$form_field,
					$mapper->cf7_key
				);
				if ( is_string( $terms_id ) ) {
					$terms_id = array( $terms_id );
				}
			}

			$field_and_values[ $form_field ] = $this->get_taxonomy_options(
				$mapper,
				$form_field,
				$taxonomy,
				$terms_id,
				$cf7_form_fields
			);
		}

		return $field_and_values;
	}

	/**
	 * Get taxonomy options for form field.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper         Mapper object.
	 * @param string          $form_field     Form field name.
	 * @param string          $taxonomy       Taxonomy slug.
	 * @param array           $terms_id       Selected term IDs.
	 * @param array           $cf7_form_fields All form fields.
	 * @return array|string Taxonomy options.
	 */
	private function get_taxonomy_options( $mapper, $form_field, $taxonomy, $terms_id, $cf7_form_fields ) {
		$field_type = $cf7_form_fields[ $form_field ];
		$is_hybrid  = $mapper->field_has_class( $form_field, 'hybrid-select' );

		if ( $is_hybrid ) {
			wp_enqueue_script( 'hybriddd-js' );
			wp_enqueue_style( 'hybriddd-style' );
		}

		$branch = 0;
		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$branch = array( 0 );
		}

		if ( $is_hybrid && 'select' !== $field_type ) {
			return $this->get_hybrid_dropdown_options( $mapper, $taxonomy, $branch, $form_field, $terms_id );
		}

		return $this->get_standard_taxonomy_options( $mapper, $taxonomy, $branch, $terms_id, $form_field, $field_type );
	}

	/**
	 * Get hybrid dropdown options.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper     Mapper object.
	 * @param string          $taxonomy   Taxonomy slug.
	 * @param array|int       $branch     Branch data.
	 * @param string          $form_field Form field name.
	 * @param array           $terms_id   Selected term IDs.
	 * @return array Hybrid dropdown options.
	 */
	private function get_hybrid_dropdown_options( $mapper, $taxonomy, $branch, $form_field, $terms_id ) {
		$limit = ( 'checkbox' === $mapper->get_cf7_form_fields()[ $form_field ] ) ? -1 : 1;
		
		$hdd = array(
			'limitSelection' => $limit,
			'fieldName'      => $form_field,
			'selectedValues' => $terms_id,
			'dataSet'        => array( '' => __( 'Select an item', 'post-my-contact-form-7' ) ),
		);

		$hdd = (array) apply_filters(
			'cf72post_filter_hybriddd_options',
			$hdd,
			$form_field,
			$mapper->cf7_key
		);

		$hdd['dataSet'] = $hdd['dataSet'] + $this->build_hybrid_dropdown(
			$taxonomy,
			$branch,
			'',
			$form_field,
			$mapper
		);

		return $hdd;
	}

	/**
	 * Get standard taxonomy options.
	 *
	 * @since 5.3.0
	 * @param C2P_Post_Mapper $mapper     Mapper object.
	 * @param string          $taxonomy   Taxonomy slug.
	 * @param array|int       $branch     Branch data.
	 * @param array           $terms_id   Selected term IDs.
	 * @param string          $form_field Form field name.
	 * @param string          $field_type Field type.
	 * @return string Taxonomy options HTML.
	 */
	private function get_standard_taxonomy_options( $mapper, $taxonomy, $branch, $terms_id, $form_field, $field_type ) {
		$options = $this->get_taxonomy_terms(
			$taxonomy,
			$branch,
			$terms_id,
			$form_field,
			$field_type,
			0,
			$mapper
		);

		// Enqueue select2 if applicable.
		if ( 'select' === $field_type ) {
			$apply_select = apply_filters(
				'cf7_2_post_filter_cf7_taxonomy_chosen_select',
				true,
				$mapper->cf7_post_id,
				$form_field,
				$mapper->cf7_key
			) && apply_filters(
				'cf7_2_post_filter_cf7_taxonomy_select2',
				true,
				$mapper->cf7_post_id,
				$form_field,
				$mapper->cf7_key
			);

			if ( $apply_select ) {
				$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
				wp_enqueue_script(
					'jquery-select2',
					$plugin_url . 'assets/select2/js/select2.min.js',
					array( 'jquery' ),
					CF7_2_POST_VERSION,
					true
				);
				wp_enqueue_style(
					'jquery-select2',
					$plugin_url . 'assets/select2/css/select2.min.css',
					array(),
					CF7_2_POST_VERSION
				);
			}
		}

		return wp_json_encode( $options );
	}

	/**
	 * Check if a field is filtered.
	 *
	 * @since 5.3.0
	 * @param string $form_field Form field name.
	 * @return bool True if filtered.
	 */
	private function is_filtered_field( $form_field ) {
		return 0 === strpos( $form_field, 'cf7_2_post_filter-' );
	}

	/**
	 * Normalize field names.
	 *
	 * @since 5.3.0
	 * @param array $field_and_values Field values.
	 * @return array Normalized field values.
	 */
	public function encode_field_names( $field_and_values ) {
		$return_values = array();
		foreach ( $field_and_values as $field => $value ) {
			$normalized = str_replace( '-', '_', $field );
			$return_values[ $normalized ] = $value;
		}
		return $return_values;
	}

	/**
	 * Get form field script.
	 *
	 * @since 1.3.0
	 * @param string          $nonce  Nonce string.
	 * @param C2P_Post_Mapper $mapper Mapper object.
	 * @return string Script content.
	 */
	public function get_form_field_script( $nonce, $mapper ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . '/partials/cf7-2-post-script.php';
		$script = ob_get_contents();
		ob_end_clean();
		return $script;
	}

	/**
	 * Build hybrid dropdown options.
	 *
	 * @since 5.0.0
	 * @param string          $taxonomy Taxonomy slug.
	 * @param mixed           $branch   Parent IDs for hierarchical taxonomies.
	 * @param string          $pslug    Parent slug.
	 * @param string          $field    Form field name.
	 * @param C2P_Post_Mapper $mapper   Mapper object.
	 * @return array Value->label pairs for hybrid dropdown.
	 */
	protected function build_hybrid_dropdown( $taxonomy, $branch, $pslug, $field, $mapper ) {
		$terms = $this->filter_taxonomy_query( $taxonomy, $branch, $field, $mapper );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		$options = array();
		foreach ( $terms as $term ) {
			$id    = $term->term_id;
			$label = apply_filters(
				'cf72post_filter_hybriddd_term_attributes',
				array(),
				$term,
				$field,
				$mapper->cf7_key
			);

			$classes = 'term-' . $id . ' slug-' . $term->slug;
			$kids    = array();

			if ( is_array( $branch ) ) {
				array_pop( $branch );
				$branch[] = $term->parent;
				$classes .= ( $term->parent > 0 ? ' parent-slug-' . $pslug . ' parent-term-' . $term->parent : '' );
				$kids = $this->build_hybrid_dropdown(
					$taxonomy,
					array_merge( $branch, array( $id ) ),
					$term->slug,
					$field,
					$mapper
				);
			}

			$options[ $id ] = array_merge(
				array( 'label' => array( $term->name, $classes ) + $label ),
				$kids
			);
		}

		return $options;
	}

	/**
	 * Filter taxonomy query for mapped taxonomy fields.
	 *
	 * @since 5.0.0
	 * @param string          $taxonomy Taxonomy slug.
	 * @param array           $branch  Parent ID of child terms.
	 * @param string          $field   Form field name.
	 * @param C2P_Post_Mapper $mapper  Mapper object.
	 * @return array|string Terms or empty string.
	 */
	public function filter_taxonomy_query( $taxonomy, $branch, $field, $mapper ) {
		$args = array( 'hide_empty' => 0 );

		if ( is_array( $branch ) ) {
			$args['parent'] = end( $branch );
		}

		$args = apply_filters(
			'cf7_2_post_filter_taxonomy_query',
			$args,
			$mapper->cf7_post_id,
			$taxonomy,
			$field,
			$mapper->cf7_key,
			$branch
		);

		if ( empty( $args ) ) {
			return '';
		}

		global $wp_version;
		if ( $wp_version >= 4.5 ) {
			$args['taxonomy'] = $taxonomy;
			$terms = get_terms( $args );
		} else {
			$terms = get_terms( $taxonomy, $args );
		}

		return $terms;
	}

	/**
	 * Get taxonomy terms for form field.
	 *
	 * @since 1.2.0
	 * @param string          $taxonomy   Taxonomy slug.
	 * @param mixed           $branch     Parent IDs or 0.
	 * @param array           $post_terms Terms assigned to post.
	 * @param string          $field      Form field name.
	 * @param string          $field_type Field type.
	 * @param int             $level      Nesting level.
	 * @param C2P_Post_Mapper $mapper     Mapper object.
	 * @return string HTML for taxonomy terms.
	 */
	protected function get_taxonomy_terms( $taxonomy, $branch, $post_terms, $field, $field_type, $level, $mapper ) {
		$terms = $this->filter_taxonomy_query( $taxonomy, $branch, $field, $mapper );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		if ( is_array( $branch ) ) {
			$parent = end( $branch );
		} else {
			$parent = $branch;
		}

		$script     = '';
		$term_class = '';

		// Wrap checkbox/radio fields in fieldset.
		if ( 'select' !== $field_type ) {
			$term_class = 'cf72post-' . $taxonomy;
			$script = '<fieldset class="c2p-top-level ' . $term_class . '">';
			if ( $parent > 0 ) {
				$script = '<fieldset class="cf72post-child-terms parent-term-' . $parent . '">';
				$term_class .= ' cf72post-child-term';
			}
		}

		foreach ( $terms as $term ) {
			$script .= $this->render_term( $term, $field, $field_type, $post_terms, $term_class, $level, $mapper, $taxonomy, $branch );
		}

		if ( 'select' !== $field_type ) {
			$script .= '</fieldset>';
		}

		return $script;
	}

	/**
	 * Render a single term.
	 *
	 * @since 5.3.0
	 * @param WP_Term         $term       Term object.
	 * @param string          $field      Form field name.
	 * @param string          $field_type Field type.
	 * @param array           $post_terms Post terms.
	 * @param string          $term_class Term class.
	 * @param int             $level      Nesting level.
	 * @param C2P_Post_Mapper $mapper     Mapper object.
	 * @param string          $taxonomy   Taxonomy slug.
	 * @param mixed           $branch     Branch data.
	 * @return string Term HTML.
	 */
	private function render_term( $term, $field, $field_type, $post_terms, $term_class, $level, $mapper, $taxonomy, $branch ) {
		$term_id = $term->term_id;
		$script = '';

		// Apply filters for custom classes and attributes.
		$custom_classes = apply_filters(
			'cf72post_filter_taxonomy_term_class',
			array(),
			$term,
			$level,
			$field,
			$mapper->cf7_key
		);

		if ( ! empty( $custom_classes ) && is_array( $custom_classes ) ) {
			$term_class .= ' ' . implode( ' ', $custom_classes );
		}

		$custom_attributes = apply_filters(
			'cf72post_filter_taxonomy_term_attributes',
			array(),
			$term,
			$level,
			$field,
			$mapper->cf7_key
		);

		$attributes = '';
		if ( ! empty( $custom_attributes ) && is_array( $custom_attributes ) ) {
			foreach ( $custom_attributes as $attr => $value ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( (string) $value ) . '"';
			}
		}

		$is_selected = in_array( $term_id, $post_terms, true );

		switch ( $field_type ) {
			case 'select':
				$script .= $this->render_select_term( $term, $term_id, $term_class, $attributes, $is_selected );
				break;
			case 'radio':
				$script .= $this->render_radio_term( $term, $term_id, $field, $term_class, $attributes, $is_selected );
				break;
			case 'checkbox':
				$script .= $this->render_checkbox_term( $term, $term_id, $field, $term_class, $attributes, $is_selected, $mapper );
				break;
			default:
				return '';
		}

		// Render children for hierarchical taxonomies.
		if ( is_array( $branch ) ) {
			array_pop( $branch );
			$branch[] = $term->parent;
			$script .= $this->get_taxonomy_terms(
				$taxonomy,
				array_merge( $branch, array( $term_id ) ),
				$post_terms,
				$field,
				$field_type,
				$level + 1,
				$mapper
			);
		}

		return $script;
	}

	/**
	 * Render select option term.
	 *
	 * @since 5.3.0
	 * @param WP_Term $term       Term object.
	 * @param int     $term_id    Term ID.
	 * @param string  $term_class Term class.
	 * @param string  $attributes Custom attributes.
	 * @param bool    $is_selected Whether term is selected.
	 * @return string HTML.
	 */
	private function render_select_term( $term, $term_id, $term_class, $attributes, $is_selected ) {
		$selected = $is_selected ? ' selected="selected"' : '';
		return sprintf(
			'<option%s class="%s" value="%s"%s>%s</option>',
			$attributes,
			esc_attr( $term_class ),
			esc_attr( $term_id ),
			$selected,
			esc_html( $term->name )
		);
	}

	/**
	 * Render radio term.
	 *
	 * @since 5.3.0
	 * @param WP_Term $term       Term object.
	 * @param int     $term_id    Term ID.
	 * @param string  $field      Field name.
	 * @param string  $term_class Term class.
	 * @param string  $attributes Custom attributes.
	 * @param bool    $is_selected Whether term is selected.
	 * @return string HTML.
	 */
	private function render_radio_term( $term, $term_id, $field, $term_class, $attributes, $is_selected ) {
		$checked = $is_selected ? ' checked' : '';
		return sprintf(
			'<div id="%s" class="radio-term"><label><input%s type="radio" name="%s" value="%s" class="%s"%s/>%s</label></div>',
			esc_attr( $term->slug ),
			$attributes,
			esc_attr( $field ),
			esc_attr( $term_id ),
			esc_attr( $term_class ),
			$checked,
			esc_html( $term->name )
		);
	}

	/**
	 * Render checkbox term.
	 *
	 * @since 5.3.0
	 * @param WP_Term         $term       Term object.
	 * @param int             $term_id    Term ID.
	 * @param string          $field      Field name.
	 * @param string          $term_class Term class.
	 * @param string          $attributes Custom attributes.
	 * @param bool            $is_selected Whether term is selected.
	 * @param C2P_Post_Mapper $mapper     Mapper object.
	 * @return string HTML.
	 */
	private function render_checkbox_term( $term, $term_id, $field, $term_class, $attributes, $is_selected, $mapper ) {
		$checked = $is_selected ? ' checked' : '';
		$field_name = $field;
		if ( ! $mapper->field_has_option( $field, 'exclusive' ) ) {
			$field_name = $field . '[]';
		}

		return sprintf(
			'<div id="%s" class="checkbox-term"><label><input%s type="checkbox" name="%s" value="%s" class="%s"%s/>%s</label></div>',
			esc_attr( $term->slug ),
			$attributes,
			esc_attr( $field_name ),
			esc_attr( $term_id ),
			esc_attr( $term_class ),
			$checked,
			esc_html( $term->name )
		);
	}

	/**
	 * Register a custom taxonomy.
	 *
	 * @since 2.0.0
	 * @param array           $taxonomy Taxonomy arguments.
	 * @param C2P_Post_Mapper $mapper   Mapper object.
	 */
	protected function register_custom_taxonomy( array $taxonomy, C2P_Post_Mapper $mapper ) {
		$labels = $this->build_taxonomy_labels( $taxonomy );
		
		$args = array(
			'labels'             => $labels,
			'hierarchical'       => $taxonomy['hierarchical'],
			'public'             => $taxonomy['public'],
			'show_ui'            => $taxonomy['show_ui'],
			'show_admin_column'  => $taxonomy['show_admin_column'],
			'show_in_nav_menus'  => $taxonomy['show_in_nav_menus'],
			'show_tagcloud'      => $taxonomy['show_tagcloud'],
			'show_in_quick_edit' => $taxonomy['show_in_quick_edit'],
			'description'        => $taxonomy['description'],
		);

		// Optional arguments.
		if ( isset( $taxonomy['meta_box_cb'] ) ) {
			$args['meta_box_cb'] = $taxonomy['meta_box_cb'];
		}
		if ( isset( $taxonomy['update_count_callback'] ) ) {
			$args['update_count_callback'] = $taxonomy['update_count_callback'];
		}
		if ( isset( $taxonomy['capabilities'] ) ) {
			$args['capabilities'] = $taxonomy['capabilities'];
		}

		$post_types = apply_filters(
			'cf7_2_post_filter_taxonomy_register_post_type',
			array( $mapper->post_properties['type'] ),
			$taxonomy['slug']
		);

		register_taxonomy( $taxonomy['slug'], $post_types, $args );
	}

	/**
	 * Build taxonomy labels.
	 *
	 * @since 5.3.0
	 * @param array $taxonomy Taxonomy arguments.
	 * @return array Labels.
	 */
	private function build_taxonomy_labels( $taxonomy ) {
		$singular = $taxonomy['singular_name'];
		$plural   = $taxonomy['name'];

		return array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'menu_name'                  => $taxonomy['menu_name'],
			'all_items'                  => 'All ' . $plural,
			'parent_item'                => 'Parent ' . $singular,
			'parent_item_colon'          => 'Parent ' . $singular . ':',
			'new_item_name'              => 'New ' . $singular . ' Name',
			'add_new_item'               => 'Add New ' . $singular,
			'edit_item'                  => 'Edit ' . $singular,
			'update_item'                => 'Update ' . $singular,
			'view_item'                  => 'View ' . $singular,
			'separate_items_with_commas' => 'Separate ' . $plural . ' with commas',
			'add_or_remove_items'        => 'Add or remove ' . $plural,
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular ' . $plural,
			'search_items'               => 'Search ' . $plural,
			'not_found'                  => 'Not Found',
			'no_terms'                   => 'No ' . $plural,
			'items_list'                 => $plural . ' list',
			'items_list_navigation'      => $plural . ' list navigation',
		);
	}

	/**
	 * Get all meta field menus for system posts.
	 *
	 * @since 5.0.0
	 * @return string HTML.
	 */
	public static function get_all_metafield_menus() {
		$factory = self::get_factory();
		$html    = '<div class="system-posts-metafields display-none">' . PHP_EOL;

		foreach ( $factory->get_system_posts() as $post_type => $label ) {
			$html .= '<div id="c2p-' . esc_attr( $post_type ) . '" class="system-post-metafield">' . PHP_EOL;
			$html .= $factory->get_metafield_menu( $post_type, '' );
			$html .= '</div>' . PHP_EOL;
		}

		$html .= '</div>' . PHP_EOL;
		return $html;
	}

	/**
	 * Get meta field menu for a post type.
	 *
	 * @since 5.0.0
	 * @param string $post_type      Post type.
	 * @param string $selected_field Selected field.
	 * @return string HTML.
	 */
	public function get_metafield_menu( $post_type, $selected_field ) {
		$cache_key = "c2p_metafield_menu_{$post_type}";
		$metas     = wp_cache_get( $cache_key );

		if ( false === $metas ) {
			global $wpdb;
			$metas = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT meta_key
					FROM {$wpdb->postmeta} as pm
					INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID
					WHERE p.post_type = %s
					AND pm.meta_key != ''",
					$post_type
				)
			);
			wp_cache_set( $cache_key, $metas );
		}

		$has_fields     = false;
		$found_existing = false;
		$disabled       = '';
		$html           = '';
		$display        = ' display-none';
		$display_select = ' select-hybrid';
		$input          = 'custom_meta_key';

		if ( empty( $selected_field ) ) {
			$disabled = ' disabled="true"';
		}

		if ( false !== $metas ) {
			$select = '<option value="">' . esc_html__( 'Select a field', 'post-my-contact-form-7' ) . '</option>' . PHP_EOL;
			
			foreach ( $metas as $row ) {
				if ( empty( trim( $row->meta_key ) ) ) {
					continue;
				}

				if ( $this->should_skip_meta_key( $row->meta_key, $post_type ) ) {
					continue;
				}

				$selected = selected( $selected_field, $row->meta_key, false );
				if ( $selected_field === $row->meta_key ) {
					$found_existing = true;
				}

				$select .= '<option value="' . esc_attr( $row->meta_key ) . '"' . $selected . '>' . 
					esc_html( $row->meta_key ) . '</option>' . PHP_EOL;
				$has_fields = true;
			}

			if ( $has_fields ) {
				$display = ' display-none';
				$input   = 'custom_meta_key';
				
				if ( ! empty( $selected_field ) && ! $found_existing ) {
					$input = $selected_field;
					$disabled = ' disabled="true"';
					$display_select = ' display-none';
					$display = '';
				}

				$select .= '<option value="cf72post-custom-meta-field">' . 
					esc_html__( 'Custom field', 'post-my-contact-form-7' ) . '</option>' . PHP_EOL;
				$select = '<select' . $disabled . ' class="existing-fields' . $display_select . '">' . 
					PHP_EOL . $select . '</select>' . PHP_EOL;
				$html .= $select;
			}

			$html .= '<input class="cf7-2-post-map-label-custom' . $display . '" type="text" value="' . 
				esc_attr( $input ) . '" ' . ( empty( $display ) ? '' : 'disabled ' ) . '/>' . PHP_EOL;
		}

		return $html;
	}

	/**
	 * Check if a meta key should be skipped.
	 *
	 * @since 5.3.0
	 * @param string $meta_key  Meta key.
	 * @param string $post_type Post type.
	 * @return bool True if should skip.
	 */
	private function should_skip_meta_key( $meta_key, $post_type ) {
		if ( 0 !== strpos( $meta_key, '_' ) ) {
			return false;
		}

		/**
		 * Filter plugin specific (internal) meta fields starting with '_'.
		 *
		 * @since 2.0.0
		 * @param bool   $skip      True by default.
		 * @param string $post_type Post type.
		 * @param string $meta_key  Meta field name.
		 */
		return apply_filters( 'cf7_2_post_skip_system_metakey', true, $post_type, $meta_key );
	}
}

/**
 * Get object factory.
 *
 * @since 1.0.0
 * @return CF72Post_Mapping_Factory
 */
function c2p_get_factory() {
	return CF72Post_Mapping_Factory::get_factory();
}

/**
 * Get mapped post types.
 *
 * @since 3.4.0
 * @return array
 */
function c2p_mapped_post_types() {
	return CF72Post_Mapping_Factory::get_mapped_post_types();
}