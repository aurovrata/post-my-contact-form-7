<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/public
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_2_Post_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_2_Post_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-2-post-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_2_Post_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_2_Post_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cf7-2-post-public.js', array( 'jquery' ), $this->version, false );

	}
  /**
  * Maps a cf7 form to its corresponding form
  * Hooks 'wpcf7_posted_data' cf7 action in the public section
  * @since 1.0.0
  * @param Array $cf7_form_data  data posted from teh cf7 form
  */
  public function save_cf7_2_post($cf7_form_data){
    debug_msg($cf7_form_data, 'cf7 form posted');
    //load the form factory
    if(isset($cf7_form_data['_wpcf7'])){
      $cf7_post_id = $cf7_form_data['_wpcf7'];
      //is this form mapped yet?
      $map_status = get_post_meta( $cf7_post_id , '_cf7_2_post-map' , true );
      if('publish' != $map_status) return; //nothing to do here

      $factory = Cf7_2_Post_Factory::get_factory($cf7_post_id);
      $factory->save_form_2_post($cf7_form_data);
    }else{
      debug_msg("ERROR, unable to get CF7 post ID for mapping in posted data!");
    }
  }

}
