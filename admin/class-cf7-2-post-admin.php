<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Admin {

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
	 * A factory object o handle the create of mapping posts.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Cf7_2_Post_Factory    $post_mapping_factory   mapping factory object.
	 */
	private $post_mapping_factory;
  /**
	 * A CF7 list table object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Cf7_WP_Post_Table    $cf7_list_table   cf7 admin list table object.
	 */
	private $cf7_list_table;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    $this->cf7_list_table = Cf7_WP_Post_Table::set_table();
    $this->load_dependencies();
	}
  /**
  * Deactivate this plugin if CF7 plugin is deactivated
  * Hooks on action 'admin_init'
  * @since 1.1.0
  */
  public function check_plugin_dependency() {
    //if either the polylang for the cf7 plugin is not active anymore, deactive this extension
    if(is_plugin_active("post-my-contact-form-7/cf7-2-post.php") &&
      !is_plugin_active("contact-form-7/wp-contact-form-7.php") ){
        deactivate_plugins( "post-my-contact-form-7/cf7-2-post.php" );
        wp_die( '<strong>Post My CF7 Form</strong> requires <strong>CF7 plugin</strong> and has been deactivated!' );
        debug_msg("Deactivating CF7 Polylang Module Enxtension");
    }

  }
  /**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cf7_2_Post_Factory. manages the cf7 mapping to custom post.
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
    // for the Cf7_2_Post_Factory class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7-2-post-factory.php';
    //contact post table list
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'assets/cf7-admin-table/admin/cf7-post-admin-table.php';
  }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
    if ('contact_page_cf7_post' != $hook){
      return;
    }
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-2-post-admin.css', array('dashicons'), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
    if ('contact_page_cf7_post' != $hook){
      return;
    }
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cf7-2-post-admin.js', array( 'jquery' ), $this->version, false );
    wp_enqueue_script('jquery-clibboard', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/clipboard/clipboard.min.js', array('jquery'),$this->version,true);
    wp_localize_script( $this->plugin_name, 'cf7_2_post_ajaxData', array('url' => admin_url( 'admin-ajax.php' )));
	}
  /**
  * Modify the regsitered cf7 post type
  * THis function enables public capability and amind UI visibility for the cf7 post type. Hooked late on `init`
  * @since 1.0.0
  *
  */
  public function modify_cf7_post_type(){
    if(class_exists('WPCF7_ContactForm') &&  post_type_exists( WPCF7_ContactForm::post_type ) ) {
        global $wp_post_types;
        $wp_post_types[WPCF7_ContactForm::post_type]->supports[] = 'custom-fields';
    }
  }
  /**
  * Adds a new sub-menu
  * Add a new sub-menu to the Contact main menu to edit mapping post
  *
  */
  public function add_cf7_sub_menu(){
    $hook = add_submenu_page(
      'wpcf7',
      'CF7 Form to post',
      'CF7->Post',
      'manage_options',
      'cf7_post',
      array($this,'display_mapping_page'));
  }
  /**
  * Modify cf7 post type list table columns
  * Hooked on 'modify_{$post_type}_posts_columns', to remove the default columns
  * @since 1.0.0
  * @param      Array    $columns       IDs of existing columns.
  * @return     Array    $columns       IDs of table columns.
  */
  public function modify_cf7_list_columns($columns){
    $columns['mapped_post']= __( 'Post Type', 'cf7-2-post');
    return $columns;
  }
  /**
  * Populate custom columns in cf7 list table
  * @since 1.0.0
  * @param      String    $column       ID of current column to populate.
  * @param      int    $post_id    CF7 post ID for the row to fill.
  */
  public function populate_custom_column( $column, $post_id ) {
    switch ( $column ) {
      case 'mapped_post' :
          $post_type =  get_post_meta( $post_id , '_cf7_2_post-type' , true );
          //$form = WPCF7_ContactForm::get_instance($post_id);
          if ($post_type){
            $status = get_post_meta( $post_id , '_cf7_2_post-map' , true );
            $url = admin_url( 'admin.php?page=cf7_post&id=' . $post_id . '&action=edit' );
            echo '<a href="'.$url.'">'.('draft'==$status ? 'Draft:':'Mapped:').$post_type.'</a>';
          }else{
            $url = admin_url( 'admin.php?page=cf7_post&id=' . $post_id . '&action=new' );
            echo '<a href="'.$url.'">Create new</a>';
          }
          break;
    }
  }
  /**
  * Display the custom admin page for creating post
  * This is a call back function based on the admin menu hook
  * @since 1.0.0
  */
  public function display_mapping_page(){
    if( isset($_GET['id']) ){
      $cf7_post_id = $_GET['id'];
      if( isset($this->post_mapping_factory) && $cf7_post_id == $this->post_mapping_factory->get_cf7_post_id() ){
        $factory_mapping = $this->post_mapping_factory;
      }else{
        $factory_mapping = Cf7_2_Post_Factory::get_factory($cf7_post_id);
        $this->post_mapping_factory = $factory_mapping;
      }
      include( plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-admin-display.php');
    }else{
      $adminUrl = admin_url('edit.php?post_type=wpcf7_contact_form');
      echo '<div><h2>Ooops! Have you taken a wrong turn?</h2>';
      echo '<p>This page is for mapping a CF7 form to a custom post,';
      echo ' please access it from the from <a href="'.$adminUrl.'">table list page</a>.</p></div>';
    }
  }


  /**
  *Save draft with Ajax data submission from admin form.
  * @since 1.0.0
  */
  public function ajax_save_post_mapping(){
    //
    //debug_msg($_POST, "save post ");
    if( !isset($_POST['cf7_2_post_nonce']) || !wp_verify_nonce( $_POST['cf7_2_post_nonce'],'cf7_2_post_mapping') ){
      wp_send_json_error("Security failed, try to reload the page");
    }
    if( isset( $_POST['cf7_post_id'] ) ){

      $cf7_post_id = $_POST['cf7_post_id'];
      $this->post_mapping_factory = Cf7_2_Post_Factory::get_factory($cf7_post_id);
      if($this->post_mapping_factory->is_system_published()){
        $json_data = array('msg'=>'Nothing to update');
        wp_send_json_error($json_data);
        die();
      }
      $create_or_update = false;
      $json_data=array('msg'=>'Unknown action', 'post'=>'unknown');;
      switch(true){
        case isset($_POST['save_draft']):
          $create_or_update = false;
          $result = $this->post_mapping_factory->save($_POST, $create_or_update);
          $json_data = array('msg'=>'Saved draft', 'post'=>'saved');
          break;
        case isset($_POST['update_post']):
          $create_or_update = true;
          $result = $this->post_mapping_factory->update($_POST);
          $json_data = array('msg'=>'Updated post', 'post'=>'created');
          break;
        case isset($_POST['save_post']):
          $create_or_update = true;
          $result = $this->post_mapping_factory->save($_POST, $create_or_update);
          $json_data = array('msg'=>'Created post', 'post'=>'created');
          break;
      }

      if($result){
        //wp_send_json_success( $data );
        wp_send_json_success( $json_data );
      }else{
        $json_data = array('msg'=>'Something is wrong, try to reload the page');
        wp_send_json_error($json_data);
      }
    }else{
      $json_data = array('msg'=>'No CF7 post ID, try to reload the page');
      wp_send_json_error($json_data);
    }
    die();
  }
  /**
  * Loads the custom posts created into the dashboard.
  * @since 1.0.0
  */
  public function register_dynamic_posts(){
    Cf7_2_Post_Factory::register_cf7_post_maps();
  }


  /**
   * Delete existing fields for a given cf7 form, as well as all post data
   * This funciton is hooked on 'wpcf7_post_delete', a filter created by hooking on the cf7 plugin 'wp_redirect' hook in the 'cf7-post-admin-table.php' file
   * @since 1.0.0
   * @param      int    $cf7_post_id    The ID of the cf7 form to be deleted .
  **/
  public function delete_cf7_post($cf7_post_id){
    if(Cf7_2_Post_Factory::is_mapped($cf7_post_id)){
      $delete_all_posts = false;
      //TODO load settings to allow users to delete all submitted form post data when deleting a mapping
      $factory = Cf7_2_Post_Factory::get_factory($cf7_post_id);
      $factory->delete_mapping($delete_all_posts);

    }
  }
  /**
   * Adds a 'save' button shortcode to cf7 forms
   *
   * @since 2.0.0
  **/
  public function cf7_shortcode_tags(){
    if ( class_exists( 'WPCF7_TagGenerator' ) ) {
      $tag_generator = WPCF7_TagGenerator::get_instance();
      $tag_generator->add(
        'save', //tag id
        __( 'save', 'cf7_2_post' ), //tag button label
        array($this,'save_tag_generator'), //callback
        array( 'nameless' => 1 ) //option name less = true, ie no name for this tag
      );
    }
  }

  /**
	 * Sav button tag screen displayt.
	 *
	 * This function is called by cf7 plugin, and is registered with a hooked function above
	 *
	 * @since 1.0.0
	 * @param WPCF7_ContactForm $contact_form the cf7 form object
	 * @param array $args arguments for this form.
	 */
	public function save_tag_generator( $contact_form, $args = '' ) {
    $args = wp_parse_args( $args, array() );
		include( plugin_dir_path( __FILE__ ) . '/partials/cf7-tag-display.php');
	}
  public function inject_footer_script(){
    include( plugin_dir_path( __FILE__ ) . '/partials/cf72post-footer-script.php');
  }
}
