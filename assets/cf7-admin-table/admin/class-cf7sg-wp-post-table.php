<?php
/**
 * The admin-specific functionality of cf7 custom post table.
 *
 * @link       http://syllogic.in
 * @since      1.1.0
 *
 * @package    Cf7_Polylang
 * @subpackage Cf7_Polylang/admin
 * @author     Aurovrata V. <vrata@syllogic.in>
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Check if the class exists.
 */
if ( ! class_exists( 'CF7SG_WP_Post_Table' ) ) {
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
	class CF7SG_WP_Post_Table {
		/**
		 * A CF7 list table object.
		 *
		 * @since    1.1.0
		 * @access   private
		 * @var      CF7SG_WP_Post_Table    $singleton   cf7 admin list table object.
		 */
		private static $singleton;
		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $version    The current version of this plugin.
		 */
		private $version;
		/**
		 * A flag to monitor if hooks are in place.
		 *
		 * @since    1.1.0
		 * @access   private
		 * @var      boolean    $hooks_set   true if hooks are set.
		 */
		private $hooks_set;
		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {
			$this->hooks_set = false;
			$this->version   = '1.1'; // hardcoded and updated when this file changes.
		}
		/**
		 * Access/setup the singleton instance of this class.
		 *
		 * @since 1.0.0
		 */
		public static function set_table() {
			if ( null === self::$singleton ) {
				self::$singleton = new self();
			}
			return self::$singleton;
		}
		/**
		 * Flag hooks setup.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			if ( ! $this->hooks_set ) {
				$this->hooks_set = true;
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
			if ( 'wpcf7_contact_form' !== $screen->post_type ) {
				return;
			}

			switch ( $screen->base ) {
				case 'post':
					// for the future.
					break;
				case 'edit':
					wp_enqueue_style( 'cf7-post-table-css', plugin_dir_url( __FILE__ ) . 'css/cf7-admin-table.css', false, $this->version );
					break;
			}
		}
		/**
		 * Register scripts for admin page.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_script() {
			$screen = get_current_screen();
			if ( 'wpcf7_contact_form' !== $screen->post_type ) {
				return;
			}

			switch ( $screen->base ) {
				case 'post':
					// for the future.
					break;
				case 'edit':
					wp_enqueue_script( 'cf7-post-table-js', plugin_dir_url( __FILE__ ) . 'js/cf7-post-table.js', false, $this->version, true );
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
			if ( ! $this->is_cf7_admin_page() ) {
				return;
			}
			$url = admin_url( 'admin.php?page=wpcf7-new' );
			?>
		<script type='text/javascript'>
		(function( $ ) {
			'use strict';
			$(document).ready(function() {
				$('h1 > a.page-title-action').attr('href','<?php echo esc_url( $url ); ?>');
				$('h1 ~ a.page-title-action').attr('href','<?php echo esc_url( $url ); ?>');
			});
		})( jQuery );
		</script>
			<?php
		}
		/**
		 * Get form id for a given key
		 *
		 * @since 1.2.0
		 * @param      string $form_key   the unique key for qhich to get the id  .
		 * @return     string    form id     .
		 **/
		public static function form_id( $form_key ) {
			$form_id = 0;
			$forms   = get_posts(
				array(
					'post_type'     => 'wpcf7_contact_form',
					'post_name__in' => array( $form_key ),
				)
			);
			if ( ! empty( $forms ) ) {
				$form_id = $forms[0]->ID;
				wp_reset_postdata();
			}
			return $form_id;
		}
		/**
		 * Get a form key from its id
		 *
		 * @since 1.2.0
		 * @param      string $id     form id.
		 * @return      string    form post key.
		 **/
		public static function form_key( $id ) {
			$key  = null;
			$form = get_post( $id );
			if ( ! empty( $form ) ) {
				$key = $form->post_name;
			}
			return $key;
		}
		/**
		 *  Checks if this is the admin table list page
		 *
		 * @since 1.1.3
		 */
		public static function is_cf7_admin_page() {
			/** This is a core admin page request check, nonce validation handled by WP */
			if ( ! isset( $_GET['post_type'] ) || false === strpos( sanitize_key( $_GET['post_type'] ), WPCF7_ContactForm::post_type ) ) {
				return false;
			} else {
				$screen = get_current_screen();
				return ( ! empty( $screen ) && 'edit' === $screen->base && '' === $screen->action );
			}
		}
		/**
		 * Check if this is a cf7 edit page.
		 *
		 * @since    1.1.3
		 * @return    bool    true is this is the edit page
		 */
		public static function is_cf7_edit_page() {
			if ( ! isset( $_GET['page'] ) || false === strpos( sanitize_key( $_GET['page'] ), 'wpcf7' ) ) {
				return false;
			} else {
				if ( function_exists( 'get_current_screen' ) ) {
					$screen = get_current_screen(); // use screen option after intial basic check else it may throw fatal error.
					return ( isset( $screen ) && 'contact_page_wpcf7-new' === $screen->base || 'toplevel_page_wpcf7' === $screen->base );
				} else {
					return false;
				}
			}
		}
		/**
		 * Modify the regsitered cf7 post tppe
		 * THis function enables public capability and amind UI visibility for the cf7 post type. Hooked late on `init`
		 *
		 * @since 1.0.0
		 */
		public function modify_cf7_post_type() {
			if ( class_exists( 'WPCF7_ContactForm' ) && post_type_exists( WPCF7_ContactForm::post_type ) ) {
				global $wp_post_types;
				$wp_post_types[ WPCF7_ContactForm::post_type ]->public                           = true;
				$wp_post_types[ WPCF7_ContactForm::post_type ]->show_ui                          = true;
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->add_new                  = __( 'Add a New Form', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->add_new_item             = __( 'Add a New Form', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->edit_item                = __( 'Edit Form', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->new_item                 = __( 'New Form', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->view_item                = __( 'View Form', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->view_items               = __( 'View Forms', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->search_items             = __( 'Search Forms', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->not_found                = __( 'No forms found.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->not_found_in_trash       = __( 'No forms found in Bin.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->attributes               = __( 'Form Attributes', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->insert_into_item         = __( 'Insert into post', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->uploaded_to_this_item    = __( 'Uploaded to this post', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->filter_items_list        = __( 'Filter posts list', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->items_list_navigation    = __( 'Forms list navigation', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->items_list               = __( 'Forms list', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_published           = __( 'Form published.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_published_privately = __( 'Form published privately.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_reverted_to_draft   = __( 'Form reverted to draft.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_trashed             = __( 'Form binned.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_scheduled           = __( 'Form scheduled.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_updated             = __( 'Form updated.', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_link                = __( 'Form Link', 'post-my-contact-form-7' );
				$wp_post_types[ WPCF7_ContactForm::post_type ]->labels->item_link_description    = __( 'A link to a post.', 'post-my-contact-form-7' );
			}
		}

		/**
		 * Adds a new sub-menu
		 * Add a new sub-menu to the Contact main menu, as well as remove the current default
		 */
		public function add_cf7_sub_menu() {

			$hook = add_submenu_page(
				'wpcf7',
				__( 'Edit Contact Form', 'contact-form-7' ),
				__( 'Contact Forms', 'contact-form-7' ),
				'wpcf7_read_contact_forms',
				'edit.php?post_type=wpcf7_contact_form'
			);
			remove_submenu_page( 'wpcf7', 'wpcf7' );
		}

		/**
		 * Change the submenu order
		 *
		 * @since 1.0.0
		 * @param String $menu_ord menu order.
		 */
		public function change_cf7_submenu_order( $menu_ord ) {
			global $submenu;
			// Enable the next line to see all menu orders.
			if ( ! isset( $submenu['wpcf7'] ) ) {
				return $menu_ord;
			}
			if ( is_network_admin() ) {
				return $menu_ord;
			}
			$arr = array();
			foreach ( $submenu['wpcf7'] as $menu ) {
				switch ( $menu[2] ) {
					case 'cf7_post': // do nothing, we hide this submenu.
						$arr[] = $menu;
						break;
					case 'edit.php?post_type=wpcf7_contact_form':
						// push to the front.
						array_unshift( $arr, $menu );
						break;
					default:
						$arr[] = $menu;
						break;
				}
			}
			$submenu['wpcf7'] = $arr;
			return $menu_ord;
		}
		/**
		 * Modify cf7 post type list table columns
		 * Hooked on 'manage_{$post_type}_posts_columns', to remove the default columns
		 *
		 * @since 1.0.0
		 * @param      Array $columns     array of columns to display.
		 * @return     Array    array of columns to display.
		 */
		public function modify_cf7_list_columns( $columns ) {
			if ( isset( $columns['title'] ) ) {
				$columns['title'] = __( 'Form', 'contact-form-7' );
			}
			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}
			$columns['shortcode'] = 'Shortcode<br /><span class="cf7-help-tip"><a href="javascript:void();">What\'s this?</a><span class="cf7-short-info">Use this shortcode the same way you would use the contact-form-7 shortcode. (See the plugin page for more information )</span></span>';
			$columns['cf7_key']   = __( 'Form key', 'post-my-contact-form-7' );
			return $columns;
		}
		/**
		 * Populate custom columns in cf7 list table
		 * hooked on 'manage_{$post_type}_posts_custom_column'
		 *
		 * @since 1.0.0
		 * @param      String $column     column key.
		 * @param      Int    $post_id     row post id.
		 */
		public function populate_custom_column( $column, $post_id ) {
			$form = get_post( $post_id );
			switch ( $column ) {
				case 'title':
					break;
				case 'shortcode':
					echo "\n" . '<span class="shortcode cf7-2-post-shortcode"><input type="text"'
					. ' onfocus="this.select();" readonly="readonly"'
					. ' value="' . esc_attr( '[cf7form cf7key="' . esc_html( $form->post_name ) . '"]' ) . '"'
					. ' class="large-text code" /></span>';
					break;
				case 'cf7_key':
					$update = '';
					$errors = get_post_meta( $post_id, '_config_errors', true );
					if ( ! empty( $errors ) ) {
						$update = 'cf7-errors';
					}
					echo '<span class="cf7-form-key" data-update="' . esc_attr( $update ) . '">' . esc_html( $form->post_name ) . '</span>';
					break;
			}
		}
		/**
		 * Add a script to the admin table page to highlight form uddates.
		 * hooked to 'admin_footer'.
		 *
		 * @since 5.3.2
		 */
		public function update_form_highlight() {
			$screen = get_current_screen();
			if ( ! isset( $screen ) || 'wpcf7_contact_form' !== $screen->post_type ) {
				return;
			}
			switch ( $screen->base ) {
				case 'edit':
					/** NB @since 4.11.5 enable cf7 form errors to be notified */
					?>
		<script type="text/javascript">
		(function($){
			$(document).ready(function(){
			$('tbody#the-list tr').each(function(){
				var $tr = $(this);
				var update = $tr.find('.cf7-form-key').data('update');
				switch(update){
					case 'cf7-errors':
						$tr.find('a.row-title').addClass(update).after('<span class="cf7sg-popup display-none"><?php esc_html_e( 'CF7 plugin has found misconfiguration errors on this form.', 'post-my-contact-form-7' ); ?></span>').parent().css('position','relative');
					break;
				}
			});
			});
		})(jQuery);
		</script>
					<?php
					break;
			}
		}
		/**
		 * Change form edit links
		 *
		 * @since 1.4
		 * @param string $link url link.
		 * @param string $post_id post id.
		 * @return string link.
		 */
		public function edit_form_link( $link, $post_id ) {
			if ( ! is_admin() ) {
				return $link;
			}
			$post = get_post( $post_id );

			if ( 'wpcf7_contact_form' === $post->post_type ) {
				$link = admin_url( 'admin.php?page=wpcf7&action=edit&post=' . $post_id );
			}
			return $link;
		}
		/**
		 * Modify the quick action links in the contact table.
		 * Since this plugin replaces the default contact form list table
		 * for the more std WP table, we need to modify the quick links to match the default ones.
		 * This function is hooked on 'post_row_actions'
		 *
		 * @since    1.1.0
		 * @param Array   $actions  quick link actions.
		 * @param WP_Post $post the current row's post object.
		 */
		public function modify_cf7_list_row_actions( $actions, $post ) {
			// check for your post type.
			if ( 'trash' === $post->post_status ) {
				return array();
			}

			if ( 'wpcf7_contact_form' === $post->post_type ) {
				$form      = WPCF7_ContactForm::get_instance( $post->ID );
				$url       = admin_url( 'admin.php?page=wpcf7&post=' . absint( $form->id() ) );
				$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );
				$idx       = strpos( $actions['trash'], '_wpnonce=' ) + 9;
				$nonce     = substr( $actions['trash'], $idx, strpos( $actions['trash'], '"', $idx ) - $idx );

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
		 *
		 * @since 1.1.3
		 * @param string $location a fully formed url.
		 * @param int    $status the html redirect status code.
		 * @return String location.
		 */
		public function filter_cf7_redirect( $location, $status ) {
			if ( self::is_cf7_admin_page() || self::is_cf7_edit_page() ) {
				if ( 'delete' === wpcf7_current_action() ) {
						global $post_ID;
						do_action( 'wpcf7_post_delete', $post_ID );

						return admin_url( 'edit.php?post_type=wpcf7_contact_form' );
				}
			}
			return $location;
		}

		/**
		 * Fuction for cf7-form Shortcode handler
		 *
		 * @since 1.0.0
		 * @param      Array $atts     array of attributes.
		 * @return     string    $p2     .
		 **/
		public function shortcode( $atts ) {
			$a = array_merge(
				array(
					'cf7key' => '',
				),
				$atts
			);
			if ( empty( $a['cf7key'] ) ) {
				return '<em>' . __( 'cf7-form shortcode missing key', 'post-my-contact-form-7' ) . '</em>';
			}
			// else get the post ID.
			$form = get_posts(
				array(
					'post_type' => 'wpcf7_contact_form',
					'name'      => $a['cf7key'],
				)
			);
			if ( ! empty( $form ) ) {
				$id = apply_filters( 'cf7_form_shortcode_form_id', $form[0]->ID, $atts );

				wp_reset_postdata();
				$attributes = '';
				foreach ( $a as $key => $value ) {
					$attributes .= ' ' . $key . '="' . $value . '"';
				}
				return do_shortcode( '[contact-form-7 id="' . $id . '"' . $attributes . ']' );
			} else {
				return '<em>' . __( 'cf7-form shortcode key error, unable to find form', 'post-my-contact-form-7' ) . '</em>';
			}
		}
	}//end class
	if ( ! function_exists( 'c2p_get_form_id' ) ) {
		/**
		 * Get form post id from slug.
		 *
		 * @since 1.0.0
		 * @param string $cf7_key slug.
		 * @return string id.
		 */
		function c2p_get_form_id( $cf7_key ) {
			return CF7SG_WP_Post_Table::form_id( $cf7_key );
		}
	}
	if ( ! function_exists( 'c2p_get_form_key' ) ) {
		/**
		 * Get form slug from id
		 *
		 * @since 1.0.0
		 * @param string $cf7_id id.
		 * @return string slug.
		 */
		function c2p_get_form_key( $cf7_id ) {
			return CF7SG_WP_Post_Table::form_key( $cf7_id );
		}
	}
}
