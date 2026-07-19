<?php
/**
 * The file setups the actions and filters requried to setup the CF7 Posts admin table page, in order to bring the form table admin page back into WP core territory.  The CF7 plugin uses a custom WP Post Table class that does not implement default core extensibility and functionality.
 *
 * Admin-facing side of the site.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/assets/cf7-admin-table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Include the admin table class CF7SG_WP_Post_Table
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/class-cf7sg-wp-post-table.php';
// reset the cf7 admin table.
$cf7_admin = CF7SG_WP_Post_Table::set_table();
if ( ! $cf7_admin->hooks() ) {
	add_action( 'admin_enqueue_scripts', array( $cf7_admin, 'enqueue_styles' ) );
	add_action( 'admin_enqueue_scripts', array( $cf7_admin, 'enqueue_script' ) );
	// modify the CF7 post type.
	add_action( 'init', array( $cf7_admin, 'modify_cf7_post_type' ), 20 );
	// cf7 sub-menu.
	add_action( 'admin_menu', array( $cf7_admin, 'add_cf7_sub_menu' ) );
	add_filter( 'custom_menu_order', array( $cf7_admin, 'change_cf7_submenu_order' ) );
	// modify the cf7 list table columns.
	add_filter( 'manage_wpcf7_contact_form_posts_columns', array( $cf7_admin, 'modify_cf7_list_columns' ), 1, 1 );
	add_action( 'manage_wpcf7_contact_form_posts_custom_column', array( $cf7_admin, 'populate_custom_column' ), 1, 2 );
	add_filter( 'post_row_actions', array( $cf7_admin, 'modify_cf7_list_row_actions' ), 1, 2 );
	// change the 'Add New' button link.
	add_action( 'admin_print_footer_scripts', array( $cf7_admin, 'change_add_new_button' ), 1 );
	// catch cf7 delete redirection.
	add_filter( 'wp_redirect', array( $cf7_admin, 'filter_cf7_redirect' ), 1, 2 );
	/** NB @since 4.1.8 edit edit link for form posts. */
	add_filter( 'get_edit_post_link', array( $cf7_admin, 'edit_form_link' ), 10, 2 );
	// add quick edit.
	// cf7-form shortcode.
	add_shortcode( 'cf7-form', array( $cf7_admin, 'shortcode' ) );
	add_shortcode( 'cf7form', array( $cf7_admin, 'shortcode' ) );
	/** NB @since 5.3.2 */
	add_action( 'admin_footer', array( $cf7_admin, 'update_form_highlight' ) );
}
