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
	 * @param string $version version number.
	 * @since    1.0.0
	 */
	public function __construct( $version ) {

		$this->plugin_name = 'post-my-contact-form-7';
		$this->version     = $version;
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
	 * - Cf7_2_Post_I18n. Defines internationalization functionality.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf72post-mapping-factory.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7-2-post-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wpgurus-debug-api.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cf7-2-post-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cf7-2-post-admin.php';
		if ( ! in_array( 'cf7-grid-layout/cf7-grid-layout.php', get_option( 'active_plugins', array() ) ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'assets/cf7-admin-table/cf7-admin-table-loader.php';
		}
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
	 * Uses the Cf7_2_Post_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cf7_2_Post_I18n();

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
		// WP hooks.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// no cahing metas.
		$this->loader->add_action( 'admin_head', $plugin_admin, 'disable_browser_page_cache', 1 );
		// modify the CF7 post type.
		$this->loader->add_action( 'init', $plugin_admin, 'modify_cf7_post_type', 20 );
		// modify the cf7 list table columns.
		$this->loader->add_filter( 'manage_wpcf7_contact_form_posts_columns', $plugin_admin, 'modify_cf7_list_columns', 30, 1 );
		$this->loader->add_action( 'manage_wpcf7_contact_form_posts_custom_column', $plugin_admin, 'populate_custom_column', 10, 2 );
		// register dynamic posts.
		$this->loader->add_action( 'init', $plugin_admin, 'register_dynamic_posts', 20 );
		// make sure our dependent plugins exists.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_plugin_dependency' );
		// hide the cf7->post page form the submenu.
		$this->loader->add_action( 'admin_print_footer_scripts', $plugin_admin, 'inject_footer_script' );
		// quick-edit.
		$this->loader->add_action( 'quick_edit_custom_box', $plugin_admin, 'quick_edit_box', 20, 2 );
		$this->loader->add_action( 'save_post_wpcf7_contact_form', $plugin_admin, 'save_quick_edit', 10, 2 );

		// hide mapping submenu page.
		/** Add metabox to mapped posts @since 3.3.0 */
		$this->loader->add_action( 'cf72post_register_mapped_post', $plugin_admin, 'cf72post_metabox', 10, 5 );
		// CF7 Hooks.
		// delete post.
		$this->loader->add_action( 'wpcf7_post_delete', $plugin_admin, 'delete_cf7_post', 10, 1 );
		// add the 'save' button tag.
		$this->loader->add_action( 'wpcf7_admin_init', $plugin_admin, 'cf7_shortcode_tags', 55, 0 );
		/** NB @since 4.0.0  add save-darft message*/
		$this->loader->add_action( 'wpcf7_messages', $plugin_admin, 'draft_message' );

		/** Hook to modify custom post in dashboard @since 3.4.0 */
		$cf7_2_post_type = c2p_mapped_post_types();
		foreach ( $cf7_2_post_type as $post_id => $type ) {
			$post_type = key( $type );
			$this->loader->add_filter( 'manage_' . $post_type . '_posts_columns', $plugin_admin, 'modify_cf72post_columns', 999, 1 );
			$this->loader->add_action( 'manage_' . $post_type . '_posts_custom_column', $plugin_admin, 'populate_custom_column', 999, 2 );
			// on save cf7 post type.
			$this->loader->add_action( 'save_post_' . $post_type, $plugin_admin, 'save_quick_custompost', 10, 2 );
			switch ( $type[ $post_type ] ) {
				case 'factory': /*add a metabox to the post edit page*/
					$this->loader->add_action( 'add_meta_boxes_' . $post_type, $plugin_admin, 'custom_post_metabox' );
					break;
				default:
					break;
			}
		}
		/** NB @since 4.1.0 mail tag for post links */
		$this->loader->add_filter( 'wpcf7_collect_mail_tags', $plugin_admin, 'email_tags' );

		/** NB @since 5.0 */
		$this->loader->add_filter( 'wpcf7_editor_panels', $plugin_admin, 'add_mapping_panel' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'set_c2p_panel_tab' );

		/** NB @since 5.0.0 hook the smart grid form saving action to fix double save_post hook call
		* calls to save_post_mapping moved to 'admin_init' hook above.
		*/
		// show helper metabox.
		$this->loader->add_action( 'add_meta_boxes_wpcf7_contact_form', $plugin_admin, 'add_helper_metabox' );
		/** NB @since 5.6.1 Warn users on major verison plugin update page */
		$this->loader->add_action( 'in_plugin_update_message-post-my-contact-form-7/cf7-2-post.php', $plugin_admin, 'major_update_warning', 10, 2 );
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
		// WP hooks.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_styles' );
		$this->loader->add_filter( 'do_shortcode_tag', $plugin_public, 'load_cf7_script', 4, 3 ); // 4: add before cf7 grid.
		// no cahing metas.
		$this->loader->add_action( 'wp_head', $plugin_public, 'disable_browser_page_cache', 1 );

		// CF7 Hooks.
		// use before_send_mail to ensure mapping post form validation.
		$this->loader->add_action( 'wpcf7_before_send_mail', $plugin_public, 'save_cf7_2_post', 100 );
		// skip mail for draft forms.
		$this->loader->add_filter( 'wpcf7_skip_mail', $plugin_public, 'skip_cf7_mail' );
		// instroduced a 'save button tag for forms.
		$this->loader->add_action( 'wpcf7_init', $plugin_public, 'save_button_shortcode_handler' );
		// skip validation for saved forms.
		$this->loader->add_filter( 'wpcf7_validate', $plugin_public, 'save_skips_wpcf7_validate', 100, 2 );
		$this->loader->add_filter( 'wpcf7_validate_file', $plugin_public, 'save_skips_file_validation', 100, 2 );
		$this->loader->add_filter( 'wpcf7_validate_file*', $plugin_public, 'save_skips_file_validation', 100, 2 );
		// add the author map for logged in user @since 3.9.0.
		$this->loader->add_filter( 'wpcf7_form_hidden_fields', $plugin_public, 'add_hidden_fields', 100, 2 );
		// filter message for draft saved forms.
		$this->loader->add_filter( 'wpcf7_display_message', $plugin_public, 'draft_message', 100, 2 );
		/** NB @since 4.1.8 filter selectable field types, fix for CF7 v5.2.1 changes. */
		$this->loader->add_filter( 'wpcf7_posted_data_select', $plugin_public, 'array_to_single', 1, 3 );
		$this->loader->add_filter( 'wpcf7_posted_data_select*', $plugin_public, 'array_to_single', 1, 3 );
		$this->loader->add_filter( 'wpcf7_posted_data_dynamic-select', $plugin_public, 'array_to_single', 1, 3 );
		$this->loader->add_filter( 'wpcf7_posted_data_dynamic-select*', $plugin_public, 'array_to_single', 1, 3 );
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
