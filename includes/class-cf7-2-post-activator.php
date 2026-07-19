<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Activator {

	/**
	 * The plugin slug for Contact Form 7.
	 *
	 * @since 5.3.0
	 * @var string
	 */
	const CF7_PLUGIN_SLUG = 'contact-form-7/wp-contact-form-7.php';

	/**
	 * Activate the plugin.
	 *
	 * Checks for required dependencies and sets up initial configuration.
	 * For multisite installations, activation should be done per site.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate() {
		// Check for required dependencies.
		if ( ! self::check_dependencies() ) {
			return;
		}

		// Set up default options.
		self::setup_default_options();

		// Flush rewrite rules for custom post types.
		self::flush_rewrite_rules();

		// Set activation flag.
		self::set_activation_flag();
	}

	/**
	 * Check if all required dependencies are met.
	 *
	 * @since 5.3.0
	 * @return bool True if all dependencies are met.
	 */
	public static function check_dependencies() {
		// Ensure plugin functions are available.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if Contact Form 7 is active.
		if ( ! is_plugin_active( self::CF7_PLUGIN_SLUG ) ) {
			self::handle_missing_dependency();
			return false;
		}

		return true;
	}

	/**
	 * Handle missing dependency error.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private static function handle_missing_dependency() {
		$error_message = self::get_dependency_error_message();

		if ( is_multisite() ) {
			$error_message .= ' ' . esc_html__(
				'If you have activated it on select sites, you will need to activate the Post My CF7 Form plugin on those sites only.',
				'post-my-contact-form-7'
			);
		}

		self::display_activation_error( $error_message );
	}

	/**
	 * Get dependency error message.
	 *
	 * @since 5.3.0
	 * @return string The error message.
	 */
	private static function get_dependency_error_message() {
		return esc_html__(
			'Post My CF7 Form requires the Contact Form 7 plugin to be installed and activated.',
			'post-my-contact-form-7'
		);
	}

	/**
	 * Display activation error.
	 *
	 * @since 5.3.0
	 * @param string $error_message The error message to display.
	 * @return void
	 */
	private static function display_activation_error( $error_message ) {
		// Store error message for admin notice.
		self::store_activation_error( $error_message );

		// Deactivate this plugin.
		self::deactivate_plugin();

		// Display error and exit.
		wp_die(
			esc_html( $error_message ),
			esc_html__( 'Plugin Activation Error', 'post-my-contact-form-7' ),
			array(
				'response'  => 500,
				'back_link' => true,
			)
		);
	}

	/**
	 * Store activation error for admin notice.
	 *
	 * @since 5.3.0
	 * @param string $error_message The error message.
	 * @return void
	 */
	private static function store_activation_error( $error_message ) {
		$errors = get_site_transient( 'cf7_2_post_activation_errors' );
		if ( ! is_array( $errors ) ) {
			$errors = array();
		}
		$errors[] = $error_message;
		set_site_transient( 'cf7_2_post_activation_errors', $errors, 30 );
	}

	/**
	 * Deactivate this plugin.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private static function deactivate_plugin() {
		$plugin = plugin_basename( dirname( __DIR__ ) . '/cf7-2-post.php' );
		deactivate_plugins( $plugin );
	}

	/**
	 * Setup default plugin options.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private static function setup_default_options() {
		$default_options = array(
			'version'                   => CF7_2_POST_VERSION,
			'activation_time'           => current_time( 'timestamp' ),
			'flush_rewrite_rules_needed' => true,
		);

		foreach ( $default_options as $key => $value ) {
			if ( false === get_option( "cf7_2_post_{$key}" ) ) {
				add_option( "cf7_2_post_{$key}", $value );
			}
		}
	}

	/**
	 * Flush rewrite rules.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private static function flush_rewrite_rules() {
		// Set a flag to flush rewrite rules on next init.
		set_site_transient( 'cf7_2_post_flush_rewrite_rules', true, 300 );
	}

	/**
	 * Set activation flag.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	private static function set_activation_flag() {
		set_site_transient( 'cf7_2_post_activated', true, 300 );
	}

	/**
	 * Check if plugin was just activated.
	 *
	 * @since 5.3.0
	 * @return bool True if just activated.
	 */
	public static function was_just_activated() {
		return (bool) get_site_transient( 'cf7_2_post_activated' );
	}

	/**
	 * Clear activation flag.
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public static function clear_activation_flag() {
		delete_site_transient( 'cf7_2_post_activated' );
	}

	/**
	 * Get activation errors.
	 *
	 * @since 5.3.0
	 * @return array Array of activation errors.
	 */
	public static function get_activation_errors() {
		$errors = get_site_transient( 'cf7_2_post_activation_errors' );
		if ( ! is_array( $errors ) ) {
			$errors = array();
		}
		delete_site_transient( 'cf7_2_post_activation_errors' );
		return $errors;
	}
}