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
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
    $plugin_dir = plugin_dir_url( __FILE__ );
    wp_register_script( $this->plugin_name.'-save', $plugin_dir . 'js/cf7-2-post-save-draft.js', array( 'jquery' ), $this->version, true );
		wp_register_script( $this->plugin_name.'-load', $plugin_dir . 'js/cf7-2-post-public.js', array( 'jquery' ), $this->version, true );
    // wp_register_script(
    //     'cf7-2-post-loader', //handle
    //     plugin_dir_url( __DIR__ ) . 'includes/partials/cf7-2-post-script.php', // src
    //     array('jquery'), // dependencies, I use jquery in dynamic-javascript.php
    //     $this->version, // version number,
    //     false //in footer
    // );
	}
  /**
  * Saves a cf7 form submission to its mapped post
  * Hooks 'wpcf7_before_send_mail' just after all validation is done
  * @since 1.0.0
  * @param  WPCF7_Contact_Form $cf7_form  cf7 form object
  */
  public function save_cf7_2_post($cf7_form){

    //load the form factory
    $cf7_post_id = $cf7_form->id();
    //is this form mapped yet?
    if(Cf7_2_Post_Factory::is_mapped($cf7_post_id)){
      $factory = Cf7_2_Post_System::get_factory($cf7_post_id);

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
   * Function to skip mail if this is a draft form being sent.
   * Hooked on 'wpcf7_skip_mail'.  Skip mail also stops form clearance on being saved.
   * @since 2.0.0
   * @param      boolean    $skip_mail     boolean flag.
   * @return     boolean    true to skip mails if this is adraft form being saved .
  **/
  public function skip_cf7_mail($skip_mail){
    if(isset($_POST['save_cf7_2_post']) && 'true'==$_POST['save_cf7_2_post']){
      $skip_mail = true;
    }
    return $skip_mail;
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
      //let's ensure the page does not cache our values.
			debug_msg('setting up metas');
      add_action('wp_head', array($this, 'disable_browser_page_cache'), 1);
      //$plugin_dir = plugin_dir_url( __FILE__ );
      $factory = Cf7_2_Post_System::get_factory($cf7_id);
      //unique nonce
      $nonce = 'cf7_2_post_'.wp_create_nonce( 'cf7_2_post'.rand() );

      $scripts = apply_filters('cf7_2_post_form_append_output', '', $attr, $nonce);
      //verify if this cf7 form is mapped to a specific post.
      $cf7_2_post_id ='';
      if(isset($attr['cf7_2_post_id'])){
        $cf7_2_post_id = $attr['cf7_2_post_id'];
      }
      $form_values = $factory->get_form_values($cf7_2_post_id);
      $inline_script = $factory->get_form_field_script( $nonce );
      wp_enqueue_script($this->plugin_name.'-load'); //previously registered.
      wp_localize_script($this->plugin_name.'-load', $nonce, $form_values);
      $output = '<div id="'.$nonce.'" class="cf7_2_post cf7_form_'.$cf7_id.'">'.$output.PHP_EOL.$inline_script.PHP_EOL.$scripts.'</div>';
    }
    return $output;
  }
  /**
  *Disables browser page caching for forms which are mapped to a post.
  * Hooked on 'wp_head' in fn load_cf7_script.
  *@since 2.5.0
  */
  public function disable_browser_page_cache(){
		debug_msg('printing metas');
      ?>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
      <?php
  }
	/**
	* Function to load dynamic script for post 2 map form filling.
	* Hooked to 'wp_ajax_load_post_2_cf7'
	*@since 2.5.0
	*/
	public function load_dynamic_script(){
		if(!isset($_REQUEST['wpnonce']) || !isset($_REQUEST['form'])){
			die('CF72POST: wrong ajax call, no attributes found!');
		}
		$nonce = $_REQUEST['wpnonce'];
		$cf7_id = $_REQUEST['form'];
    debug_msg($_REQUEST, 'request ');
    debug_msg($_GET, 'get ');
    debug_msg('nonce: '.wp_verify_nonce( $nonce, 'post-2-cf7-'.$cf7_id ));
		if( ! wp_verify_nonce( $nonce, 'post-2-cf7-'.$cf7_id ) ) {
      debug_msg('incalid nonce');
			die( 'CF72POST: invalid nonce' );
		} else {
			$factory = Cf7_2_Post_System::get_factory($cf7_id);
			require_once  plugin_dir_path( __DIR__ ) . '/includes/partials/cf7-2-post-script.php';
		}
		exit;
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
      wp_enqueue_script( $this->plugin_name.'-save');

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
  /**
   * Reset cf7 validation if this form is being saved as a draft.
   * Hooked to filter 'wpcf7_validate', sets up the final $results object
   * @since 2.0.0
   * @param WPCF7_Validation $results   validation object
   * @param Array $tags   an array of cf7 tag used in this form
   * @return WPCF7_Validation  validation results
  **/
  public function save_skips_wpcf7_validate($results, $tags){
    if( !isset($_POST['save_cf7_2_post']) || 'false'==$_POST['save_cf7_2_post']){
      return $results;
    }
    $cf7form = WPCF7_ContactForm::get_current();
    $cf7_id = $cf7form->id();
    $cf7_post = get_post($cf7_id, ARRAY_A);
    $cf7_key = $cf7_post['post_name'];
    /**
    * Filter to skip validation if this form is being saved as a draft
    * @since 2.0.0
    * @param boolean $skip_validation  default to true
    * @param string $cf7_key  current form's unique key identifier
    */
    $skip_validation = true;

    if(apply_filters('cf7_2_post_draft_skips_validation', $skip_validation, $cf7_key)){
      $results = new WPCF7_Validation();
    }
    //skip mail by default
    $skip_mail = true;
    /**
    * Filter to skip mail sending if this form is being saved as a draft
    * @since 2.0.0
    * @param boolean $skip_mail  default to true
    * @param string $cf7_key  current form's unique key identifier
    */
    if(apply_filters('cf7_2_post_draft_skips_mail', $skip_mail, $cf7_key)){
      add_filter('wpcf7_skip_mail', function($skip_mail, $contact_form) use($cf7_id){
        if($cf7_id === $contact_form->id()){
          return true;
        }else{
          return $skip_mail;
        }
      }, 10, 2);
    }
    return $results;
  }
}
