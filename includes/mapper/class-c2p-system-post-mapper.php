<?php
/**
 * System Post Mapper Class for CF7 to Post Plugin.
 *
 * Handles mapping of Contact Form 7 forms to existing WordPress system post types.
 * This class extends the base C2P_Post_Mapper class to provide system post
 * specific functionality.
 *
 * @link       http://syllogic.in
 * @since      5.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 */

// Include dependencies.
require_once plugin_dir_path( __FILE__ ) . 'class-c2p-post-mapper.php';

/**
 * Class to handle mapping form submissions to system posts.
 *
 * This class manages the mapping of CF7 forms to existing WordPress post types
 * such as posts, pages, or custom post types registered by other plugins or themes.
 *
 * @since 5.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 * @author     Aurovrata V. <vrata@digital-tiff.in>
 */
class C2P_System_Post_Mapper extends C2P_Post_Mapper {

	/**
	 * The system post type being mapped.
	 *
	 * @since 5.3.0
	 * @var string
	 */
	protected $system_post_type;

	/**
	 * Constructor method.
	 *
	 * Initializes the system post mapper for a specific CF7 form.
	 *
	 * @since 5.0.0
	 * @param int                      $cf7_id  The Contact Form 7 form ID.
	 * @param CF72Post_Mapping_Factory $factory The mapping factory instance.
	 */
	public function __construct( $cf7_id, $factory ) {
		$this->cf7_post_id                    = absint( $cf7_id );
		self::$factory                        = $factory;
		$this->post_properties['type_source'] = 'system';
		$this->post_properties['default']     = 0; // Only custom posts can be default.

		// Call parent constructor.
		parent::__construct( $cf7_id, $factory );

		// Set system post type.
		// $this->set_system_post_type();
	}

	/**
	 * Set up post properties from saved mapping data.
	 *
	 * Loads and initializes the post properties for the system post type mapping.
	 *
	 * @since 5.0.0
	 * @return bool True if properties were set successfully, false otherwise.
	 */
	protected function set_post_properties() {
		$properties = $this->get_mapped_fields( 'mapped_post_' );

		if ( empty( $properties ) || ! is_array( $properties ) ) {
			$this->log_error( 'No mapped fields found for system post mapping.' );
			return false;
		}

		$valid_properties = $this->validate_properties( $properties );

		foreach ( $valid_properties as $prop => $value ) {
			$this->process_property( $prop, $value );
		}

		// Ensure required properties are set.
		$this->ensure_required_properties();

		// Set system post type.
		$this->set_system_post_type();

		return true;
	}

	/**
	 * Process an individual property.
	 *
	 * @since 5.3.0
	 * @param string $prop  The property name.
	 * @param mixed  $value The property value.
	 * @return void
	 */
	protected function process_property( $prop, $value ) {
		switch ( $prop ) {
			case 'type':
			case 'map':
				$this->post_properties[ $prop ] = sanitize_text_field( $value );
				break;

			case 'default':
				$this->post_properties[ $prop ] = 0;
				break;

			default:
				// Handle boolean properties.
				if ( in_array( $prop, $this->get_boolean_properties(), true ) ) {
					$this->post_properties[ $prop ] = ! empty( $value );
				} else {
					$this->post_properties[ $prop ] = $value;
				}
				break;
		}
	}

	/**
	 * Get the list of boolean properties.
	 *
	 * @since 5.3.0
	 * @return array Array of boolean property names.
	 */
	private function get_boolean_properties() {
		return array(
			'public',
			'show_ui',
			'show_in_menu',
			'show_in_admin_bar',
			'show_in_nav_menus',
			'can_export',
			'has_archive',
			'exclude_from_search',
			'publicly_queryable',
			'hierarchical',
			'flush_permalink_rules',
		);
	}

	/**
	 * Validate properties before setting.
	 *
	 * @since 5.3.0
	 * @param array $properties The properties to validate.
	 * @return array Validated properties.
	 */
	private function validate_properties( $properties ) {
		$validated = array();

		foreach ( $properties as $key => $value ) {
			// Skip empty values for non-boolean properties.
			if ( empty( $value ) && ! in_array( $key, $this->get_boolean_properties(), true ) ) {
				continue;
			}

			// Sanitize based on type.
			switch ( $key ) {
				case 'type':
				case 'map':
					$validated[ $key ] = sanitize_key( $value );
					break;

				case 'menu_position':
					$validated[ $key ] = absint( $value );
					break;

				default:
					// For boolean properties, check if they're set.
					if ( in_array( $key, $this->get_boolean_properties(), true ) ) {
						$validated[ $key ] = true;
					} else {
						$validated[ $key ] = sanitize_text_field( $value );
					}
					break;
			}
		}

		return $validated;
	}

	/**
	 * Ensure required properties are set.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private function ensure_required_properties() {
		// Ensure type is set.
		if ( empty( $this->post_properties['type'] ) ) {
			$this->post_properties['type'] = 'post';
			$this->log_error( 'Post type not set, defaulting to "post".' );
		}

		// Ensure map is set.
		if ( empty( $this->post_properties['map'] ) ) {
			$this->post_properties['map'] = 'publish';
			$this->log_error( 'Map status not set, defaulting to "publish".' );
		}

		// Ensure type_source is maintained.
		$this->post_properties['type_source'] = 'system';
	}

	/**
	 * Set the system post type from properties.
	 *
	 * @since 5.3.0
	 * @return bool True if post type was set, false otherwise.
	 */
	private function set_system_post_type() {
		if ( ! empty( $this->post_properties['type'] ) ) {
			$this->system_post_type = $this->post_properties['type'];
			return true;
		}

		$this->log_error( 'System post type could not be determined.');
		return false;
	}

	/**
	 * Get the system post type.
	 *
	 * @since 5.3.0
	 * @return string The system post type.
	 */
	public function get_system_post_type() {
		return $this->system_post_type;
	}

	/**
	 * Check if the specified post type exists.
	 *
	 * @since 5.3.0
	 * @param string $post_type The post type to check.
	 * @return bool True if post type exists, false otherwise.
	 */
	public function post_type_exists( $post_type ) {
		return post_type_exists( $post_type );
	}

	/**
	 * Check if the mapping is for a system post.
	 *
	 * @since 5.3.0
	 * @return bool True if system post, false otherwise.
	 */
	public function is_system_post() {
		return 'system' === $this->post_properties['type_source'];
	}

	/**
	 * Get the post properties.
	 *
	 * @since 5.3.0
	 * @return array The post properties.
	 */
	public function get_post_properties() {
		return $this->post_properties;
	}

	/**
	 * Validate the post type before saving.
	 *
	 * @since 5.3.0
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_post_type() {
		if ( empty( $this->system_post_type ) ) {
			$this->log_error( 'No system post type defined.' );
			return false;
		}

		if ( ! $this->post_type_exists( $this->system_post_type ) ) {
			/* translators: %s: Post type name */
			$this->log_error( sprintf( 'System post type "%s" does not exist.', $this->system_post_type ) );
			return false;
		}

		return true;
	}
}