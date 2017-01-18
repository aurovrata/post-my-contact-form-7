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
  		//let's check if this is a cf7 admin page
  		/*if( !($action = $this->is_cf7_admin_page()) ){
  			return;
  		}*/
  		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-polylang-admin.css', array('contact-form-7-admin'), $this->version, 'all' );
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
    * Hooked on 'modify_{$post_type}_posts_columns', to remove the default columns
    *
    */
    public function modify_cf7_list_columns($columns){
      return array(
          'cb' => '<input type="checkbox" />',
          'custom_title' => __( 'Title', 'contact-form-7' ),
          'shortcode' => __( 'Shortcode', 'contact-form-7'),
          'custom_author' => __('Author', 'contact-form-7'),
          'date' => __('Date', 'contact-form-7')
      );
    }
    /**
    * Populate custom columns in cf7 list table
    * @since 1.0.0
    *
    */
    public function populate_custom_column( $column, $post_id ) {
      switch ( $column ) {
        case 'custom_title':
          if( !class_exists('WPCF7_ContactForm') ){
            echo 'No CF7 Form class';
          }else{
            $form = WPCF7_ContactForm::get_instance($post_id);
            $url = admin_url( 'admin.php?page=wpcf7&post=' . absint( $form->id() ) );
        		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

        		$output = sprintf(
        			'<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
        			esc_url( $edit_link ),
        			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'contact-form-7' ),
        				$form->title() ) ),
        			esc_html( $form->title() ) );

        		$output = sprintf( '<strong>%s</strong>', $output );

        		if ( function_exists('wpcf7_validate_configuration') && wpcf7_validate_configuration()
          		&& current_user_can( 'wpcf7_edit_contact_form', $form->id() ) ) {
                $config_validator = new WPCF7_ConfigValidator( $form );

          			if ( $count_errors = $config_validator->count_errors() ) {
          				$error_notice = sprintf(
          					_n(
          						'%s configuration error found',
          						'%s configuration errors found',
          						$count_errors, 'contact-form-7' ),
          					number_format_i18n( $count_errors ) );
          				$output .= sprintf(
          					'<div class="config-error">%s</div>',
          					$error_notice );
          			}
          	}

            echo $output;
        		//$output .= $this->row_actions( $actions );

          }

          break;
        case 'shortcode' :
          if( !class_exists('WPCF7_ContactForm') ){
            echo 'No CF7 Form class found';
          }else{
            $form = WPCF7_ContactForm::get_instance($post_id);
            $shortcodes = array( $form->shortcode() );
        		$output = '';
        		foreach ( $shortcodes as $shortcode ) {
        			$output .= "\n" . '<span class="shortcode"><input type="text"'
        				. ' onfocus="this.select();" readonly="readonly"'
        				. ' value="' . esc_attr( $shortcode ) . '"'
        				. ' class="large-text code" /></span>';
        		}
        		echo trim( $output );
          }
          break;
        case 'custom_author':
          $post = get_post( $post_id );
          if ( ! $post ) {
            break;
          }
          $author = get_userdata( $post->post_author );
          if ( false === $author ) {
            break;
          }
          echo esc_html( $author->display_name );
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
          $trash = $actions['trash'];

          $actions = array(
            'edit' => sprintf( '<a href="%1$s">%2$s</a>',
              esc_url( $edit_link ),
              esc_html( __( 'Edit', 'contact-form-7' ) ) ) );

          if ( current_user_can( 'wpcf7_edit_contact_form', $form->id() ) ) {
            $copy_link = wp_nonce_url(
              add_query_arg( array( 'action' => 'copy' ), $url ),
              'wpcf7-copy-contact-form_' . absint( $form->id() ) );

            $actions = array_merge( $actions, array(
              'copy' => sprintf( '<a href="%1$s">%2$s</a>',
                esc_url( $copy_link ),
                esc_html( __( 'Duplicate', 'contact-form-7' ) ) ) ) );
                //reinsert thrash link
                //$actions['trash']=$trash;
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
  }

}
