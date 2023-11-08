<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.we2ours2.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/public
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->not_form_page = true;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_scripts() {
		$plugin_dir = plugin_dir_url( __DIR__ );
		wp_register_script( $this->plugin_name . '-save', $plugin_dir . 'public/js/cf7-2-post-save-draft.js', array( 'jquery' ), $this->version, true );
		wp_register_script( $this->plugin_name . '-load', $plugin_dir . 'public/js/cf7-2-post-public.js', array( 'jquery' ), $this->version, true );
		wp_register_script( 'hybriddd-js', $plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.js', null, $this->version, true );
	}
	/**
	 * Register style
	 *
	 * @since 5.2.2
	 */
	public function register_styles() {
		$plugin_dir = plugin_dir_url( __DIR__ );
		wp_register_style( $this->plugin_name . '-css', $plugin_dir . 'public/css/cf7-2-post-styling.css', null, $this->version );
		wp_register_style( 'hybriddd-style', $plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.css', null, $this->version );

	}
	/**
	 * Saves a cf7 form submission to its mapped post
	 * Hooks 'wpcf7_before_send_mail' just after all validation is done
	 *
	 * @since 1.0.0
	 * @param  WPCF7_Contact_Form $cf7_form  cf7 form object.
	 */
	public function save_cf7_2_post( $cf7_form ) {

		// load the form factory.
		$cf7_post_id = $cf7_form->id();
		// is this form mapped yet?
		$factory = c2p_get_factory();
		if ( $factory->is_live( $cf7_post_id ) ) {
			// the curent submission object from cf7 plugin.
			$submission = WPCF7_Submission::get_instance();

			if ( $factory->is_filter( $cf7_post_id ) ) { // backward compatibility.
				/**
				* Action to by-pass the form submission process altogether.
			 *
				* @since v1.3.0
				* @param string $key unique form key.
				* @param array $data array of submitted key=>value pairs.
				* @param array $file array of submitted files if any.
				*/
				do_action( 'cf7_2_post_save_submission', c2p_get_form_key( $cf7_post_id ), $submission->get_posted_data(), $submission->uploaded_files() );
				return;
			}
			$mapper  = $factory->get_post_mapper( $cf7_post_id );
			$post_id = $mapper->save_form_2_post( $submission );
			self::setup_mailtags_for_mapped_post( $post_id ); /** NB @since 5.4.3 */
		}
		return $cf7_form;
	}
	/**
	 * Handle mail tags filters
	 *
	 * @since 4.1.0 handle post link mail tag.
	 * NOTE: the post_Id could be either a submitted form or a draft save.
	 * either way, the mail tag filter will only fire for submitted forms.
	 * @param String $post_id post ID.
	 */
	public static function setup_mailtags_for_mapped_post( $post_id ) {
		add_filter(
			'wpcf7_special_mail_tags',
			function( $value, $tag, $html ) use ( $post_id ) {
				switch ( $tag ) {
					case 'cf7_2_post-edit':
						$value = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
						break;
					case 'cf7_2_post-permalink':
						$value = get_permalink( $post_id );
						break;
				}
				return $value;
			},
			10,
			3
		);
	}
	/**
	 * Function to skip mail if this is a draft form being sent.
	 * Hooked on 'wpcf7_skip_mail'.  Skip mail also stops form clearance on being saved.
	 *
	 * @since 2.0.0
	 * @param      boolean $skip_mail     boolean flag.
	 * @return     boolean    true to skip mails if this is adraft form being saved .
	 **/
	public function skip_cf7_mail( $skip_mail ) {
		if ( isset( $_POST['_c2p_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			if ( isset( $_POST['save_cf7_2_post'] ) && 'true' === $_POST['save_cf7_2_post'] ) {
				$skip_mail = true;
			}
		}
		return $skip_mail;
	}
	/**
	 * Function to load scripts required for cf7 form loading
	 * hooked on WP 4.7 'do_shortcode_tag' filter
	 *
	 * @since 1.3.0
	 * @param string $output Shortcode output.
	 * @param string $tag    Shortcode name.
	 * @param array  $attr   Shortcode attributes array.
	 * @return     string    shortcode html string.
	 **/
	public function load_cf7_script( $output, $tag, $attr ) {
		if ( 'contact-form-7' !== $tag ) {
			return $output;
		}
		if ( ! isset( $attr['id'] ) ) {
			wpg_debug( $attr, 'Missing cf7 shortcode id attribute' );
			return $output;
		}
		$cf7_id = $attr['id'];
		// let get the corresponding factory object.
		$factory = c2p_get_factory();
		if ( $factory->is_mapped( $cf7_id ) ) {
			$cf7_2_post_id = '';
			$cf7_key       = c2p_get_form_key( $cf7_id );
			if ( ! $factory->is_filter( $cf7_id ) ) {
				$mapper = $factory->get_post_mapper( $cf7_id );
				// let's ensure the page does not cache our values.
				$this->not_form_page = false;
				// unique nonce.
				$nonce = 'cf7_2_post_' . wp_create_nonce( 'cf7_2_post' . wp_rand() );
				// verify if this cf7 form is mapped to a specific post.
				if ( isset( $attr['cf7_2_post_id'] ) ) {
					$cf7_2_post_id = $attr['cf7_2_post_id'];
				}
				$form_values   = $factory->get_form_values( $cf7_id, $cf7_2_post_id );
				$inline_script = $factory->get_form_field_script( $nonce, $mapper );
				wp_enqueue_script( $this->plugin_name . '-load' ); // previously registered.
				wp_localize_script( $this->plugin_name . '-load', $nonce, $form_values );
				wp_add_inline_script( $this->plugin_name . '-load', $inline_script );
				$scripts = apply_filters( 'cf7_2_post_form_append_output', '', $attr, $nonce, $mapper->cf7_key, $form_values );
				$output  = '<div id="' . $nonce . '" class="cf7_2_post cf7_form_' . $cf7_id . '">' . $output . PHP_EOL . $scripts . '</div>';
				wp_enqueue_style( $this->plugin_name . '-css' );
			}
			/**
			* Action for enqueueing other scripts.
			*
			* @since 3.8.0.
			* @param string $cf7_key unique key of form being printed.
			* @param int $cf7_2_post_id post ID of form being printed.
			*/
			do_action( 'cf72post_form_printed_to_screen', $cf7_key, $cf7_2_post_id );
		}
		return $output;
	}
	/**
	 * Disables browser page caching for forms which are mapped to a post.
	 * Hooked on 'wp_head' in fn load_cf7_script.
	 *
	 * @since 3.0.0
	 */
	public function disable_browser_page_cache() {
		if ( ! $this->scan_for_mapped_forms() ) {
			return;
		}
		if ( ! apply_filters( 'cf7_2_post_print_page_nocache_metas', true ) ) {
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
	 * Function to scan the current post content for mapped cf7 forms.
	 *
	 * @since 3.0.0
	 * @param string $content content to check, else it will try to get the global $post object's content.
	 * @return boolean true if found.
	 */
	public function scan_for_mapped_forms( $content = null ) {
		global $post;
		$has_mapped_form = false;
		if ( null === $content ) {
			if ( isset( $post ) ) {
				$content = $post->post_content;
			} else {
				$content = '';
			}
		}
		if ( empty( $content ) ) {
			return $has_mapped_form;
		}
		$shortcodes = array( 'cf7-form', 'contact-form-7' );
		$pattern    = get_shortcode_regex();

		// if shortcode 'book' exists.
		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches )
		&& array_key_exists( 2, $matches )
		&& ! empty( array_intersect( $shortcodes, $matches[2] ) ) ) {
			$shortcode_atts = array();
			foreach ( $shortcodes as $shortcode ) {
				$shortcode_atts = array_merge( $shortcode_atts, array_keys( $matches[2], $shortcode, true ) );
			}
			// if shortcode has attributes.
			$cf7_forms = array();
			if ( ! empty( $shortcode_atts ) ) {
				foreach ( $shortcode_atts as $idx ) {
					preg_match( '/id="(\d+)"/', $matches[3][ $idx ], $cf7_id );
					preg_match( '/cf7key="(.*)"/', $matches[3][ $idx ], $cf7_key );
					if ( ! empty( $cf7_id ) ) {
						$cf7_forms[] = $cf7_id[1];
					} elseif ( ! empty( $cf7_key ) ) {
						$cf7_forms[] = c2p_get_form_id( $cf7_key[1] );
					}
				}
			}
			$factory = c2p_get_factory();
			foreach ( $cf7_forms as $form_id ) {
				$has_mapped_form = $factory->is_mapped( $form_id );
				if ( $has_mapped_form ) {
					break;
				}
			}
		}
		return $has_mapped_form;
	}
	/**
	 * Register a [save] shortcode with CF7.
	 * Hooked  on 'wpcf7_init'
	 * This function registers a callback function to expand the shortcode for the save button field.
	 *
	 * @since 2.0.0
	 */
	public function save_button_shortcode_handler() {
		if ( function_exists( 'wpcf7_add_form_tag' ) ) {
			wpcf7_add_form_tag(
				array( 'save' ),
				array( $this, 'save_button_display' ),
				false // no name.
			);
		}
	}
	/**
	 * Dsiplays the save button field
	 * This function expands the shortcode into the required hiddend fields
	 * to manage the googleMap forms.  This function is called by cf7 directly, registered above.
	 *
	 * @since 1.0.0
	 * @param string $tag the tag name designated in the tag help screen.
	 * @return string a set of html fields to capture the googleMap information.
	 */
	public function save_button_display( $tag ) {
		// check if this form is currently mapped.
		$atts     = array();
		$disabled = false;
		$cf7_form = wpcf7_get_current_contact_form();
		$factory  = c2p_get_factory();
		$cf7_key  = c2p_get_form_key( $cf7_form->id() );

		if ( ! $factory->is_live( $cf7_form->id() ) ) {
			$disabled = true;
		}
		// enqueue required scripts and styles.
		wp_enqueue_script( $this->plugin_name . '-save' );
		/** NB @since 5.4.7 fix block-theme localise bug */
		$localise = array(
			'disabled' => $disabled,
			'error'    => __( 'save is disabled, form is not mapped.', 'post-my-contact-form-7' ),
			'paint'    => apply_filters( 'c2p_autostyle_save_button', true, $cf7_key ),
		);
		add_action(
			'wp_footer',
			function() use ( $localise ) {
				printf( '<script type="text/javascript">var cf72post_save = %1$s</script>', wp_json_encode( $localise ) );
			}
		);

		$tag   = new WPCF7_FormTag( $tag );
		$class = wpcf7_form_controls_class( $tag->type );

		$atts['class']    = $tag->get_class_option( $class );
		$atts['class']   .= ' wpcf7-submit cf7_2_post_save ';
		$atts['id']       = $tag->get_id_option();
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

		$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

		if ( empty( $value ) ) {
			$value = __( 'Save', 'contact-form-7' );
		}

		$atts['type']  = 'submit';
		$atts['value'] = $value;

		$atts = wpcf7_format_atts( $atts );

		$html  = sprintf( '<input %1$s />', $atts );
		$html .= PHP_EOL . '<input type="hidden" name="save_cf7_2_post" class="cf7_2_post_draft" value="false"/>';
		return $html;
	}
	/**
	 * Reset cf7 validation if this form is being saved as a draft.
	 * Hooked to filter 'wpcf7_validate', sets up the final $results object
	 *
	 * @since 2.0.0
	 * @param WPCF7_Validation $results   validation object.
	 * @param Array            $tags   an array of cf7 tag used in this form.
	 * @return WPCF7_Validation  validation results.
	 **/
	public function save_skips_wpcf7_validate( $results, $tags ) {
		// check if this is a mapped form.
		if ( isset( $_POST['_c2p_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			// check if this submission is a draft being saved.
			if ( ! isset( $_POST['save_cf7_2_post'] ) || 'false' === $_POST['save_cf7_2_post'] ) {
				return $results;
			}

			$cf7form  = WPCF7_ContactForm::get_current();
			$cf7_id   = $cf7form->id();
			$cf7_post = get_post( $cf7_id, ARRAY_A );
			$cf7_key  = $cf7_post['post_name'];
			/**
			* Filter to skip validation if this form is being saved as a draft
			*
			* @since 2.0.0
			* @param boolean $skip_validation  default to true.
			* @param string $cf7_key  current form's unique key identifier.
			*/
			$skip_validation = true;

			if ( apply_filters( 'cf7_2_post_draft_skips_validation', $skip_validation, $cf7_key ) ) {
				$results = new WPCF7_Validation();
			}
			// skip mail by default.
			$skip_mail = true;
			/**
			* Filter to skip mail sending if this form is being saved as a draft
			*
			* @since 2.0.0
			* @param boolean $skip_mail  default to true.
			* @param string $cf7_key  current form's unique key identifier.
			*/
			if ( apply_filters( 'cf7_2_post_draft_skips_mail', $skip_mail, $cf7_key ) ) {
				add_filter(
					'wpcf7_skip_mail',
					function( $skip_mail, $contact_form ) use ( $cf7_id ) {
						if ( $cf7_id === $contact_form->id() ) {
							return true;
						} else {
							return $skip_mail;
						}
					},
					10,
					2
				);
			}
		}
		return $results;
	}
	/**
	 * Method to skip CF7 plugin calidation of file fields when saving draft form.
	 * Hooked to 'wpcf7_validate_file'
	 *
	 * @since 5.0.0
	 * @param WPCF7_Validation $result   validation object.
	 * @param WPCF7_FormTag    $tag  file tag object.
	 * @return WPCF7_Validation  validation results.
	 */
	public function save_skips_file_validation( $result, $tag ) {
		if ( isset( $_POST['_c2p_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			if ( ! isset( $_POST['save_cf7_2_post'] ) || 'false' === $_POST['save_cf7_2_post'] ) {
				return $result;
			}
		}
		$cf7form  = WPCF7_ContactForm::get_current();
		$cf7_id   = $cf7form->id();
		$cf7_post = get_post( $cf7_id, ARRAY_A );
		$cf7_key  = $cf7_post['post_name'];
		/**
		* Filter to skip validation if this form is being saved as a draft
		*
		* @since 2.0.0
		* @param boolean $skip_validation  default to true.
		* @param string $cf7_key  current form's unique key identifier.
		*/
		$skip_validation = true;

		if ( apply_filters( 'cf7_2_post_draft_skips_validation', $skip_validation, $cf7_key ) ) {
			$result = new WPCF7_Validation();
		}
		return $result;
	}
	/**
	 * Map author to hidden field on form load.
	 * Hooked to 'wpcf7_form_hidden_fields'
	 *
	 * @since 3.9.0
	 * @param array $hidden array of hidden fields.
	 * @return array array of hidden fields.
	 */
	public function add_hidden_fields( $hidden ) {
		$author = 1;// default to admin.
		if ( current_user_can( 'edit_posts' ) ) {
			$user   = wp_get_current_user();
			$author = $user->ID;
		}
		$hidden['_map_author'] = $author;
		// create nonce for transient storage as well as validation.
		$hidden['_c2p_nonce'] = CF72Post_Mapping_Factory::noncify();
		if ( ! isset( $hidden['_wpnonce'] ) ) { /** NB @since 6.0.0 setup wp_rest nonce for user authentication. */
			$hidden['_wpnonce'] = wp_create_nonce( 'wp_rest' );
		}
		return $hidden;
	}
	/**
	 * Filter message for draft forms.
	 * hooked to
	 *
	 * @since 4.0.0
	 * @param string $message confirmation message.
	 * @param string $status status.
	 * @return string text_description.
	 */
	public function draft_message( $message, $status ) {
		if ( isset( $_POST['_c2p_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			if ( 'mail_sent_ok' === $status && isset( $_POST['save_cf7_2_post'] ) && 'true' === $_POST['save_cf7_2_post'] ) {
				$form = wpcf7_get_current_contact_form();
				if ( ! empty( $form ) ) {
					$messages = $form->prop( 'messages' );
					$message  = isset( $messages['draft_saved'] ) ? $messages['draft_saved'] : $message;
				}
			}
		}
		return $message;
	}
	/**
	 * Function to un-array single select values
	 * Hooked to 'wpcf7_posted_data_{$tag_type}'
	 *
	 * @since 4.1.8
	 * @param mixed     $value cf7 plugin submitted value.
	 * @param string    $org something from cf7.
	 * @param WPCF7_Tag $tag  form field object.
	 * @return string single value if original was single.
	 */
	public function array_to_single( $value, $org, $tag ) {
		if ( isset( $_POST['_c2p_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			if ( is_array( $value ) && isset( $_POST[ $tag->name ] ) && ! is_array( $_POST[ $tag->name ] ) ) {
				$value = sanitize_key( $_POST[ $tag->name ] ); // keep the original value.
			}
		}
		return $value;
	}
}
