<?php
/**
 * The file that defines the core plugin class.
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
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cf7_2_Post_Loader $loader Maintains and registers all hooks.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Flag to indicate if Smart Grid Layout plugin is active.
	 *
	 * @since    5.3.0
	 * @access   protected
	 * @var      bool $is_smart_grid_active Whether Smart Grid Layout is active.
	 */
	protected $is_smart_grid_active;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 * @param string $version Version number.
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
	 * - Cf7_2_Post_Loader: Orchestrates the hooks
	 * - Cf7_2_Post_I18n: Defines internationalization
	 * - Cf7_2_Post_Admin: Defines admin hooks
	 * - Cf7_2_Post_Public: Defines public hooks
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		$plugin_dir = plugin_dir_path( dirname( __FILE__ ) );

		// Core classes.
		require_once $plugin_dir . 'includes/class-cf72post-mapping-factory.php';
		require_once $plugin_dir . 'includes/class-cf7-2-post-loader.php';
		require_once $plugin_dir . 'includes/wpgurus-debug-api.php';

		// Internationalization.
		require_once $plugin_dir . 'includes/class-cf7-2-post-i18n.php';

		// Admin.
		require_once $plugin_dir . 'admin/class-cf7-2-post-admin.php';

		// Smart Grid Layout compatibility.
		$this->is_smart_grid_active = $this->is_plugin_active( 'cf7-grid-layout/cf7-grid-layout.php' );
		if ( ! $this->is_smart_grid_active ) {
			require_once $plugin_dir . 'assets/cf7-admin-table/cf7-admin-table-loader.php';
		}

		// Public.
		require_once $plugin_dir . 'public/class-cf7-2-post-public.php';

		$this->loader = new Cf7_2_Post_Loader();
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @since 5.3.0
	 * @param string $plugin_path Plugin path (e.g., 'plugin/plugin.php').
	 * @return bool True if plugin is active.
	 */
	private function is_plugin_active( $plugin_path ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin_path );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cf7_2_Post_I18n class to set the domain and register the hook.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Cf7_2_Post_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all hooks related to the admin area functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Cf7_2_Post_Admin( $this->get_plugin_name(), $this->get_version() );

		// General admin hooks.
		$this->register_admin_general_hooks( $plugin_admin );

		// CF7 post type hooks.
		$this->register_cf7_post_hooks( $plugin_admin );

		// Mapped post type hooks.
		$this->register_mapped_post_hooks( $plugin_admin );

		// Additional CF7 hooks.
		$this->register_cf7_specific_hooks( $plugin_admin );
	}

	/**
	 * Register general admin hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Admin $plugin_admin Admin class instance.
	 */
	private function register_admin_general_hooks( $plugin_admin ) {
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'disable_browser_page_cache', 1 );
		$this->loader->add_action( 'init', $plugin_admin, 'register_dynamic_posts', 20 );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_plugin_dependency' );
		$this->loader->add_action( 'admin_print_footer_scripts', $plugin_admin, 'inject_footer_script' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'set_c2p_panel_tab' );

		// Update warning.
		$this->loader->add_action(
			'in_plugin_update_message-post-my-contact-form-7/cf7-2-post.php',
			$plugin_admin,
			'major_update_warning',
			10,
			2
		);
	}

	/**
	 * Register CF7 post type hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Admin $plugin_admin Admin class instance.
	 */
	private function register_cf7_post_hooks( $plugin_admin ) {
		// Modify CF7 post type.
		$this->loader->add_action( 'init', $plugin_admin, 'modify_cf7_post_type', 20 );

		// Modify CF7 list table columns.
		$this->loader->add_filter( 'manage_wpcf7_contact_form_posts_columns', $plugin_admin, 'modify_cf7_list_columns', 30 );
		$this->loader->add_action( 'manage_wpcf7_contact_form_posts_custom_column', $plugin_admin, 'populate_custom_column', 10, 2 );

		// Quick edit.
		$this->loader->add_action( 'quick_edit_custom_box', $plugin_admin, 'quick_edit_box', 20, 2 );
		$this->loader->add_action( 'save_post_wpcf7_contact_form', $plugin_admin, 'save_quick_edit', 10, 2 );

		// Delete CF7 post.
		$this->loader->add_action( 'wpcf7_post_delete', $plugin_admin, 'delete_cf7_post', 10 );

		// CF7 shortcode tags.
		$this->loader->add_action( 'wpcf7_admin_init', $plugin_admin, 'cf7_shortcode_tags', 55 );

		// Add draft message.
		$this->loader->add_action( 'wpcf7_messages', $plugin_admin, 'draft_message' );

		// Email tags.
		$this->loader->add_filter( 'wpcf7_collect_mail_tags', $plugin_admin, 'email_tags' );

		// Editor panels.
		$this->loader->add_filter( 'wpcf7_editor_panels', $plugin_admin, 'add_mapping_panel' );

		// Helper metabox.
		$this->loader->add_action( 'add_meta_boxes_wpcf7_contact_form', $plugin_admin, 'add_helper_metabox' );
	}

	/**
	 * Register mapped post type hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Admin $plugin_admin Admin class instance.
	 */
	private function register_mapped_post_hooks( $plugin_admin ) {
		$mapped_post_types = c2p_mapped_post_types();

		if ( empty( $mapped_post_types ) || ! is_array( $mapped_post_types ) ) {
			return;
		}

		foreach ( $mapped_post_types as $post_id => $type_data ) {
			if ( ! is_array( $type_data ) ) {
				continue;
			}

			$post_type = key( $type_data );
			if ( empty( $post_type ) ) {
				continue;
			}

			$this->register_single_mapped_post_hooks( $plugin_admin, $post_type, $type_data );
		}
	}

	/**
	 * Register hooks for a single mapped post type.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Admin $plugin_admin Admin class instance.
	 * @param string           $post_type    Post type name.
	 * @param array            $type_data    Type data array.
	 */
	private function register_single_mapped_post_hooks( $plugin_admin, $post_type, $type_data ) {
		$source = isset( $type_data[ $post_type ] ) ? $type_data[ $post_type ] : '';

		// Column filters.
		$this->loader->add_filter( 'manage_' . $post_type . '_posts_columns', $plugin_admin, 'modify_cf72post_columns', 999 );
		$this->loader->add_action( 'manage_' . $post_type . '_posts_custom_column', $plugin_admin, 'populate_custom_column', 999, 2 );

		// Save hooks.
		$this->loader->add_action( 'save_post_' . $post_type, $plugin_admin, 'save_quick_custompost', 10, 2 );

		// Metabox for factory posts.
		if ( 'factory' === $source ) {
			$this->loader->add_action( 'add_meta_boxes_' . $post_type, $plugin_admin, 'custom_post_metabox' );
		}

		// Action for registering mapped post.
		$this->loader->add_action(
			'cf72post_register_mapped_post',
			$plugin_admin,
			'cf72post_metabox',
			10,
			5
		);
	}

	/**
	 * Register CF7 specific hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Admin $plugin_admin Admin class instance.
	 */
	private function register_cf7_specific_hooks( $plugin_admin ) {
		// Smart Grid Layout compatibility hooks.
		if ( $this->is_smart_grid_active ) {
			// Add any grid-specific hooks here.
		}
	}

	/**
	 * Register all hooks related to the public-facing functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new Cf7_2_Post_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public->is_smart_grid_active($this->is_smart_grid_active);
		// WP hooks.
		$this->register_public_wp_hooks( $plugin_public );

		// CF7 hooks.
		$this->register_public_cf7_hooks( $plugin_public );
	}

	/**
	 * Register public WP hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Public $plugin_public Public class instance.
	 */
	private function register_public_wp_hooks( $plugin_public ) {
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_styles' );
		$this->loader->add_filter( 'do_shortcode_tag', $plugin_public, 'load_cf7_script', 4, 10 );
		$this->loader->add_action( 'wp_head', $plugin_public, 'disable_browser_page_cache', 1 );
	}

	/**
	 * Register public CF7 hooks.
	 *
	 * @since 5.3.0
	 * @param Cf7_2_Post_Public $plugin_public Public class instance.
	 */
	private function register_public_cf7_hooks( $plugin_public ) {
		// Save form to post.
		$this->loader->add_action( 'wpcf7_before_send_mail', $plugin_public, 'save_cf7_2_post', 100 );

		// Skip mail for drafts.
		$this->loader->add_filter( 'wpcf7_skip_mail', $plugin_public, 'skip_cf7_mail' );

		// Save button shortcode.
		$this->loader->add_action( 'wpcf7_init', $plugin_public, 'save_button_shortcode_handler' );

		// Reset select enum rules (CF7 v5.9+).
		$this->loader->add_action( 'wpcf7_init', $plugin_public, 'reset_select_enum_rules', 100 );

		// Skip validation for saved drafts.
		$this->loader->add_filter( 'wpcf7_validate', $plugin_public, 'save_skips_wpcf7_validate', 100, 2 );
		$this->loader->add_filter( 'wpcf7_validate_file', $plugin_public, 'save_skips_file_validation', 100, 2 );
		$this->loader->add_filter( 'wpcf7_validate_file*', $plugin_public, 'save_skips_file_validation', 100, 2 );

		// Hidden fields.
		$this->loader->add_filter( 'wpcf7_form_hidden_fields', $plugin_public, 'add_hidden_fields', 100 );

		// Draft message filter.
		$this->loader->add_filter( 'wpcf7_display_message', $plugin_public, 'draft_message', 100, 2 );

		// Array to single value for select fields.
		$select_hooks = array(
			'select',
			'select*',
			'dynamic-select',
			'dynamic-select*',
		);

		foreach ( $select_hooks as $hook ) {
			$this->loader->add_filter(
				'wpcf7_posted_data_' . $hook,
				$plugin_public,
				'array_to_single',
				1,
				3
			);
		}
	}

	/**
	 * Run the loader to execute all hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the name of the plugin.
	 *
	 * @since 1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the loader instance.
	 *
	 * @since 1.0.0
	 * @return Cf7_2_Post_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get the version number of the plugin.
	 *
	 * @since 1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}