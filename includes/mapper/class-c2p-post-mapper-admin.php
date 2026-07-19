<?php

/**
 * Abstract Post Mapper Class for CF7 to Post Plugin to handle all admin requests such as saving form to post mapping meta.
 *
 * Defines the interface for mapping Contact Form 7 forms to WordPress posts.
 * This abstract class provides common functionality for both system and custom
 * post type mappers.
 *
 * @link       http://digital-tiff.in
 * @since      5.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 */

/**
 * Abstract class to define a general mapping interface for the admin form to post.
 *
 * This class handles the mapping of CF7 form fields to post fields,
 * meta fields, and taxonomies. It provides methods for saving, loading,
 * and managing post mappings.
 *
 * @since 5.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
abstract class C2P_Post_Mapper_Admin {
    /**
	 * Reference to the mapper factory.
	 *
	 * @since 1.0.0
	 * @var CF72Post_Mapping_Factory
	 */
	protected static $factory;

	/**
	 * The properties of the mapped custom post type.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $post_properties = array();

	/**
	 * The properties of the mapped custom taxonomies.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $taxonomy_properties = array();

	/**
	 * The CF7 post ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $cf7_post_id = 0;

	/**
	 * The CF7 post unique key.
	 *
	 * @since 1.2.7
	 * @var string
	 */
	public $cf7_key;

	/**
	 * The CF7 form fields (field name => field type).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $cf7_form_fields = array();

	/**
	 * The CF7 form fields from old database format.
	 *
	 * @since 5.0.0
	 * @var array
	 */
	protected $old_db_fields = array();

	/**
	 * The CF7 form fields options.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $cf7_form_fields_options = array();

	/**
	 * The CF7 form fields CSS classes.
	 *
	 * @since 5.0.0
	 * @var array
	 */
	protected $cf7_form_fields_classes = array();

	/**
	 * CF7 form fields mapped to post fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $post_map_fields = array();

	/**
	 * CF7 form fields mapped to post meta fields.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $post_map_meta_fields = array();

	/**
	 * CF7 form fields mapped to taxonomies.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $post_map_taxonomy = array();

	/**
	 * CF7 form field values for localization.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $localise_values;

	/**
	 * WPCF7 type taxonomy terms for this form.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	public $form_terms = array();

	/**
	 * Array of mapped post types.
	 *
	 * @since 3.4.0
	 * @var array
	 */
	protected static $mapped_post_types = array();

	/**
	 * Flag to flush rewrite rules when posts are created/updated.
	 *
	 * @since 3.8.2
	 * @var bool
	 */
	public $flush_permalink_rules = false;

	/**
	 * Array of existing system post types.
	 *
	 * @since 5.0.0
	 * @var array
	 */
	protected static $system_post_types = array();

    /**
	 * Constructor.
	 *
	 * @since 5.0.0
	 * @param int                      $cf7_id  The Contact Form 7 form ID.
	 * @param CF72Post_Mapping_Factory $factory The mapping factory instance.
	 */
	public function __construct( $cf7_id, $factory ) {
		$this->cf7_post_id = absint( $cf7_id );
		self::$factory     = $factory;
	}

    /* ==========================================================================
	 * GETTER METHODS
	 * ========================================================================== */

	/**
	 * Get the post map fields.
	 *
	 * @since 5.0.0
	 * @return array The post map fields.
	 */
	public function get_post_map_fields() {
		return $this->post_map_fields;
	}

	/**
	 * Get the post map meta fields.
	 *
	 * @since 5.0.0
	 * @return array The post map meta fields.
	 */
	public function get_post_map_meta_fields() {
		return $this->post_map_meta_fields;
	}

	/**
	 * Get the post map taxonomy.
	 *
	 * @since 5.0.0
	 * @return array The post map taxonomy.
	 */
	public function get_post_map_taxonomy() {
		return $this->post_map_taxonomy;
	}

	/**
	 * Get the CF7 form fields.
	 *
	 * @since 5.0.0
	 * @return array The CF7 form fields.
	 */
	public function get_cf7_form_fields() {
		return $this->cf7_form_fields;
	}

	/**
	 * Get the form post ID.
	 *
	 * @since 5.0.0
	 * @return int The form post ID.
	 */
	public function form_id() {
		return $this->cf7_post_id;
	}

	/**
	 * Get the mapping of CF7 form fields to taxonomy.
	 *
	 * @since 2.0.0
	 * @return array Array of form field => taxonomy name mappings.
	 */
	public function get_mapped_taxonomy() {
		return $this->post_map_taxonomy;
	}

	/**
	 * Get the CF7 form fields.
	 *
	 * @since 2.5.0
	 * @return array Array of field => type pairs.
	 */
	public function get_form_fields() {
		if ( empty( $this->cf7_form_fields ) ) {
			$this->load_form_fields();
		}
		return $this->cf7_form_fields;
	}

	/**
	 * Get the mapping of CF7 form fields to post meta fields.
	 *
	 * @since 1.0.0
	 * @return array Array of form field => post field mappings.
	 */
	public function get_mapped_meta_fields() {
		return $this->post_map_meta_fields;
	}

	/**
	 * Get mapped custom post property.
	 *
	 * @since 1.0.0
	 * @param string $property Post property attribute.
	 * @return string Value of the property, or empty string if not set.
	 */
	public function get( $property = 'type' ) {
		return isset( $this->post_properties[ $property ] ) 
			? $this->post_properties[ $property ] 
			: '';
	}

	/**
	 * Get taxonomy properties.
	 *
	 * @since 1.0.0
	 * @param string $taxonomy The taxonomy name.
	 * @return array Taxonomy properties.
	 */
	public function get_taxonomy( $taxonomy ) {
		return isset( $this->taxonomy_properties[ $taxonomy ] ) 
			? $this->taxonomy_properties[ $taxonomy ] 
			: array();
	}

	/* ==========================================================================
	 * FIELD MAPPING METHODS
	 * ========================================================================== */

	/**
	 * Get form field mapped to post field.
	 *
	 * @since 5.0.0
	 * @param string $post_field The post field name.
	 * @param bool   $is_meta    Whether this is a meta field.
	 * @return string|false The form field name.
	 */
	public function get_mapped_form_field( $post_field, $is_meta = false ) {
		return $this->get_c2p_field( $post_field, ( $is_meta ? 'meta-field' : 'field' ) );
	}

	/**
	 * Get taxonomy mapped form fields.
	 *
	 * @since 5.0.0
	 * @param string $taxonomy The taxonomy slug.
	 * @return mixed Single or array of form fields.
	 */
	public function get_taxonomy_mapped_form_field( $taxonomy = null ) {
		return $this->get_c2p_field( $taxonomy, 'taxonomy' );
	}

	/**
	 * Get form field mapped to post field by type.
	 *
	 * @since 5.0.0
	 * @param string $post_field The post field name.
	 * @param string $data_type  The mapping type.
	 * @return mixed The form field name(s).
	 */
	private function get_c2p_field( $post_field, $data_type ) {
		$search = $this->get_search_array( $data_type );
		
		if ( empty( $search ) ) {
			$prefix = $this->get_search_prefix( $data_type );
			return get_post_meta( $this->cf7_post_id, $prefix . $post_field, true );
		}
		
		return array_search( $post_field, $search, true );
	}

	/**
	 * Get the search array for a data type.
	 *
	 * @since 5.3.0
	 * @param string $data_type The data type.
	 * @return array The search array.
	 */
	private function get_search_array( $data_type ) {
		switch ( $data_type ) {
			case 'meta-field':
				return $this->post_map_meta_fields;
			case 'taxonomy':
				return $this->post_map_taxonomy;
			case 'field':
			default:
				return $this->post_map_fields;
		}
	}

	/**
	 * Get the search prefix for a data type.
	 *
	 * @since 5.3.0
	 * @param string $data_type The data type.
	 * @return string The prefix.
	 */
	private function get_search_prefix( $data_type ) {
		switch ( $data_type ) {
			case 'meta-field':
				return 'cf7_2_post_map_meta-';
			case 'taxonomy':
				return 'cf7_2_post_map_taxonomy-';
			case 'field':
			default:
				return 'cf7_2_post_map-';
		}
	}

	/* ==========================================================================
	 * LOAD AND SAVE METHODS
	 * ========================================================================== */

	/**
	 * Load mapping properties from database.
	 *
	 * @since 5.0.0
	 * @param string $cf7_key Form unique key.
	 * @return void
	 */
	public function load_post_mapping( $cf7_key ) {
		$this->cf7_key              = $cf7_key;
		$this->post_map_fields      = array();
		$this->post_map_meta_fields = array();
		$this->post_map_taxonomy    = array();
		$this->post_properties      = array();

		$fields = get_post_meta( $this->cf7_post_id );
		$this->parse_load_fields( $fields );
		$this->load_taxonomies();
		
		$this->post_properties['cf7_title'] = get_the_title( $this->cf7_post_id );
		
		if ( ! isset( $this->post_properties['version'] ) ) {
			$this->post_properties['version'] = '1.2.0';
		}
	}

	/**
	 * Parse loaded fields.
	 *
	 * @since 5.3.0
	 * @param array $fields Database fields.
	 * @return void
	 */
	private function parse_load_fields( $fields ) {
		$start1 = strlen( '_cf7_2_post-' );
		$start2 = strlen( 'cf7_2_post_map-' );
		$start3 = strlen( 'cf7_2_post_map_meta-' );
		
		$array_keys = array(
			'_cf7_2_post-taxonomy',
			'_cf7_2_post-supports',
			'_cf7_2_post-capabilities',
		);

		foreach ( $fields as $key => $value ) {
			if ( '_cf7_2_post_flush_rewrite_rules' === $key ) {
				$this->flush_permalink_rules = (bool) $value[0];
			} elseif ( in_array( $key, $array_keys, true ) ) {
				$prop = substr( $key, $start1 );
				$this->post_properties[ $prop ] = maybe_unserialize( $value[0] );
			} elseif ( 0 === strpos( $key, '_cf7_2_post-' ) ) {
				$prop = substr( $key, $start1 );
				$this->post_properties[ $prop ] = $value[0];
			} elseif ( 0 === strpos( $key, 'cf7_2_post_map-' ) ) {
				$this->post_map_fields[ $value[0] ] = substr( $key, $start2 );
			} elseif ( 0 === strpos( $key, 'cf7_2_post_map_meta-' ) ) {
				$this->post_map_meta_fields[ $value[0] ] = substr( $key, $start3 );
			}
		}
	}

	/**
	 * Load taxonomies from database.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private function load_taxonomies() {
		if ( empty( $this->post_properties['taxonomy'] ) ) {
			return;
		}

		$system_tax = $this->get_system_taxonomies();

		foreach ( $this->post_properties['taxonomy'] as $slug ) {
			$taxonomy_array = $this->build_taxonomy_array( $slug, $system_tax );
			$this->taxonomy_properties[ $slug ] = $taxonomy_array;
			
			$cf7_field = get_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy-' . $slug, true );
			if ( ! is_array( $cf7_field ) ) {
				$cf7_field = array( $cf7_field );
			}
			
			foreach ( $cf7_field as $field ) {
				$this->post_map_taxonomy[ $field ] = $taxonomy_array['slug'];
			}
		}
	}

	/**
	 * Get system taxonomies.
	 *
	 * @since 5.3.0
	 * @return array System taxonomies.
	 */
	private function get_system_taxonomies() {
		return get_taxonomies(
			array(
				'public'   => true,
				'_builtin' => true,
			),
			'objects'
		);
	}

	/**
	 * Build taxonomy array.
	 *
	 * @since 5.3.0
	 * @param string $slug       The taxonomy slug.
	 * @param array  $system_tax System taxonomies.
	 * @return array Taxonomy properties.
	 */
	private function build_taxonomy_array( $slug, $system_tax ) {
		$source = get_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_source-' . $slug, true );
		
		if ( ! $source ) {
			return $this->build_legacy_taxonomy_array( $slug );
		}

		$taxonomy_array = array(
			'slug'          => $slug,
			'name'          => '',
			'singular_name' => '',
			'source'        => $source,
		);

		if ( isset( $system_tax[ $slug ] ) ) {
			$taxonomy_array['name']          = $system_tax[ $slug ]->labels->name;
			$taxonomy_array['singular_name'] = $system_tax[ $slug ]->labels->singular_name;
		}

		return $taxonomy_array;
	}

	/**
	 * Build legacy taxonomy array (pre-1.1 version).
	 *
	 * @since 5.3.0
	 * @param string $slug The taxonomy slug.
	 * @return array Taxonomy properties.
	 */
	private function build_legacy_taxonomy_array( $slug ) {
		return array(
			'slug'          => $slug,
			'name'          => get_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_names-' . $slug, true ),
			'singular_name' => get_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_name-' . $slug, true ),
			'source'        => 'factory',
		);
	}

	/**
	 * Save mapping to the database.
	 *
	 * @since 5.0.0
	 * @return bool True on success.
	 */
	public function save_mapping() {
		// Validate permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->log_error( 'Insufficient permissions to save mapping.' );
			return false;
		}

		$this->post_properties['taxonomy'] = array();
		$this->post_properties['version']  = CF7_2_POST_VERSION;

		$this->set_post_properties();
		$this->load_old_db_fields();
		$this->save_post_properties();
		$this->save_post_fields();
		$this->save_post_meta_fields();
		$this->save_taxonomy_fields();
		$this->clean_old_db_fields();
		
		update_post_meta( $this->cf7_post_id, '_cf7_2_post_flush_rewrite_rules', $this->flush_permalink_rules );
		self::$factory->register( $this );

		return true;
	}

	/**
	 * Load old database fields.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private function load_old_db_fields() {
		$this->old_db_fields = get_post_meta( $this->cf7_post_id );
		foreach ( $this->old_db_fields as $name => $value ) {
			if ( false === strpos( $name, 'cf7_2_post' ) ) {
				unset( $this->old_db_fields[ $name ] );
			}
		}
	}

	/**
	 * Save post properties to database.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private function save_post_properties() {
		foreach ( $this->post_properties as $prop => $value ) {
			$meta_key = '_cf7_2_post-' . $prop;
			update_post_meta( $this->cf7_post_id, $meta_key, $value );
			
			if ( isset( $this->old_db_fields[ $meta_key ] ) ) {
				unset( $this->old_db_fields[ $meta_key ] );
			}
		}
	}

	/**
	 * Save post fields mapping.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function save_post_fields() {
		$fields = $this->get_mapped_fields( 'cf7_2_post_map-' );
		$this->post_map_fields = $fields;
		$this->save_to_db( $fields, 'cf7_2_post_map-' );
	}

	/**
	 * Save post meta fields mapping.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function save_post_meta_fields() {
		$fields = $this->get_mapped_fields( 'cf7_2_post_map_meta_value-' );
		$this->post_map_meta_fields = $fields;
		$this->save_to_db( $fields, 'cf7_2_post_map_meta-' );
	}

	/**
	 * Save mapping data to the database.
	 *
	 * @since 1.0.0
	 * @param array  $fields_and_value Array of field name => post field pairs.
	 * @param string $prefix           Prefix indicating field type.
	 * @return void
	 */
	protected function save_to_db( $fields_and_value, $prefix ) {
		foreach ( $fields_and_value as $post_field => $form_field ) {
			$meta_key = $prefix . $form_field;
			update_post_meta( $this->cf7_post_id, $meta_key, $post_field );
			
			if ( isset( $this->old_db_fields[ $meta_key ] ) ) {
				unset( $this->old_db_fields[ $meta_key ] );
			}
		}
	}

	/**
	 * Clean old database fields.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private function clean_old_db_fields() {
		$prefixes = array(
			'_cf7_2_post-',
			'cf7_2_post_map-',
			'cf7_2_post_map_meta-',
			'cf7_2_post_map_taxonomy_source-',
			'cf7_2_post_map_taxonomy_names-',
			'cf7_2_post_map_taxonomy_name-',
		);

		foreach ( $this->old_db_fields as $key => $value ) {
			foreach ( $prefixes as $prefix ) {
				if ( 0 === strpos( $key, $prefix ) ) {
					delete_post_meta( $this->cf7_post_id, $key );
					break;
				}
			}
		}
	}

	/* ==========================================================================
	 * TAXONOMY METHODS
	 * ========================================================================== */

	/**
	 * Save taxonomy fields mapping.
	 *
	 * @since 1.0.0
	 * @return bool True on success.
	 */
	protected function save_taxonomy_fields() {
		$fields = $this->get_mapped_fields( 'cf7_2_post_map_taxonomy_' );
		
		$len_taxonomy = strlen( 'value-' );
		$len_name     = strlen( 'name-' );
		$len_names    = strlen( 'names-' );
		$len_source   = strlen( 'source-' );

		$slugs = array();

		foreach ( $fields as $field => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			$this->process_taxonomy_field( $field, $value, $slugs, $len_taxonomy, $len_name, $len_names, $len_source );
		}

		$this->save_taxonomy_slugs( $slugs );
		$this->post_properties['taxonomy'] = array_unique( 
			array_merge( $this->post_properties['taxonomy'], array_keys( $slugs ) ) 
		);
		
		update_post_meta( $this->cf7_post_id, '_cf7_2_post-taxonomy', $this->post_properties['taxonomy'] );

		return true;
	}

	/**
	 * Process a single taxonomy field.
	 *
	 * @since 5.3.0
	 * @param string $field       The field name.
	 * @param string $value       The field value.
	 * @param array  $slugs       Reference to slugs array.
	 * @param int    $len_value   Length of 'value-' prefix.
	 * @param int    $len_name    Length of 'name-' prefix.
	 * @param int    $len_names   Length of 'names-' prefix.
	 * @param int    $len_source  Length of 'source-' prefix.
	 * @return void
	 */
	private function process_taxonomy_field( $field, $value, &$slugs, $len_value, $len_name, $len_names, $len_source ) {
		if ( 0 === strpos( $field, 'source-' ) ) {
			$slug = substr( $field, $len_source );
			$this->ensure_taxonomy_property( $slug );
			$this->taxonomy_properties[ $slug ]['source'] = $value;
			if ( ! isset( $slugs[ $slug ] ) ) {
				$slugs[ $slug ] = array();
			}
		} elseif ( 0 === strpos( $field, 'names-' ) ) {
			$slug = substr( $field, $len_names );
			$this->ensure_taxonomy_property( $slug );
			$this->taxonomy_properties[ $slug ]['name'] = $value;
		} elseif ( 0 === strpos( $field, 'name-' ) ) {
			$slug = substr( $field, $len_name );
			$this->ensure_taxonomy_property( $slug );
			$this->taxonomy_properties[ $slug ]['singular_name'] = $value;
		} elseif ( 0 === strpos( $field, 'value-' ) ) {
			$slug = $this->extract_taxonomy_slug( $field, $value, $len_value );
			if ( ! isset( $slugs[ $slug ] ) ) {
				$slugs[ $slug ] = array();
			}
			$slugs[ $slug ][] = $value;
			$this->post_map_taxonomy[ $value ] = $slug;
		}
	}

	/**
	 * Ensure taxonomy property exists.
	 *
	 * @since 5.3.0
	 * @param string $slug The taxonomy slug.
	 * @return void
	 */
	private function ensure_taxonomy_property( $slug ) {
		if ( ! isset( $this->taxonomy_properties[ $slug ] ) ) {
			$this->taxonomy_properties[ $slug ] = array();
		}
	}

	/**
	 * Extract taxonomy slug from field value.
	 *
	 * @since 5.3.0
	 * @param string $field     The field name.
	 * @param string $value     The field value.
	 * @param int    $len_value Length of 'value-' prefix.
	 * @return string The taxonomy slug.
	 */
	private function extract_taxonomy_slug( $field, $value, $len_value ) {
		$slug = substr( $field, $len_value );
		if ( 0 === strpos( $value, 'cf7_2_post_filter-' ) ) {
			$slug = str_replace( '/', '', $slug );
		} else {
			$slug = str_replace( "/$value", '', $slug );
		}
		return $slug;
	}

	/**
	 * Save taxonomy slugs to database.
	 *
	 * @since 5.3.0
	 * @param array $slugs Array of taxonomy slugs.
	 * @return void
	 */
	private function save_taxonomy_slugs( $slugs ) {
		foreach ( $slugs as $slug => $fields ) {
			$this->save_single_taxonomy_slug( $slug, $fields );
		}
	}

	/**
	 * Save a single taxonomy slug.
	 *
	 * @since 5.3.0
	 * @param string $slug   The taxonomy slug.
	 * @param array  $fields The form fields mapped to this taxonomy.
	 * @return void
	 */
	private function save_single_taxonomy_slug( $slug, $fields ) {
		$source_key = 'cf7_2_post_map_taxonomy_source-' . $slug;
		update_post_meta( $this->cf7_post_id, $source_key, $this->taxonomy_properties[ $slug ]['source'] );
		
		if ( isset( $this->old_db_fields[ $source_key ] ) ) {
			unset( $this->old_db_fields[ $source_key ] );
		}

		if ( 'factory' === $this->taxonomy_properties[ $slug ]['source'] ) {
			$names_key = 'cf7_2_post_map_taxonomy_names-' . $slug;
			$name_key  = 'cf7_2_post_map_taxonomy_name-' . $slug;
			
			update_post_meta( $this->cf7_post_id, $names_key, $this->taxonomy_properties[ $slug ]['name'] );
			update_post_meta( $this->cf7_post_id, $name_key, $this->taxonomy_properties[ $slug ]['singular_name'] );
			
			if ( isset( $this->old_db_fields[ $names_key ] ) ) {
				unset( $this->old_db_fields[ $names_key ] );
			}
			if ( isset( $this->old_db_fields[ $name_key ] ) ) {
				unset( $this->old_db_fields[ $name_key ] );
			}
		}

		update_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy-' . $slug, $fields );
	}

	/* ==========================================================================
	 * STRIPPED FIELD METHODS
	 * ========================================================================== */

	/**
	 * Strips mapped fields from the admin $_POST form saving.
	 *
	 * This method is accessed from admin requests only.
	 *
	 * @since 5.0.0
	 * @param string $field_prefix Field prefix used to identify mapping type.
	 * @return array Array of fields mapped to post values.
	 */
	protected function get_mapped_fields( $field_prefix ) {
		if ( ! isset( $_POST['cf7_2_post_nonce'] ) || 
			 ! wp_verify_nonce( sanitize_key( $_POST['cf7_2_post_nonce'] ), 'cf7_2_post_mapping' ) ) {
			$this->log_error( 'Invalid nonce while saving mapping.' );
			return array( 'error' => 'Invalid nonce' );
		}

		$prefix_length = strlen( $field_prefix );
		$fields        = array();

		foreach ( $_POST as $field => $value ) {
			if ( empty( $value ) || 0 !== strpos( $field, $field_prefix ) ) {
				continue;
			}

			$post_field = substr( $field, $prefix_length );
			$sanitized_value = $this->sanitize_field_value( $value, $field_prefix );

			switch ( $field_prefix ) {
				case 'mapped_post_':
				case 'cf7_2_post_map_taxonomy_':
					$fields[ $post_field ] = $sanitized_value;
					break;
				default:
					$fields[ $sanitized_value ] = $post_field;
					break;
			}
		}

		return $fields;
	}

	/**
	 * Sanitize field value based on prefix.
	 *
	 * @since 5.3.0
	 * @param string $value        The field value.
	 * @param string $field_prefix The field prefix.
	 * @return string Sanitized value.
	 */
	private function sanitize_field_value( $value, $field_prefix ) {
		if ( 'mapped_post_' === $field_prefix ) {
			return sanitize_key( $value );
		}
		return sanitize_text_field( $value );
	}

	/* ==========================================================================
	 * FORM FIELD LOADING METHODS
	 * ========================================================================== */

	/**
	 * Load the CF7 form fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_form_fields() {
		if ( ! empty( $this->cf7_form_fields ) ) {
			return;
		}

		$form = WPCF7_ContactForm::get_instance( $this->cf7_post_id );
		if ( ! $form ) {
			return;
		}

		$form_elements = $form->scan_form_tags();
		foreach ( $form_elements as $element ) {
			if ( empty( $element['name'] ) ) {
				continue;
			}
			
			$field_name = $element['name'];
			$field_type = str_replace( '*', '', $element['type'] );
			
			$this->cf7_form_fields[ $field_name ] = $field_type;
			$this->cf7_form_fields_options[ $field_name ] = $element['options'];
			$this->cf7_form_fields_classes[ $field_name ] = (array) $element->get_option( 'class', 'class' );
		}
	}

	/**
	 * Check if a CF7 field has an option.
	 *
	 * @since 2.0.0
	 * @param string $field_name The field name.
	 * @param string $option     The option to check.
	 * @return bool True if the option is set.
	 */
	public function field_has_option( $field_name, $option ) {
		return isset( $this->cf7_form_fields_options[ $field_name ] ) && 
			   in_array( $option, $this->cf7_form_fields_options[ $field_name ], true );
	}

	/**
	 * Check if a CF7 field has a CSS class.
	 *
	 * @since 5.0.0
	 * @param string $field_name The field name.
	 * @param string $class      The class to check.
	 * @return bool True if the class is set.
	 */
	public function field_has_class( $field_name, $class ) {
		return isset( $this->cf7_form_fields_classes[ $field_name ] ) && 
			   in_array( $class, $this->cf7_form_fields_classes[ $field_name ], true );
	}

	/* ==========================================================================
	 * SUPPORT AND VALIDATION METHODS
	 * ========================================================================== */

	/**
	 * Check if a post attribute is supported.
	 *
	 * @since 1.0.0
	 * @param string $post_attribute The post attribute to check.
	 * @return bool True if supported.
	 */
	public function supports( $post_attribute ) {
		if ( 'draft' === $this->post_properties['map'] ) {
			return true;
		}
		return in_array( $post_attribute, $this->post_properties['supports'], true );
	}

	/**
	 * Set an existing taxonomy for this post.
	 *
	 * @since 1.0.0
	 * @param string $taxonomy The taxonomy name.
	 * @return void
	 */
	public function set_taxonomy( $taxonomy ) {
		if ( ! in_array( $taxonomy, $this->post_properties['taxonomy'], true ) && 
			 taxonomy_exists( $taxonomy ) ) {
			$this->post_properties['taxonomy'][] = $taxonomy;
		}
	}

	/**
	 * Set the post type capability.
	 *
	 * @since 1.0.0
	 * @param string $capability Post type capability.
	 * @param bool   $flag       True or false.
	 * @return void
	 */
	public function set_post_capability( $capability, $flag = false ) {
		$this->post_properties[ $capability ] = $flag;
	}

	/**
	 * Set the post support attributes.
	 *
	 * @since 1.0.0
	 * @param array $supports Post type supports attributes.
	 * @return void
	 */
	public function set_supports( $supports ) {
		$this->post_properties['supports'] = $supports;
	}

	/**
	 * Get mapped custom post property with conditional output.
	 *
	 * @since 1.0.0
	 * @param string $property                     Post property attribute.
	 * @param string $echo_string_or_value_if_null Optional string to echo if property is set.
	 * @return string If the property is set/true, echo of the 2nd parameter if passed.
	 *                Else the property value if the 2nd parameter is omitted.
	 */
	public function is( $property, $echo_string_or_value_if_null = null ) {
		if ( ! isset( $this->post_properties[ $property ] ) ) {
			return '';
		}
		$echo = isset( $echo_string_or_value_if_null ) 
			? $echo_string_or_value_if_null 
			: $this->post_properties[ $property ];
		return $this->post_properties[ $property ] ? $echo : '';
	}

	/* ==========================================================================
	 * DISPLAY METHODS
	 * ========================================================================== */

	/**
	 * Print the input text field for custom meta fields.
	 *
	 * @since 5.0.0
	 * @param string $post_field Field name.
	 * @return string HTML string.
	 */
	public function get_metafield_input( $post_field ) {
		$disabled = '';
		if ( empty( $post_field ) ) {
			$disabled   = ' disabled="true"';
			$post_field = 'meta_key_1';
		}
		return sprintf(
			'<input%s name="cf7_2_post_map_meta-%s" class="cf7-2-post-map-labels" type="text" value="%s"/>',
			$disabled,
			esc_attr( $post_field ),
			esc_attr( $post_field )
		);
	}

	/**
	 * Display a dropdown list of taxonomies.
	 *
	 * Called by the dashboard page.
	 *
	 * @since 1.1.0
	 * @param string $taxonomy_slug Slug of taxonomy to show as selected.
	 * @return string HTML select element.
	 */
	public function get_taxonomy_listing( $taxonomy_slug = null ) {
		$result  = '';
		$result .= sprintf(
			'<select class="taxonomy-list%s">',
			empty( $taxonomy_slug ) ? '' : ' select-hybrid'
		);

		if ( empty( $taxonomy_slug ) ) {
			$result .= '<option value="" data-name="" >' . 
				esc_html__( 'Choose a Taxonomy', 'post-my-contact-form-7' ) . 
				'</option>';
		}

		// Add factory taxonomy option.
		$default_slug = sanitize_title( $this->get( 'singular_name' ) ) . '_categories';
		$result .= sprintf(
			'<option class="factory-taxonomy" value="%s" data-name="%s">%s</option>',
			esc_attr( $default_slug ),
			esc_attr__( 'New Category', 'post-my-contact-form-7' ),
			esc_html__( 'New Categories', 'post-my-contact-form-7' )
		);

		// Add selected taxonomy.
		if ( ! empty( $taxonomy_slug ) && isset( $this->taxonomy_properties[ $taxonomy_slug ] ) ) {
			$taxonomy = $this->taxonomy_properties[ $taxonomy_slug ];
			$result .= sprintf(
				'<option selected data-name="%s" value="%s" class="%s-taxonomy">%s</option>',
				esc_attr( $taxonomy['singular_name'] ),
				esc_attr( $taxonomy_slug ),
				esc_attr( $taxonomy['source'] ),
				esc_html( $taxonomy['name'] )
			);
		}

		// Add system taxonomies.
		$result .= $this->get_system_taxonomy_options( $taxonomy_slug );

		$result .= '</select>';
		return $result;
	}

	/**
	 * Get system taxonomy options.
	 *
	 * @since 5.3.0
	 * @param string $taxonomy_slug The selected taxonomy slug.
	 * @return string HTML option elements.
	 */
	private function get_system_taxonomy_options( $taxonomy_slug ) {
		$result = '';

		// Add default post tags and category.
		if ( 'post_tag' !== $taxonomy_slug ) {
			$result .= sprintf(
				'<option value="post_tag" data-name="%s" class="system-taxonomy">%s</option>',
				esc_attr__( 'Post Tag', 'post-my-contact-form-7' ),
				esc_html__( 'Post Tags', 'post-my-contact-form-7' )
			);
		}

		if ( 'category' !== $taxonomy_slug ) {
			$result .= sprintf(
				'<option value="category" data-name="%s" class="system-taxonomy">%s</option>',
				esc_attr__( 'Post Category', 'post-my-contact-form-7' ),
				esc_html__( 'Post Categories', 'post-my-contact-form-7' )
			);
		}

		$system_taxonomies = get_taxonomies(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		foreach ( $system_taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy_slug ) && $taxonomy_slug === $taxonomy->name ) {
				continue;
			}
			$result .= sprintf(
				'<option value="%s" data-name="%s" class="system-taxonomy">%s</option>',
				esc_attr( $taxonomy->name ),
				esc_attr( $taxonomy->labels->singular_name ),
				esc_html( $taxonomy->labels->name )
			);
		}

		return $result;
	}
    /* ==========================================================================
	 * DELETE METHODS
	 * ========================================================================== */

	/**
	 * Delete a post mapping.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function delete_mapping() {
		$post_type = $this->post_properties['type'];
		$source    = $this->post_properties['type_source'];

		// Delete post properties.
		foreach ( $this->post_properties as $key => $value ) {
			delete_post_meta( $this->cf7_post_id, '_cf7_2_post-' . $key );
		}
		delete_post_meta( $this->cf7_post_id, '_cf7_2_post_flush_rewrite_rules' );

		// Delete post field mappings.
		foreach ( $this->post_map_fields as $cf7_field => $post_field ) {
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map-' . $post_field );
		}

		// Delete meta field mappings.
		foreach ( $this->post_map_meta_fields as $cf7_field => $post_field ) {
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map_meta-' . $post_field );
		}

		// Delete taxonomy mappings.
		delete_post_meta( $this->cf7_post_id, '_cf7_2_post-taxonomy' );
		foreach ( $this->post_properties['taxonomy'] as $slug ) {
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_names-' . $slug );
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_name-' . $slug );
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy-' . $slug );
			delete_post_meta( $this->cf7_post_id, 'cf7_2_post_map_taxonomy_source-' . $slug );
		}

		// Delete submitted posts if filter allows.
		$this->delete_submitted_posts( $post_type );
	}

	/**
	 * Delete submitted posts for this mapping.
	 *
	 * @since 5.3.0
	 * @param string $post_type The post type.
	 * @return void
	 */
	private function delete_submitted_posts( $post_type ) {
		if ( ! apply_filters( 'c2p_delete_all_submitted_posts', false, $post_type, $this->cf7_key ) ) {
			return;
		}

		$query = apply_filters(
			'c2p_delete_all_submitted_posts_query',
			array( 'numberposts' => -1 ),
			$post_type,
			$this->cf7_key
		);

		$query['post_type'] = $post_type;
		$allposts = get_posts( $query );

		foreach ( $allposts as $eachpost ) {
			wp_delete_post( $eachpost->ID, true );
		}
	}
}
