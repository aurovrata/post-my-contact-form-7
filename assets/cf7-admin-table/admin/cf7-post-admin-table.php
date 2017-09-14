<?php

/**
 * The admin-specific functionality of cf7 custom post table.
 *
 * @link       http://syllogic.in
 * @since      1.1.0
 *
 * @package    Cf7_Polylang
 * @subpackage Cf7_Polylang/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_Polylang
 * @subpackage Cf7_Polylang/admin
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
if(!class_exists('Cf7_WP_Post_Table')){


  class Cf7_WP_Post_Table {
    /**
  	 * A CF7 list table object.
  	 *
  	 * @since    1.1.0
  	 * @access   private
  	 * @var      Cf7_WP_Post_Table    $singleton   cf7 admin list table object.
  	 */
  	private static $singleton;
    private $version;
    /**
  	 * A flag to monitor if hooks are in place.
  	 *
  	 * @since    1.1.0
  	 * @access   private
  	 * @var      boolean    $hooks_set   true if hooks are set.
  	 */
  	private $hooks_set;

    protected function __construct(){
      $this->hooks_set= false;
      $this->version = "1.1";
    }

    public static function set_table(){
      if(null === self::$singleton ){
        self::$singleton = new self();
      }
      return self::$singleton;
    }

    public function hooks(){
      if( !$this->hooks_set ){
        $this->hooks_set= true;
        return false;
      }
      return $this->hooks_set;
    }
    /**
  	 * Register the stylesheets for the admin area.
  	 *
  	 * @since    1.1.0
  	 */
  	public function enqueue_styles() {
      $screen = get_current_screen();
      if ('wpcf7_contact_form' != $screen->post_type){
        return;
      }

      switch( $screen->base ){
        case 'post':
  		    //for the future
          break;
        case 'edit':
          wp_enqueue_style( 'cf7-post-table-css', plugin_dir_url( __FILE__ ) . 'css/cf7-admin-table.css', false, $this->version );
          break;
      }
  	}
    public function enqueue_script() {
      $screen = get_current_screen();
      if ('wpcf7_contact_form' != $screen->post_type){
        return;
      }

      switch( $screen->base ){
        case 'post':
  		    //for the future
          break;
        case 'edit':
          //get all cf7 forms
          $cf7_posts = get_posts(array(
            'post_type'=>'wpcf7_contact_form',
            'posts_per_page' => -1,
          ));
          $keys=array();
          if(!empty($cf7_posts)){
            foreach($cf7_posts as $cf7){
              $keys[]=$cf7->post_name;
            }
            wp_reset_postdata();
          }
          wp_enqueue_script('jquery-effects-core');
          wp_enqueue_script( 'cf7-post-table-js', plugin_dir_url( __FILE__ ) . 'js/cf7-post-table.js', false, $this->version, true );
          wp_localize_script('cf7-post-table-js','cf7_2_post_admin', array('keys'=>$keys));
          break;
      }
  	}

    /**
  	 * Loads footer script on admin table list page
     * script to chagne the link to the 'Add New' button, hooked on 'admin_print_footer_scripts'
  	 *
  	 * @since    1.1.3
  	 */
  	public function change_add_new_button() {
  		if( !$this->is_cf7_admin_page() ){
  			return;
  		}
      $url = admin_url('admin.php?page=wpcf7-new');
      ?>
      <script type='text/javascript'>
        (function( $ ) {
          'use strict';
          $(document).ready(function() {
            $('h1 > a.page-title-action').attr('href','<?php echo $url; ?>');
            $('h1 ~ a.page-title-action').attr('href','<?php echo $url; ?>');
          });
        })( jQuery );
      </script>
      <?php

  	}
    /**
     * get form id for a given key
     *
     * @since 1.2.0
     * @param      string    $form_key   the unique key for qhich to get the id  .
     * @return     string    form id     .
    **/
    public static function form_id($form_key){
      $form_id = 0;
      $forms = get_posts(array(
        'post_type' => 'wpcf7_contact_form',
        'post_name' => $form_key
      ));
      if(!empty($forms)){
        $form_id = $forms[0]->ID;
        wp_reset_postdata();
      }
      return $form_id;
    }
    /**
    *  Checks if this is the admin table list page
    *
    * @since 1.1.3
    */
    public static function is_cf7_admin_page(){
      if(!isset($_GET['post_type']) || false === strpos($_GET['post_type'],WPCF7_ContactForm::post_type) ){
  			return false;
  		}else{
        $screen = get_current_screen();
        return ( 'edit' == $screen->base && '' == $screen->action );
      }
    }
    /**
  	 * check if this is a cf7 edit page.
  	 *
  	 * @since    1.1.3
  	 * @return    bool    true is this is the edit page
  	 */
  	public static function is_cf7_edit_page(){
      if(!isset($_GET['page']) || false === strpos($_GET['page'],'wpcf7') ){
        return false;
      }else{
        if(isset($_GET['post']) ){
          global $post_ID; //need to set the global post ID to make sure it is available for polylang.
          $post_ID = $_GET['post'];
        }
        if(function_exists('get_current_screen')){
          $screen = get_current_screen(); //use screen option after intial basic check else it may throw fatal error
          return ( 'contact_page_wpcf7-new' == $screen->base || 'toplevel_page_wpcf7' == $screen->base );
        }else{
          return false;
        }
      }
  	}
    /**
    * Modify the regsitered cf7 post tppe
    * THis function enables public capability and amind UI visibility for the cf7 post type. Hooked late on `init`
    * @since 1.0.0
    *
    */
    public function modify_cf7_post_type(){
      if(class_exists('WPCF7_ContactForm') &&  post_type_exists( WPCF7_ContactForm::post_type ) ) {
          global $wp_post_types;
          $wp_post_types[WPCF7_ContactForm::post_type]->public = true;
          $wp_post_types[WPCF7_ContactForm::post_type]->show_ui = true;
      }
    }

    /**
    * Adds a new sub-menu
    * Add a new sub-menu to the Contact main menu, as well as remove the current default
    *
    */
    public function add_cf7_sub_menu(){

      $hook = add_submenu_page(
        'wpcf7',
        __( 'Edit Contact Form', 'contact-form-7' ),
    		__( 'Contact Forms', 'contact-form-7' ),
    		'wpcf7_read_contact_forms',
        'edit.php?post_type=wpcf7_contact_form');
      //remove_submenu_page( $menu_slug, $submenu_slug );
      remove_submenu_page( 'wpcf7', 'wpcf7' );
    }

    /**
    * Change the submenu order
    * @since 1.0.0
    */
    public function change_cf7_submenu_order( $menu_ord ) {
        global $submenu;
        // Enable the next line to see all menu orders
        if(!isset($submenu['wpcf7']) ){
          return $menu_ord;
        }
        if( is_network_admin() ){
          return $menu_ord;
        }
        $arr = array();
        foreach($submenu['wpcf7'] as $menu){
          switch($menu[2]){
            case 'cf7_post': //do nothing, we hide this submenu
              $arr[]=$menu;
              break;
            case 'edit.php?post_type=wpcf7_contact_form':
              //push to the front
              array_unshift($arr, $menu);
              break;
            default:
              $arr[]=$menu;
              break;
            }
          }
        $submenu['wpcf7'] = $arr;
        return $menu_ord;
    }
    /**
    * Modify cf7 post type list table columns
    * Hooked on 'manage_{$post_type}_posts_columns', to remove the default columns
    * @since 1.0.0
    * @param      Array    $columns     array of columns to display.
    * @return     Array    array of columns to display.
    */
    public function modify_cf7_list_columns($columns){
      $columns['shortcode'] = 'Shortcode<br /><span class="cf7-help-tip"><a href="javascript:void();">What\'s this?</a><span class="cf7-short-info">Use this shortcode the same way you would use the contact-form-7 shortcode. (See the plugin page for more information )</span></span>';
      $columns['cf7_key'] = __('Form key', 'contact-form-7');
      return $columns;
    }
    /**
    * Populate custom columns in cf7 list table
    * hooked on 'manage_{$post_type}_posts_custom_column'
    * @since 1.0.0
    * @param      String    $column     column key.
    * @param      Int    $post_id     row post id.
    * @return     String    value to display.
    */
    public function populate_custom_column( $column, $post_id ) {
      switch ( $column ) {
        case 'shortcode' :

          $form = get_post($post_id);
    			$output = "\n" . '<span class="shortcode cf7-2-post-shortcode"><input type="text"'
    				. ' onfocus="this.select();" readonly="readonly"'
    				. ' value="' . esc_attr( '[cf7-form cf7key="'.$form->post_name.'"]' ) . '"'
    				. ' class="large-text code" /></span>';

      		echo trim( $output );

          break;
        case 'cf7_key':
          $form = get_post($post_id);
          echo '<span class="cf7-form-key">'.$form->post_name.'</span>';
          break;
      }
    }

    /**
  	 * Modify the quick action links in the contact table.
  	 * Since this plugin replaces the default contact form list table
     * for the more std WP table, we need to modify the quick links to match the default ones.
     * This function is hooked on 'post_row_actions'
  	 * @since    1.1.0
     * @param Array  $actions  quick link actions
     * @param WP_Post $post the current row's post object
     */
    public function modify_cf7_list_row_actions($actions, $post){
        //check for your post type
        if('trash'==$post->post_status) return array();

        if ($post->post_type =="wpcf7_contact_form"){
          $form = WPCF7_ContactForm::get_instance($post->ID);
          $url = admin_url( 'admin.php?page=wpcf7&post=' . absint( $form->id() ) );
          $edit_link = add_query_arg( array( 'action' => 'edit' ), $url );
          $idx = strpos($actions['trash'],'_wpnonce=') + 9;
          $nonce = substr($actions['trash'], $idx, strpos($actions['trash'],'"', $idx) - $idx);

          if ( current_user_can( 'wpcf7_edit_contact_form', $form->id() ) ) {
            $actions['edit'] = sprintf(
              '<a href="%1$s">%2$s</a>',
              esc_url( $edit_link ),
              esc_html( __( 'Edit', 'contact-form-7' ) )
            );

            $actions['trash'] = sprintf(
              '<a href="%1$s">%2$s</a>',
              admin_url( 'post.php?post=' . $post->ID . '&action=trash&_wpnonce=' . $nonce ),
              esc_html( __( 'Trash', 'contact-form-7' ) )
            );

            $copy_link = wp_nonce_url(
              add_query_arg( array( 'action' => 'copy' ), $url ),
              'wpcf7-copy-contact-form_' . absint( $form->id() )
            );

            $actions['copy'] = sprintf(
              '<a href="%1$s">%2$s</a>',
              esc_url( $copy_link ),
              esc_html( __( 'Duplicate', 'contact-form-7' ) )
            );
          }
        }
        return $actions;
    }
    /**
     * Redirect to new table list on form delete
     * hooks on 'wp_redirect'
     * @since 1.1.3
     * @var string $location a fully formed url
     * @var int $status the html redirect status code
     */
     public function filter_cf7_redirect($location, $status){
       if( self::is_cf7_admin_page() || self::is_cf7_edit_page() ){
         if( 'delete' == wpcf7_current_action()){
           global $post_ID;
           do_action('wpcf7_post_delete',$post_ID);

           return admin_url('edit.php?post_type=wpcf7_contact_form');
         }
       }
       return $location;
     }

    /**
     * Function to populate the quick edit form
     * Hooked on 'quick_edit_custom_box' action
     *
     * @since 1.0.0
     * @param      string    $column_name     column name to add edit field.
     * @param      string    $post_type     post type being displayed.
     * @return     string    echos the html fields.
    **/
    /*public function quick_edit_box( $column_name, $post_type ) {
      if("wpcf7_contact_form" != $post_type){
        return;
      }
      static $printNonce = TRUE;
      if ( $printNonce ) {
          $printNonce = FALSE;
          wp_nonce_field( plugin_basename( __DIR__ ), 'cf7_key_nonce' );
      }
      switch ( $column_name ) {
        default:
          //echo '';
          break;
      }
    }*/
    /**
     *cf7-form Shortcode handler
     *
     * @since 1.0.0
     * @param      Array    $atts     array of attributes.
     * @return     string    $p2     .
    **/
    public function shortcode( $atts ) {
      $a = array_merge( array(
          'cf7key' => '',
      ), $atts );
      if(empty($a['cf7key'])){
        return '<em>' . _('cf7-form shortcode missing key','cf7-admin-table') . '</em>';
      }
      //else get the post ID
      $form = get_posts(array(
        'post_type' => 'wpcf7_contact_form',
        'name' => $a['cf7key']
      ));
      if(!empty($form)){
        $id = apply_filters('cf7_form_shortcode_form_id',$form[0]->ID, $atts);

        wp_reset_postdata();
        $attributes ='';
        foreach($a as $key=>$value){
          $attributes .= ' '.$key.'="'.$value.'"';
        }
        return do_shortcode('[contact-form-7 id="'.$id.'"'.$attributes.']');
      }else{
        return '<em>' . _('cf7-form shortcode key error, unable to find form','cf7-admin-table') . '</em>';
      }
    }
  } //end class
  function get_cf7form_id($cf7_key){
  	return Cf7_WP_Post_Table::form_id($cf7_key);
  }
}
