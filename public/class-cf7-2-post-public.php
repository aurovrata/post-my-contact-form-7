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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-2-post-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
    $plugin_dir = plugin_dir_url( __FILE__ );
		wp_register_script( $this->plugin_name, $plugin_dir . 'js/cf7-2-post-public.js', array( 'jquery' ), $this->version, false );
	}
  /**
  * Maps a cf7 form to its corresponding post
  * Hooks 'wpcf7_before_send_mail' just after all validation is done
  * @since 1.0.0
  * @param  WPCF7_Contact_Form $cf7_form  cf7 form object
  */
  public function save_cf7_2_post($cf7_form){

    //load the form factory
    $cf7_post_id = $cf7_form->id();
    //is this form mapped yet?
    if(Cf7_2_Post_Factory::is_mapped($cf7_post_id)){
      $factory = Cf7_2_Post_Factory::get_factory($cf7_post_id);

      //load all the submittec values
      //$cf7_form_data = array();
      //$tags = $cf7_form->scan_form_tags(); //get your form tags
      //the curent submission object from cf7 plugin
      $submission = WPCF7_Submission::get_instance();
      //debug_msg($submission, "saving form data ");
      $factory->save_form_2_post($submission);
    }

    return $cf7_form;
  }
  /**
   * Function to load scripts rqeuired for cf7 form loading
   * hooked on WP 4.7 'do_shortcode_tag' filter
   * @since 1.3.0
   * @param string $output Shortcode output.
 	 * @param string $tag    Shortcode name.
 	 * @param array  $attr   Shortcode attributes array,
   * @return     string    shortcode html string.
  **/
  public function load_cf7_script($output, $tag, $attr){

    if('contact-form-7' != $tag){
      return $output;
    }
    if(!isset($attr['id'])){
      debug_msg($attr, "Missing cf7 shortcode id attribute");
      return $output;
    }
    $cf7_id = $attr['id'];
    //let get the corresponding factory object,
    if(Cf7_2_Post_Factory::is_mapped($cf7_id)){
      $plugin_dir = plugin_dir_url( __FILE__ );
      $factory = Cf7_2_Post_Factory::get_factory($cf7_id);
      //unique nonce
      $nonce = 'cf7_2_post_'.wp_create_nonce( 'cf7_2_post'.rand() );

      $scripts = apply_filters('cf7_2_post_form_append_output', '', $attr, $nonce);
      //verify if this cf7 form is mapped to a specific post.
      $cf7_2_post_id ='';
      if(isset($attr['cf7_2_post_id'])){
        $cf7_2_post_id = $attr['cf7_2_post_id'];
      }
      $map_script = $factory->get_form_field_script( $nonce, $cf7_2_post_id );
      $output = '<div id="'.$nonce.'" class="cf7_2_post cf7_form_'.$cf7_id.'">'.$output.PHP_EOL.$map_script.PHP_EOL.$scripts.'</div>';
    }
    return $output;
  }
  /**
   * Register a [save] shortcode with CF7.
   * Hooked  on 'wpcf7_init'
   * This function registers a callback function to expand the shortcode for the save button field.
   * @since 2.0.0
   */
  public function save_button_shortcode_handler() {
    if( function_exists('wpcf7_add_form_tag') ) {
      wpcf7_add_form_tag(
        array( 'save' ),
        array($this,'save_button_display'),
        true //has name
      );
    }
  }
  /**
	 * Dsiplays the save button field
	 * This function expands the shortcode into the required hiddend fields
	 * to manage the googleMap forms.  This function is called by cf7 directly, registered above.
	 *
	 * @since 1.0.0
	 * @param strng $tag the tag name designated in the tag help screen
	 * @return string a set of html fields to capture the googleMap information
	 */
	public function save_button_display( $tag ) {
      //enqueue required scripts and styles
      wp_enqueue_script( $this->plugin_name);

	    $tag = new WPCF7_FormTag( $tag );
      $class = wpcf7_form_controls_class( $tag->type );

    	$atts = array();

    	$atts['class'] = $tag->get_class_option( $class );
      $atts['class'] .=' wpcf7-submit cf7_2_post_save';
    	$atts['id'] = $tag->get_id_option();
    	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

    	if ( empty( $value ) ) {
    		$value = __( 'Save', 'contact-form-7' );
    	}

    	$atts['type'] = 'submit';
    	$atts['value'] = $value;

    	$atts = wpcf7_format_atts( $atts );
    	$html = sprintf( '<input %1$s />', $atts );
      $html .=PHP_EOL.'<input type="hidden" name="save_cf7_2_post" class="cf7_2_post_draft" value="false"/>';
	    return $html;
	}
}
