<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://syllogic.in
 * @since             1.0.0
 * @package           Cf7_2_Post
 *
 * @wordpress-plugin
 * Plugin Name:       Post My CF7 Form
 * Plugin URI:        http://wordpress.syllogic.in
 * Description:       This plugin enables the mapping of your CF7 forms to custom posts.
 * Version:           3.7.0
 * Author:            Aurovrata V.
 * Author URI:        http://syllogic.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-2-post
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'CF7_2_POST_VERSION', '3.7.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cf7-2-post-activator.php
 */
function activate_cf7_2_post() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post-activator.php';
	Cf7_2_Post_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cf7-2-post-deactivator.php
 */
function deactivate_cf7_2_post() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post-deactivator.php';
	Cf7_2_Post_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cf7_2_post' );
register_deactivation_hook( __FILE__, 'deactivate_cf7_2_post' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7-2-post.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cf7_2_post() {

	$plugin = new Cf7_2_Post(CF7_2_POST_VERSION);
	$plugin->run();

}
run_cf7_2_post();
