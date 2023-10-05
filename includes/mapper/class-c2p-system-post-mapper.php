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
class C2P_System_Post_Mapper extends C2P_Post_Mapper {
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
		$this->post_properties['type_source'] = 'system';
		$this->post_properties['default']     = 0; // only custom post can be default for now.
	}
	/**
	 * Setup properties.
	 *
	 * @since    1.0.0
	 * */
	protected function set_post_properties() {

		$properties = $this->get_mapped_fields( 'mapped_post_' );

		// properties of factory post.
		foreach ( $properties as $prop => $value ) {
			switch ( $prop ) {
				case 'type':
				case 'map':
					$this->post_properties[ $prop ] = $value;
					break;
				case 'default':
					$this->post_properties[ $prop ] = 0;
					break;
				default: // properties with boolean, unchked are blank and skipped.
					break;
			}
		}

	}

}
