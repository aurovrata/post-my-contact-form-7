<?php

/**
 * The file that defines the core plugin class
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

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cf7_2_Post_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($version) {

		$this->plugin_name = 'post-my-contact-form-7';
		$this->version = $version;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cf7_2_Post_Loader. Orchestrates the hooks of the plugin.
	 * - Cf7_2_Post_i18n. Defines internationalization functionality.
	 * - Cf7_2_Post_Admin. Defines all hooks for the admin area.
	 * - Cf7_2_Post_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7-2-post-loader.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wordpress-gurus-debug-api.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7-2-post-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cf7-2-post-admin.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'assets/cf7-admin-table/cf7-admin-table-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cf7-2-post-public.php';

		$this->loader = new Cf7_2_Post_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cf7_2_Post_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cf7_2_Post_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Cf7_2_Post_Admin( $this->get_plugin_name(), $this->get_version() );
    /* WP hooks */
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    //no cahing metas
		$this->loader->add_action('admin_head', $plugin_admin, 'disable_browser_page_cache', 1);
    //modify the CF7 post type
    $this->loader->add_action('init', $plugin_admin, 'modify_cf7_post_type',20);
    //cf7 sub-menu
    $this->loader->add_action('admin_menu',  $plugin_admin, 'add_cf7_sub_menu' );
    //$this->loader->add_filter( 'custom_menu_order', $plugin_admin, 'change_cf7_submenu_order' );
    //modify the cf7 list table columns
    $this->loader->add_filter('manage_wpcf7_contact_form_posts_columns' , $plugin_admin, 'modify_cf7_list_columns',30,1);
    $this->loader->add_action('manage_wpcf7_contact_form_posts_custom_column', $plugin_admin, 'populate_custom_column',10,2);
    //ajax submission
    $this->loader->add_action('wp_ajax_save_post_mapping', $plugin_admin, 'ajax_save_post_mapping');
    $this->loader->add_action('wp_ajax_get_meta_options', $plugin_admin, 'ajax_get_meta_options');
    //register dynamic posts
    $this->loader->add_action('init',$plugin_admin, 'register_dynamic_posts',20);
    //make sure our dependent plugins exists.
    $this->loader->add_action( 'admin_init', $plugin_admin, 'check_plugin_dependency');
    //hide the cf7->post page form the submenu
    $this->loader->add_action( 'admin_print_footer_scripts', $plugin_admin, 'inject_footer_script');
    //quick-edit
    $this->loader->add_action( 'quick_edit_custom_box',   $plugin_admin, 'quick_edit_box', 20, 2 );
    //save quick edit
    $this->loader->add_action('save_post_wpcf7_contact_form', $plugin_admin, 'save_quick_edit', 10);
    //hide mapping submenu page.
    //$this->loader->add_filter( 'custom_menu_order', $plugin_admin, 'hide_mapping_menu', 10);
    /**
    * add metabox to mapped posts.
    * @since 3.3.0
    */
    $this->loader->add_action('cf72post_register_mapped_post', $plugin_admin, 'cf72post_metabox');
    /* CF7 Hooks */
    //delete post
    $this->loader->add_action( 'wpcf7_post_delete',$plugin_admin, 'delete_cf7_post',10,1);
    //add the 'save' button tag
    $this->loader->add_action( 'wpcf7_admin_init', $plugin_admin, 'cf7_shortcode_tags' );

    /**
    * hook to modify custom post in dashboard
    * @since 3.4.0
    */
    $cf7_post_ids = Cf7_2_Post_Factory::get_mapped_post_types();
    foreach($cf7_post_ids as $post_id=>$type){
      $post_type = key($type);
      switch($type[$post_type]){
        case 'factory':
          $this->loader->add_filter('manage_' . $post_type . '_posts_columns', $plugin_admin, 'modify_cf72post_columns',5,1);
					$this->loader->add_action('manage_' . $post_type . '_posts_custom_column', $plugin_admin, 'populate_custom_column',10,2);
          //on save cf7 post type
          $this->loader->add_action( 'save_post_'. $post_type, $plugin_admin,'save_quick_custompost', 10, 2 );
          $this->loader->add_action( 'add_meta_boxes_'. $post_type, $plugin_admin,'custom_post_metabox' );
          break;
        default:
          break;
      }
    }
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Cf7_2_Post_Public( $this->get_plugin_name(), $this->get_version() );
    /* WP hooks */
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    $this->loader->add_filter( 'do_shortcode_tag', $plugin_public, 'load_cf7_script', 100,3 );
		//no cahing metas
		$this->loader->add_action('wp_head', $plugin_public, 'disable_browser_page_cache', 1);

    /*CF7 Hooks*/
    //use before_send_mail to ensure mapping post form validation
    $this->loader->add_action( 'wpcf7_before_send_mail', $plugin_public, 'save_cf7_2_post', 100);
    //skip mail for draft forms
    $this->loader->add_filter('wpcf7_skip_mail', $plugin_public, 'skip_cf7_mail');
    //instroduced a 'save button tag for forms
    $this->loader->add_action( 'wpcf7_init', $plugin_public, 'save_button_shortcode_handler' );
    //skip validation for saved forms
    $this->loader->add_filter( 'wpcf7_validate', $plugin_public, 'save_skips_wpcf7_validate', 100, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cf7_2_Post_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
