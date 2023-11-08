<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * The screen ID of our custom admin page.
	 *
	 * @since 3.0.0
	 * @access private
	 * @var string   the id of the screen, which is dependent on the CF7 main menu.
	 */
	private static $map_screen_id;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();
	}

	/**
	 * Deactivate this plugin if CF7 plugin is deactivated
	 * Hooks on action 'admin_init'
	 *
	 * @since 1.1.0
	 */
	public function check_plugin_dependency() {
		if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			deactivate_plugins( 'post-my-contact-form-7/cf7-2-post.php' );
			wp_die( '<p><strong>Post My CF7 Form</strong> requires <strong>Contact Form 7</strong> plugin, and has therefore been deactivated!</p><a href="' . esc_url( network_admin_url( 'plugins.php' ) ) . '">Return to Plugins</a></a>' );
		}
		/** NB @since 5.0.0 hook the smart grid form saving action to fix double save_post hook call */
		if ( is_plugin_active( 'cf7-grid-layout/cf7-grid-layout.php' ) ) {
			add_action( 'cf7sg_save_post', array( $this, 'save_post_mapping' ), 10 );
		} else {
			add_action( 'save_post_wpcf7_contact_form', array( $this, 'save_post_mapping' ), 10 );
		}
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// contact post table list.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'assets/cf7-admin-table/admin/class-cf7sg-wp-post-table.php';
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook page handle.
	 */
	public function enqueue_styles( $hook ) {
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( 'toplevel_page_wpcf7' == $hook ||
		( WPCF7_ContactForm::post_type === $screen->post_type && 'post' === $screen->base ) ) {

			$plugin_dir = plugin_dir_url( __DIR__ );
			wp_enqueue_style( 'cf7-2-post-panel-css', plugin_dir_url( __FILE__ ) . 'css/mapping-panel.css', $this->version, 'all' );
			wp_enqueue_style( 'hybrid-select-css', $plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-toggles-css', $plugin_dir . 'assets/jquery-toggles/css/toggles.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-toggles-light-css', $plugin_dir . 'assets/jquery-toggles/css/themes/toggles-light.css', array( 'jquery-toggles-css' ), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-2-post-mapping.css', array( 'dashicons' ), $this->version, 'all' );
		}
		if ( WPCF7_ContactForm::post_type === $screen->post_type && 'edit' === $screen->base ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7-table.css', null, $this->version, 'all' );
		}//
		$factory = c2p_get_factory();
		if ( false != $factory->is_mapped_post_types( $screen->post_type, 'factory' ) ) {
			switch ( $screen->base ) {
				case 'post':
					wp_enqueue_style( 'cf72-custompost-css', plugin_dir_url( __FILE__ ) . 'css/cf72-custompost.css', array(), $this->version, 'all' );
					break;
			}
		}
	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook page handle.
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return;
		}

		$screen = get_current_screen();
		switch ( true ) {
			case ( 'toplevel_page_wpcf7' === $hook || ( WPCF7_ContactForm::post_type === $screen->post_type && 'post' === $screen->base ) ): // form editor.
				$plugin_dir = plugin_dir_url( __DIR__ );
				/** NB @since 5.5.1 fix tag name scanning.*/
				$tags = array();
				if ( class_exists( 'WPCF7_FormTagsManager' ) ) {
					$form_tags_manager = WPCF7_FormTagsManager::get_instance();
					$tags              = $form_tags_manager->collect_tag_types(
						array(
							'feature' => 'name-attr',
						)
					);
					$tags              = array_filter(
						$tags,
						function( $t ) {
							return ! strpos( $t, '*' );
						}
					);
				}

				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/c2p-edit-panel.js', array( 'jquery', 'postbox' ), $this->version, true );
				wp_enqueue_script( 'jquery-toggles', $plugin_dir . 'assets/jquery-toggles/toggles.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'jquery-clibboard', $plugin_dir . 'assets/clipboard/clipboard.min.js', array( 'jquery' ), $this->version, true );
				wp_localize_script(
					$this->plugin_name,
					'c2pLocal',
					array(
						'warning'    => __( 'Warning: Field already selected!', 'post-my-contact-form-7' ),
						'copy'       => __( 'Click to copy!', 'post-my-contact-form-7' ),
						'paste'      => __( 'Paste helper code into your theme functions.php file.', 'post-my-contact-form-7' ),
						'draft'      => __( 'draft', 'post-my-contact-form-7' ),
						'live'       => __( 'live', 'post-my-contact-form-7' ),
						'warn'       => __( 'CF7 2 POST WARNING: Your form is live! Changing its fields and mapping may create inconsistent data entries.' ),
						'wpcf7_tags' => $tags,
					)
				);
				wp_enqueue_script( 'hybrid-select', $plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.js', null, $this->version, true );
				break;
			case ( WPCF7_ContactForm::post_type === $screen->post_type && 'edit' === $screen->base ): // form post table.
				$plugin_dir = plugin_dir_url( __DIR__ );
				wp_enqueue_script( 'jquery-clibboard', $plugin_dir . 'assets/clipboard/clipboard.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'quickedit-c2p-js', $plugin_dir . 'admin/js/cf7-2-post-quick-edit.js', array( 'jquery-clibboard' ), $this->version, true );
				break;
		}
		$factory = c2p_get_factory();
		if ( false != $factory->is_mapped_post_types( $screen->post_type ) ) {
			switch ( $screen->base ) {
				case 'edit':
					wp_enqueue_script( 'cf72custompost-quick-edit-js', plugin_dir_url( __FILE__ ) . 'js/cf7-2-custom-post-quick-edit.js', array( 'jquery' ), $this->version, true );
					break;
			}
		}

	}
	/**
	 * Modify the regsitered cf7 post type
	 * THis function enables public capability and amind UI visibility for the cf7 post type. Hooked late on `init`
	 *
	 * @since 1.0.0
	 */
	public function modify_cf7_post_type() {
		if ( class_exists( 'WPCF7_ContactForm' ) && post_type_exists( WPCF7_ContactForm::post_type ) ) {
			global $wp_post_types;
			$wp_post_types[ WPCF7_ContactForm::post_type ]->supports[] = 'custom-fields';
		}
	}

	/**
	 * Add helper metabox to edito page.
	 *
	 * @since 5.0.0
	 */
	public function add_helper_metabox() {
		add_meta_box(
			'helper', // Meta box ID.
			__( 'CF7 2 Post:<br/> Actions &amp; Filters', 'post-my-contact-form-7' ), // Meta box Title.
			array( $this, 'show_helper_metabox' ), // Callback defining the plugin's innards.
			'wpcf7_contact_form', // Screen to which to add the meta box.
			'side' // Context.
		);
	}
	/**
	 * Display helper metabox
	 * Callback fn above.
	 *
	 * @since 3.0.0
	 */
	public function show_helper_metabox() {
		$closed = ' closed';
		include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-helper-metabox.php';
	}
	/**
	 * Display metafield metabox
	 * Callback fn above.
	 *
	 * @since 3.0.0
	 */
	public function show_field_metabox() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-field-metabox.php';
	}
	/**
	 * Display taxonomy metabox
	 * Callback fn above.
	 *
	 * @since 3.0.0
	 */
	public function show_taxonomy_metabox() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-taxonomy-metabox.php';
	}
	/**
	 * Add custom column to custom posts mapped/created by plugin.
	 * Hooked on 'manage_{$post_type}_posts_columns'
	 *
	 * @since 3.4.0
	 * @param      Array $columns       IDs of existing columns.
	 * @return     Array    $columns       IDs of table columns.
	 */
	public function modify_cf72post_columns( $columns ) {
		$capability = apply_filters( 'cf7_2_post_view_submit_capability', 'manage_options' );
		if ( current_user_can( $capability ) ) {
			// $columns['mapped_post']= __( 'Post Type', 'post-my-contact-form-7' ).
			$columns['cf7_2_post'] = __( 'Submitted', 'post-my-contact-form-7' );
		}
		return $columns;
	}
	/**
	 * Modify cf7 post type list table columns
	 * Hooked on 'manage_{$post_type}_posts_columns', to remove the default columns
	 *
	 * @since 1.0.0
	 * @param      Array $columns       IDs of existing columns.
	 * @return     Array    $columns       IDs of table columns.
	 */
	public function modify_cf7_list_columns( $columns ) {
		$capability = apply_filters( 'cf7_2_post_mapping_capability', 'manage_options' );
		if ( current_user_can( $capability ) ) {
			// $columns['mapped_post']= __( 'Post Type', 'post-my-contact-form-7' );
			$columns['map_cf7_2_post'] = __( 'Form to post', 'post-my-contact-form-7' );
		}
		return $columns;
	}
	/**
	 * Populate custom columns in cf7 list table
	 *
	 * @since 1.0.0
	 * @param      String $column       ID of current column to populate.
	 * @param      int    $post_id    CF7 post ID for the row to fill.
	 */
	public function populate_custom_column( $column, $post_id ) {

		switch ( $column ) {
			case 'map_cf7_2_post':
				$page = 'admin.php?page=wpcf7&';
				if ( defined( 'CF7_GRID_VERSION' ) ) {
					$page = 'post.php?action=edit&';
				}
				$url = admin_url( "{$page}post={$post_id}&active-tab=" . get_option( '_c2p_active_tab', 0 ) );

				$post_type   = get_post_meta( $post_id, '_cf7_2_post-type', true );
				$post_source = get_post_meta( $post_id, '_cf7_2_post-type_source', true );
				$status      = get_post_meta( $post_id, '_cf7_2_post-map', true );
				if ( $post_type ) {
					echo '<a class="cf7-2-post-map-link" href="' . esc_url( $url ) . '">' . esc_html( 'draft' === $status ? __( 'Draft:', 'post-my-contact-form-7' ) : __( 'Mapped:', 'post-my-contact-form-7' ) ) . esc_html( $post_type ) . '</a>';
					echo '<input type="hidden" class="cf7-2-post-status" value="' . esc_attr( $status ) . '"/>';
					echo '<input type="hidden" class="cf7-2-post-type c2p-' . esc_attr( $post_source ) . '" value="' . esc_attr( $post_type ) . '"/>';
				} elseif ( $post_source ) {
					echo '<a class="cf7-2-post-map-link" href="' . esc_url( $url ) . '">' . esc_html( 'draft' === $status ? __( 'Draft w/ ', 'post-my-contact-form-7' ) : __( 'Mapped w/ ', 'post-my-contact-form-7' ) ) . esc_html( $post_type ) . '</a>';
				} else {
					echo '<a class="cf7-2-post-map-link" href="' . esc_url( $url ) . '">' . esc_html( __( 'Create new', 'post-my-contact-form-7' ) ) . '</a>';
				}
				break;
			case 'cf7_2_post':
				$capability = apply_filters( 'cf7_2_post_view_submit_capability', 'manage_options' );
				if ( ! current_user_can( $capability, $post_id ) ) {
					return;
				}
				$submit = get_post_meta( $post_id, '_cf7_2_post_form_submitted', true );
				echo '<span class="cf7-2-post-submit">' . esc_html( $submit ) . '</span>';
				break;
		}
	}
	/**
	 * Function to populate the quick edit form
	 * Hooked on 'quick_edit_custom_box' action
	 *
	 * @since 1.0.0
	 * @param      string $column_name     column name to add edit field.
	 * @param      string $post_type     post type being displayed.
	 * @return     string    echos the html fields.
	 **/
	public function quick_edit_box( $column_name, $post_type ) {
		static $print_nonce = true;
		if ( $print_nonce ) {
			$print_nonce = false;
			wp_nonce_field( 'cf7_2_post_quick_edit', 'c2p_nonce' );
		}
		switch ( $post_type ) {
			case 'wpcf7_contact_form':
				$capability = apply_filters( 'cf7_2_post_mapping_capability', 'manage_options' );
				if ( ! current_user_can( $capability ) ) {
					return;
				}
				switch ( $column_name ) {
					case 'map_cf7_2_post':
						include_once plugin_dir_path( __FILE__ ) . 'partials/c2p-quick-edit.php';
						break;
				}
				break;
			default:
				switch ( $column_name ) {
					case 'cf7_2_post':
						$capability = apply_filters( 'cf7_2_post_mapping_capability', 'manage_options' );
						if ( ! current_user_can( $capability ) ) {
							  return;
						}
						include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-custom-post-quick-edit.php';
						break;
				}
				break;
		}
	}
	/**
	 * Save quick edits.
	 *
	 * @since 5.3.0
	 * @param string $post_id post id.
	 */
	public function save_quick_edit( $post_id ) {
		if ( ! isset( $_POST['c2p_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['c2p_nonce'] ), 'c2p_quickedit_nonce' ) ) {
			return;
		}

		$capability = apply_filters( 'cf7_2_post_mapping_capability', 'manage_options' );
		if ( ! current_user_can( $capability ) ) {
			return;
		}
		if ( isset( $_POST['delete_c2p_map'] ) && $_POST['delete_c2p_map'] == $post_id ) {
			$factory = c2p_get_factory();
			if ( ! $factory->is_filter( $post_id ) ) { /** NB @since 5.4.3 */
				$mapper = $factory->get_post_mapper( $post_id );
				$mapper->delete_mapping();
			} else {
				delete_post_meta( $post_id, '_cf7_2_post-map' );
				delete_post_meta( $post_id, '_cf7_2_post-type_source' );
			}
		}
	}
	/**
	 * Saves Quick-edits changes
	 * Hooked to save_post_{$post_type}
	 *
	 * @since 3.4.0
	 * @param string    $post_id     post ID.
	 * @param WP_Object $post     post object.
	 **/
	public function save_quick_custompost( $post_id, $post ) {
		if ( ! isset( $_POST['c2p_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['c2p_nonce'] ), 'c2p_quickedit_nonce' ) ) {
			return;
		}
		if ( isset( $_POST['cf7_2_post_submit'] ) ) {
			update_post_meta( $post_id, '_cf7_2_post_form_submitted', 'yes' );
		} else {
			update_post_meta( $post_id, '_cf7_2_post_form_submitted', 'no' );
		}
	}
	/**
	 * Save draft with data submission from admin form.
	 * Hooked on save_post_wpcf7_contact_form
	 *
	 * @since 5.0.0
	 * @param string $post_id post ID.
	 */
	public function save_post_mapping( $post_id ) {
		// wpg_debug($_POST, "save post ").
		if ( ! isset( $_POST['cf7_2_post_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cf7_2_post_nonce'] ), 'cf7_2_post_mapping' ) ) {
			return;
		}
		if ( isset( $_POST['c2p_active_tab'] ) ) {
			update_option( '_c2p_active_tab', sanitize_key( $_POST['c2p_active_tab'] ) );
		}
		// check if any changes on the form.
		if ( isset( $_POST['mapped_post_type_source'] ) ) {
			switch ( sanitize_key( $_POST['mapped_post_type_source'] ) ) {
				case 'system':
				case 'factory':
					if ( ( isset( $_POST['mapped_post_default'] ) && sanitize_key( $_POST['mapped_post_default'] ) ) ||
					( isset( $_POST['c2p_mapping_changes'] ) && sanitize_key( $_POST['c2p_mapping_changes'] ) ) ) {
						// wpg_debug('saving mapping....').
						$factory = c2p_get_factory();
						$factory->save( $post_id );
					}
					break;
				case 'filter':
					if ( isset( $_POST['mapped_post_map'] ) ) {
						update_post_meta( $post_id, '_cf7_2_post-map', sanitize_key( $_POST['mapped_post_map'] ) );
					}
					break;
			}
		}
	}
	/**
	 * Disables browser page caching for forms which are mapped to a post.
	 * Hooked on 'wp_head' in fn load_cf7_script.
	 *
	 * @since 3.0.0
	 */
	public function disable_browser_page_cache() {
		$screen = get_current_screen();
		if ( self::$map_screen_id !== $screen->id ) {
			return;
		}
		?>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />
		<?php
	}

	/**
	 * Loads the custom posts created into the dashboard.
	 *
	 * @since 1.0.0
	 */
	public function register_dynamic_posts() {
		$factory = c2p_get_factory();
		$factory->register_cf7_post_maps();
	}


	/**
	 * Delete existing fields for a given cf7 form, as well as all post data
	 * This funciton is hooked on 'wpcf7_post_delete', a filter created by hooking on the cf7 plugin 'wp_redirect' hook in the 'cf7-post-admin-table.php' file
	 *
	 * @since 1.0.0
	 * @param      int $cf7_post_id    The ID of the cf7 form to be deleted .
	 **/
	public function delete_cf7_post( $cf7_post_id ) {
		$factory = c2p_get_factory();
		if ( $factory->is_mapped( $cf7_post_id ) && ! $factory->is_filter( $cf7_post_id ) ) {
			// TODO load settings to allow users to delete all submitted form post data when deleting a mapping.
			$mapper           = $factory->get_post_mapper( $cf7_post_id );
			$delete_all_posts = apply_filters( 'cf7_2_post_delete_submitted_posts', false, $mapper->get( 'type' ), $mapper->cf7_key );
			$mapper->delete_mapping( $delete_all_posts );
		}
	}
	/**
	 * Adds a 'save' button shortcode to cf7 forms
	 * hooked to 'wpcf7_admin_init'
	 *
	 * @since 2.0.0
	 **/
	public function cf7_shortcode_tags() {
		if ( isset( $_GET['post'] ) ) {
			$factory = c2p_get_factory();
			if ( ! $factory->is_mapped( sanitize_key( $_GET['post'] ) ) ) {
				return;
			}
			// only display save button for mapped forms.
			if ( class_exists( 'WPCF7_TagGenerator' ) ) {
				$tag_generator = WPCF7_TagGenerator::get_instance();
				$tag_generator->add(
					'save', // tag id.
					__( 'save', 'post-my-contact-form-7' ), // tag button label.
					array( $this, 'save_tag_generator' ), // callback.
					array( 'nameless' => 1 ) // option name less = true, ie no name for this tag.
				);
			}
		}
	}

	/**
	 * Save button tag screen displayt.
	 *
	 * This function is called by cf7 plugin, and is registered with a hooked function above
	 *
	 * @since 1.0.0
	 * @param WPCF7_ContactForm $contact_form the cf7 form object.
	 * @param array             $args arguments for this form.
	 */
	public function save_tag_generator( $contact_form, $args = '' ) {
		$args = wp_parse_args( $args, array() );
		include plugin_dir_path( __FILE__ ) . '/partials/cf7-tag-display.php';
	}
	/**
	 * Inject script into footer.
	 *
	 * @since 1.0.0
	 */
	public function inject_footer_script() {
		include plugin_dir_path( __FILE__ ) . '/partials/cf72post-footer-script.php';
	}
	/**
	 * Function to add a metabox
	 * Hooked to 'cf72post_register_mapped_post'
	 *
	 * @since 3.3.0
	 * @param String  $post_type post type being mapped to.
	 * @param Boolean $system   true if form is mapped to an existing post, false if it is being registered by this plugin.
	 * @param String  $cf7_key   the form key value which is being mapped to the post type.
	 * @param String  $pid   the form post ID value which is being mapped to the post type.
	 * @param Boolean $is_duplicate true if this post type was previously regsitered.
	 */
	public function cf72post_metabox( $post_type, $system, $cf7_key, $pid, $is_duplicate ) {
		if ( ! $is_duplicate ) {
			add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_cf72post_metabox' ) );
			add_action( 'save_post_' . $post_type, array( $this, 'save_cf72post_metabox' ) );
			// enqueue the required script.
			add_action(
				'admin_enqueue_scripts',
				function( $hook ) use ( $post_type ) {
					if ( 'post.php' === $hook ) {
						$screen = get_current_screen();
						if ( $post_type === $screen->post_type ) {
							wp_enqueue_script( "{$this->plugin_name}-{$post_type}-js", plugin_dir_url( __FILE__ ) . 'js/c2p-mapped-post-edit.js', array( 'jquery' ), $this->version, true );
						}
					}
				}
			);
		}
	}
	/**
	 * Function to add metaboxes to enable the submitted flag to be reset.
	 * Callback function from action 'add_meta_boxes_'.$post_type above.
	 *
	 * @since 3.3.0
	 * @param WP_Post $post post object.
	 */
	public function add_cf72post_metabox( $post ) {
		add_meta_box(
			'cf72post_submitted', // Meta box ID.
			__( 'Submitted form', 'post-my-contact-form-7' ), // Meta box Title.
			array( $this, 'display_cf72post_metabox' ), // Callback defining the plugin's innards.
			$post->post_type, // Screen to which to add the meta box.
			'side' // Context.
		);
	}
	/**
	 * Display a metabox to enable the submitted flag to be reset.
	 * Callback function from action add_meta_box() function above.
	 *
	 * @since 3.3.0
	 * @param WP_Post $post post object.
	 */
	public function display_cf72post_metabox( $post ) {
		$submitted = get_post_meta( $post->ID, '_cf7_2_post_form_submitted', true );
		$checked   = ' disabled';
		$hidden    = ' disabled';
		// wpg_debug("post id: {$post->ID}, s $submitted").
		switch ( $submitted ) {
			case 'yes':
				$checked = ' checked';
				$hidden  = '';
				break;
			case 'no':
				$checked = '';
				$hidden  = '';
				break;
		}
		?>
	<span>Form:&nbsp;</span>
	<input class="cf72post-submitted" type="checkbox" value="submitted" <?php echo esc_attr( $checked ); ?>/>
	<label for="cf7_2_post_submitted">submitted.</label>
	<p><em>Uncheck to flag this submission as un-submitted.</em> This will reload this post into the form when the its author logs in again.</p>
		<?php wp_nonce_field( "cf72post_metabox_{$post->ID}", '_cf72post_metabox_nonce' ); ?>
	<input type="hidden" id="cf72post-submitted-hidden" name="cf7_2_post_submitted" value="<?php echo esc_attr( $submitted ); ?>" <?php echo esc_attr( $hidden ); ?>/>
		<?php
	}
	/**
	 * Allows saved posts to be toggled as submitted or not in order to reload a post into a form.
	 * Hooked on 'save_post_{$post_type}' above in method cf72post_metabox().
	 *
	 * @since 3.3.0
	 * @param string $post_id post id.
	 */
	public function save_cf72post_metabox( $post_id ) {
		if ( ! isset( $_POST['_cf72post_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_cf72post_metabox_nonce'] ), "cf72post_metabox_{$post_id}" ) ) {
			return;
		}
		if ( isset( $_POST['cf7_2_post_submitted'] ) ) {
			$value = sanitize_key( $_POST['cf7_2_post_submitted'] );
			update_post_meta( $post_id, '_cf7_2_post_form_submitted', $value ); // form is in saved mode.
		}
	}
	/**
	 * Function to add meta box to custom post mapped to forms.
	 * Hooked to action 'add_meta_boxes_'. $post_type
	 *
	 * @since 3.4.0
	 * @param WP_Post $post post object.
	 */
	public function custom_post_metabox( $post ) {
		// meta-fields.
		add_meta_box(
			'cf72post', // Meta box ID.
			__( 'Contact Form 7 fields', 'post-my-contact-form-7' ), // Meta box Title.
			array( $this, 'show_custom_post_metabox' ), // Callback defining the plugin's innards.
			$post->post_type, // Screen to which to add the meta box.
			'normal' // Context.
		);
	}
	/**
	 * Display custom post metabox for admin edit map
	 * Called by add_meta_box();
	 *
	 * @since 3.4.0
	 * @param WP_Post $post post object.
	 */
	public function show_custom_post_metabox( $post ) {
		$path        = apply_filters( 'cf7_2_post_mapped_post_metabox', '', $post->post_type );
		$factory     = c2p_get_factory();
		$cf7_post_id = $factory->is_mapped_post_types( $post->post_type, 'factory' );
		if ( false == $cf7_post_id ) {
			echo '<em>This post is not mapped to a cf7 form</em>';
			return;
		}
		$mapper        = $factory->get_post_mapper( $cf7_post_id );
		$mapped_fields = $mapper->get_mapped_meta_fields();
		if ( ! empty( $path ) && file_exists( $path ) ) {
			include $path;
		} else {
			include plugin_dir_path( __FILE__ ) . '/partials/cf7-2-custom-post-metabox.php';
		}
	}
	/**
	 * Add save draft message to cf7 messages.
	 * Hooked to 'wpcf7_messages', see file contact-form-7/includes/contact-form-template.php fn messages().
	 *
	 * @since 2.6.0
	 * @param array $messages array of messages to filter.
	 * @return array array of cf7 messages.
	 */
	public function draft_message( $messages ) {
		$messages['draft_saved'] = array(
			'description'
				=> __( 'Draft form was saved successfully', 'post-my-contact-form-7' ),
			'default'
				=> __( 'A draft of this form has been saved, you may complete and submit the form at a later time.', 'post-my-contact-form-7' ),
		);
		return $messages;
	}
	/**
	 * Set up email tags
	 * hooked on cf7 filter 'wpcf7_collect_mail_tags'
	 *
	 * @since 4.1.0
	 * @param      Array $mailtags     tag-name.
	 * @return     string    $p2     .
	 **/
	public function email_tags( $mailtags ) {
		// wpg_debug($mailtags, 'mail tags ').
		$cf7_form    = WPCF7_ContactForm::get_current();
		$cf7_post_id = $cf7_form->id();
		// is this form mapped yet?
		$factory = c2p_get_factory();
		if ( $factory->is_mapped( $cf7_post_id ) ) {
			$mailtags[] = 'cf7_2_post-edit';
			$mailtags[] = 'cf7_2_post-permalink';
		}
		return $mailtags;
	}
	/**
	 * Add mapping panel to the cf7 post editor to redirect to pages.
	 * hooked to 'wpcf7_editor_panels'
	 *
	 * @since 5.0.0
	 * @param Array $panels array of panels presented as tabs in the editor, $id => array( 'title' => $panel_title, 'callback' => $callback_function).  The $callback_function must be a valid function to echo the panel html script.
	 */
	public function add_mapping_panel( $panels ) {
		$contact_form = WPCF7_ContactForm::get_current();

		$panels['cf7-2-post'] = array(
			'title'    => __( 'Form to post', 'post-my-contact-form-7' ),
			'callback' => array( $this, 'display_amdin_panel' ),
		);
		return $panels;
	}
	/**
	 * Callback fn to display mapping tab in cf7 editor page.
	 *
	 * @since 5.0.0
	 */
	public function display_amdin_panel() {
		$cf7_post_id = -1;// for new forms.
		$cf7_key     = null;
		$factory     = c2p_get_factory();
		$is_filter   = false;
		if ( isset( $_GET['post'] ) ) {
			$cf7_post_id = sanitize_key( $_GET['post'] );
			$cf7_key     = c2p_get_form_key( $cf7_post_id );
			$is_filter   = ( $factory->is_filter( $cf7_post_id ) || apply_filters( 'cf7_2_post_save_with_filter', false, $cf7_key ) );
		}

		if ( $is_filter ) {
			update_post_meta( $cf7_post_id, '_cf7_2_post-type_source', 'filter' );
			$status = get_post_meta( $cf7_post_id, '_cf7_2_post-map', true );
			if ( ! $status ) {
				$status = 'publish';
			}
			include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-with-filter-admin-panel-display.php';
		} else {
			$post_mapper = $factory->get_post_mapper( $cf7_post_id );
			include_once plugin_dir_path( __FILE__ ) . 'partials/cf7-2-post-admin-panel-display.php';
		}
	}
	/**
	 * Find the panel index for this plugin on the cf7 editor.
	 *
	 * @since 5.0.0
	 */
	public function set_c2p_panel_tab() {
		if ( get_option( '_c2p_active_tab', 0 ) ) {
			return;
		}
		global $wp_filter;
		$tab = 3; // zero based.
		foreach ( $wp_filter['wpcf7_editor_panels']->callbacks as $idx => $cb ) {
			foreach ( $cb as $key => $val ) {
				$tab++;
				if ( strpos( $key, 'add_mapping_panel' ) !== false ) {
					break 2;
				}
			}
		}
		update_option( '_c2p_active_tab', $tab );
	}
	/**
	 * Listen for plugin updates and warn users for major updates.
	 * Hooked on 'in_plugin_update_message-{$file}'.
	 *
	 * @since 5.0.0
	 * @param array  $plugin_data An array of plugin metadata.
	 * @param object $response An object of metadata about the available plugin update.
	 */
	public function major_update_warning( $plugin_data, $response ) {
		$major = explode( '.', $this->version );
		if ( ! is_array( $major ) ) {
			return; // nothing to compare.
		}
		$major     = $major[0];
		$major_new = '';
		if ( property_exists( $response, 'new_version' ) && isset( $response->new_version ) ) {
			$major_new = explode( '.', $response->new_version );
		}
		if ( ! is_array( $major_new ) ) {
			return; // nothing to compare.
		}
		$major_new = $major_new[0];
		if ( version_compare( $major, $major_new, '<' ) ) { // this is a major update.
			include plugin_dir_path( __FILE__ ) . '/partials/cf7-2-post-update-warning.php';
		}
	}
}
