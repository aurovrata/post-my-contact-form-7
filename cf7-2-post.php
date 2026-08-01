<?php
/**
 * Plugin Name:       Post My CF7 Form
 * Plugin URI:        https://digital-tiff.in
 * Description:       Map Contact Form 7 forms to custom posts.
 * Version:           7.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurovrata V.
 * Author URI:        https://www.digital-tiff.in
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       post-my-contact-form-7
 * Domain Path:       /languages
 */

// Do not load directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cf7_2_post_plugin_data = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	)
);
define( 'CF7_2_POST_VERSION', $cf7_2_post_plugin_data['Version'] );
define( 'CF7_2_POST_MAJOR_UPDATE', true );

/**
 * Runs during plugin activation.
 */
function cf7_2_post_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post-activator.php';
	Cf7_2_Post_Activator::activate();
}

/**
 * Runs during plugin deactivation.
 */
function cf7_2_post_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post-deactivator.php';
	Cf7_2_Post_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'cf7_2_post_activate' );
register_deactivation_hook( __FILE__, 'cf7_2_post_deactivate' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function cf7_2_post_run() {
	$plugin = new Cf7_2_Post( CF7_2_POST_VERSION );
	$plugin->run();
}
add_action( 'plugins_loaded', 'cf7_2_post_run' );
