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
 * Defines the plugin name, version, and hooks for enqueuing public assets.
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
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Flag to prevent page caching form values.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      boolean $not_form_page Flag to prevent page caching form values.
	 */
	private $not_form_page;

	/**
	 * Flag if smart grid is active
	 * 
	 * @since     7.0.0
	 * @access    private
	 * @var       boolean $is_smart_grid_active flag to handle CF7 Smart Grid Layout plugin special forms.
	 */
	private $is_smart_grid_active = false;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->not_form_page = true;
	}

	/**
	 * Set smart grid is active flag.
	 * 
	 */
	public function is_smart_grid_active( $flag ) {
		$this->is_smart_grid_active = $flag;
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		$plugin_dir = plugin_dir_url( __DIR__ );

		wp_register_script(
			$this->plugin_name . '-save',
			$plugin_dir . 'public/js/cf7-2-post-save-draft.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_register_script(
			$this->plugin_name . '-load',
			$plugin_dir . 'public/js/cf7-2-post-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_register_script(
			'hybriddd-js',
			$plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.js',
			array(),
			$this->version,
			true
		);
	}

	/**
	 * Register styles for the public-facing side of the site.
	 *
	 * @since 5.2.2
	 */
	public function register_styles() {
		$plugin_dir = plugin_dir_url( __DIR__ );

		wp_register_style(
			$this->plugin_name . '-css',
			$plugin_dir . 'public/css/cf7-2-post-styling.css',
			array(),
			$this->version
		);

		wp_register_style(
			'hybriddd-style',
			$plugin_dir . 'assets/hybrid-html-dropdown/hybrid-dropdown.min.css',
			array(),
			$this->version
		);
	}

	/**
	 * Save CF7 form submission to its mapped post.
	 * Hooks 'wpcf7_before_send_mail' after all validation is done.
	 *
	 * @since 1.0.0
	 * @param WPCF7_Contact_Form $cf7_form CF7 form object.
	 * @return WPCF7_Contact_Form The original form object.
	 */
	public function save_cf7_2_post( $cf7_form ) {
		$cf7_post_id = $cf7_form->id();
		$factory     = c2p_get_factory();

		if ( ! $factory->is_live( $cf7_post_id ) ) {
			return $cf7_form;
		}

		$submission = WPCF7_Submission::get_instance();

		// Backward compatibility for filter-based mapping.
		if ( $factory->is_filter( $cf7_post_id ) ) {
			/**
			 * Action to bypass the form submission process.
			 *
			 * @since 1.3.0
			 * @param string $key  Unique form key.
			 * @param array  $data Array of submitted key=>value pairs.
			 * @param array  $file Array of submitted files if any.
			 */
			do_action(
				'cf7_2_post_save_submission',
				cf7sg_get_form_key( $cf7_post_id ),
				$submission->get_posted_data(),
				$submission->uploaded_files()
			);
			return $cf7_form;
		}

		$mapper  = $factory->get_post_mapper( $cf7_post_id );
		$post_id = $mapper->save_form_2_post( $submission );

		self::setup_mailtags_for_mapped_post( $post_id );

		return $cf7_form;
	}

	/**
	 * Setup mail tags filters for mapped post.
	 *
	 * @since 4.1.0
	 * @param int $post_id Post ID.
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
	 * Skip CF7 mail if this is a draft form being saved.
	 * Hooked on 'wpcf7_skip_mail'.
	 *
	 * @since 2.0.0
	 * @param bool $skip_mail Boolean flag.
	 * @return bool True to skip mails if this is a draft form being saved.
	 */
	public function skip_cf7_mail( $skip_mail ) {
		if ( ! isset( $_POST['_c2p_nonce'] ) ) {
			return $skip_mail;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			return $skip_mail;
		}

		if ( isset( $_POST['save_cf7_2_post'] ) && 'true' === $_POST['save_cf7_2_post'] ) {
			$skip_mail = true;
		}

		return $skip_mail;
	}

	/**
	 * Load scripts required for CF7 form loading.
	 * Hooked on 'do_shortcode_tag' filter.
	 *
	 * @since 1.3.0
	 * @param string $output Shortcode output.
	 * @param string $tag    Shortcode name.
	 * @param array  $attr   Shortcode attributes array.
	 * @return string Shortcode HTML string.
	 */
	public function load_cf7_script( $output, $tag, $attr ) {
		if ( 'contact-form-7' !== $tag ) {
			return $output;
		}

		if ( ! isset( $attr['id'] ) ) {
			wpg_debug( $attr, 'Missing cf7 shortcode id attribute' );
			return $output;
		}

		$cf7_id  = $attr['id'];
		$factory = c2p_get_factory();

		if ( ! $factory->is_mapped( $cf7_id ) ) {
			return $output;
		}

		$cf7_key       = cf7sg_get_form_key( $cf7_id );
		$cf7_2_post_id = isset( $attr['cf7_2_post_id'] ) ? $attr['cf7_2_post_id'] : '';

		if ( ! $factory->is_filter( $cf7_id ) ) {
			$output = $this->load_mapped_form_scripts( $output, $factory, $cf7_id, $cf7_2_post_id, $attr );
		}

		/**
		 * Action for enqueuing other scripts.
		 *
		 * @since 3.8.0
		 * @param string $cf7_key        Unique key of form being printed.
		 * @param int    $cf7_2_post_id  Post ID of form being printed.
		 */
		do_action( 'cf72post_form_printed_to_screen', $cf7_key, $cf7_2_post_id );

		return $output;
	}

	/**
	 * Load scripts for mapped form.
	 *
	 * @since 5.3.0
	 * @param string                     $output          Shortcode output.
	 * @param CF72Post_Mapping_Factory   $factory         Factory instance.
	 * @param int                        $cf7_id          CF7 form ID.
	 * @param int|string                 $cf7_2_post_id   Post ID or empty.
	 * @param array                      $attr            Shortcode attributes.
	 * @return string Modified shortcode output.
	 */
	private function load_mapped_form_scripts( $output, $factory, $cf7_id, $cf7_2_post_id, $attr ) {
		$mapper = $factory->get_post_mapper( $cf7_id );

		$this->not_form_page = false;

		$nonce         = 'cf7_2_post_' . wp_create_nonce( 'cf7_2_post_' . wp_rand() );
		$form_values   = $factory->get_form_values( $cf7_id, $cf7_2_post_id );
		$inline_script = '';

		if( $this->is_smart_grid_active ) {
			$cf7_key = cf7sg_get_form_key( $cf7_id );
			add_filter('cf7sg_prefill_form_fields', function ( $prefill, $cf7key ) use ( $form_values , $cf7_key ) {
				if( $cf7_key == $cf7key ) {
					$prefill = $form_values;
				}
				return $prefill;
			}, 10, 2);
		} else { //incase the smart grid is not active, handle the form prefill.
			$inline_script = $factory->get_form_field_script( $nonce, $mapper );
			$form_values = $factory->encode_field_names( $form_values );
			wp_enqueue_script( $this->plugin_name . '-load' );
			// wp_localize_script( $this->plugin_name . '-load', $nonce, $form_values );
			$jsid = $this->plugin_name . '-inline';
			add_action(
				'wp_footer',
				function() use ( $nonce, $form_values, $jsid, $inline_script ) {
					printf( '<script type="text/javascript">var %1s  = %2s</script>', 
						$nonce, 
						json_encode( $form_values ) 
					);
					printf( '<script id="%1s" type="text/javascript">%2s</script>', 
						$jsid, 
						$inline_script
					);
				}
			);
		}
		// wp_enqueue_script( $this->plugin_name . '-inline' );
		// wp_add_inline_script( $this->plugin_name . '-load', $inline_script, 'after' );

		$scripts = apply_filters(
			'cf7_2_post_form_append_output',
			'',
			$attr,
			$nonce,
			$mapper->cf7_key,
			$form_values
		);

		$output = '<div id="' . esc_attr( $nonce ) . '" class="cf7_2_post cf7_form_' . esc_attr( $cf7_id ) . '">' .
			$output . PHP_EOL . $scripts . '</div>';

		wp_enqueue_style( $this->plugin_name . '-css' );

		return $output;
	}

	/**
	 * Disable browser page caching for forms mapped to a post.
	 * Hooked on 'wp_head'.
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
	 * Scan the current post content for mapped CF7 forms.
	 *
	 * @since 3.0.0
	 * @param string $content Content to check (optional).
	 * @return bool True if a mapped form is found.
	 */
	public function scan_for_mapped_forms( $content = null ) {
		global $post;

		if ( null === $content && isset( $post ) ) {
			$content = $post->post_content;
		}

		if ( empty( $content ) ) {
			return false;
		}

		$shortcodes = array( 'cf7-form', 'contact-form-7' );
		$pattern    = get_shortcode_regex();

		if ( ! preg_match_all( '/' . $pattern . '/s', $content, $matches ) ) {
			return false;
		}

		if ( ! array_key_exists( 2, $matches ) ) {
			return false;
		}

		$intersection = array_intersect( $shortcodes, $matches[2] );
		if ( empty( $intersection ) ) {
			return false;
		}

		$shortcode_atts = array();
		foreach ( $shortcodes as $shortcode ) {
			$shortcode_atts = array_merge(
				$shortcode_atts,
				array_keys( $matches[2], $shortcode, true )
			);
		}

		if ( empty( $shortcode_atts ) ) {
			return false;
		}

		$cf7_forms = array();
		foreach ( $shortcode_atts as $idx ) {
			preg_match( '/id="(\d+)"/', $matches[3][ $idx ], $cf7_id );
			preg_match( '/cf7key="(.*)"/', $matches[3][ $idx ], $cf7_key );

			if ( ! empty( $cf7_id ) ) {
				$cf7_forms[] = $cf7_id[1];
			} elseif ( ! empty( $cf7_key ) ) {
				$cf7_forms[] = cf7sg_get_form_id( $cf7_key[1] );
			}
		}

		$factory = c2p_get_factory();
		foreach ( $cf7_forms as $form_id ) {
			if ( $factory->is_mapped( $form_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Reset the CF7 select enum rules for mapped forms.
	 * Hooked on 'wpcf7_init'.
	 *
	 * @since 6.1.0
	 */
	public function reset_select_enum_rules() {
		remove_action( 'wpcf7_swv_create_schema', 'wpcf7_swv_add_select_enum_rules', 20, 2 );

		add_action(
			'wpcf7_swv_create_schema',
			array( $this, 'add_custom_select_enum_rules' ),
			20,
			2
		);
	}

	/**
	 * Add custom select enum rules for mapped forms.
	 *
	 * @since 5.3.0
	 * @param object $schema The schema object.
	 * @param object $form   The form object.
	 */
	public function add_custom_select_enum_rules( $schema, $form ) {
		$tags    = $form->scan_form_tags( array( 'basetype' => array( 'select' ) ) );
		$values  = $this->get_select_field_values( $tags, $form );
		$factory = c2p_get_factory();
		$form_id = $form->id();

		if ( ! $factory->is_mapped( $form_id ) ) {
			return;
		}

		$mapper    = $factory->get_post_mapper( $form_id );
		$mapped2tax = $mapper->get_post_map_taxonomy();

		foreach ( $values as $field => $field_values ) {
			if ( isset( $mapped2tax[ $field ] ) ) {
				$taxonomy      = $mapped2tax[ $field ];
				$branch        = false;
				$terms         = $factory->filter_taxonomy_query( $taxonomy, $branch, $field, $mapper );
				$field_values  = wp_list_pluck( $terms, 'term_id' );
			} else {
				$field_values = array_map(
					static function ( $value ) {
						return html_entity_decode(
							(string) $value,
							ENT_QUOTES | ENT_HTML5,
							'UTF-8'
						);
					},
					$field_values
				);
			}

			$field_values = array_filter(
				array_unique( $field_values ),
				static function ( $value ) {
					return '' !== $value;
				}
			);

			$schema->add_rule(
				wpcf7_swv_create_rule(
					'enum',
					array(
						'field'  => $field,
						'accept' => array_values( $field_values ),
						'error'  => $form->filter_message(
							esc_html__( 'Undefined value was submitted through this field.', 'contact-form-7' )
						),
					)
				)
			);
		}
	}

	/**
	 * Get select field values from tags.
	 *
	 * @since 5.3.0
	 * @param array  $tags Array of form tags.
	 * @param object $form The form object.
	 * @return array Array of field values.
	 */
	private function get_select_field_values( $tags, $form ) {
		$values = array_reduce(
			$tags,
			function ( $values, $tag ) {
				if ( ! isset( $values[ $tag->name ] ) ) {
					$values[ $tag->name ] = array();
				}

				$tag_values = array_merge(
					(array) $tag->values,
					(array) $tag->get_data_option()
				);

				if ( $tag->has_option( 'first_as_label' ) ) {
					$tag_values = array_slice( $tag_values, 1 );
				}

				$values[ $tag->name ] = array_merge(
					$values[ $tag->name ],
					$tag_values
				);

				return $values;
			},
			array()
		);

		return $values;
	}

	/**
	 * Register a [save] shortcode with CF7.
	 * Hooked on 'wpcf7_init'.
	 *
	 * @since 2.0.0
	 */
	public function save_button_shortcode_handler() {
		if ( function_exists( 'wpcf7_add_form_tag' ) ) {
			wpcf7_add_form_tag(
				array( 'save' ),
				array( $this, 'save_button_display' ),
				false // No name.
			);
		}
	}

	/**
	 * Display the save button field.
	 * Called by CF7 directly via wpcf7_add_form_tag.
	 *
	 * @since 1.0.0
	 * @param string $tag The tag name.
	 * @return string HTML for the save button.
	 */
	public function save_button_display( $tag ) {
		$cf7_form = wpcf7_get_current_contact_form();
		$factory  = c2p_get_factory();
		$cf7_key  = cf7sg_get_form_key( $cf7_form->id() );

		$disabled = ! $factory->is_live( $cf7_form->id() );

		wp_enqueue_script( $this->plugin_name . '-save' );

		$localise = array(
			'disabled' => $disabled,
			'error'    => __( 'Save is disabled, form is not mapped.', 'post-my-contact-form-7' ),
			'paint'    => apply_filters( 'c2p_autostyle_save_button', true, $cf7_key ),
		);

		add_action(
			'wp_footer',
			function() use ( $localise ) {
				printf(
					'<script type="text/javascript">var cf72post_save = %s</script>',
					wp_json_encode( $localise )
				);
			}
		);

		$tag_obj = new WPCF7_FormTag( $tag );
		$class   = wpcf7_form_controls_class( $tag_obj->type );

		$atts = array(
			'class'    => $tag_obj->get_class_option( $class ) . ' wpcf7-submit cf7_2_post_save',
			'id'       => $tag_obj->get_id_option(),
			'tabindex' => $tag_obj->get_option( 'tabindex', 'int', true ),
			'type'     => 'submit',
			'value'    => $tag_obj->values[0] ?? __( 'Save', 'contact-form-7' ),
		);

		$html  = sprintf( '<input %s />', wpcf7_format_atts( $atts ) );
		$html .= PHP_EOL . '<input type="hidden" name="save_cf7_2_post" class="cf7_2_post_draft" value="false"/>';

		return $html;
	}

	/**
	 * Reset CF7 validation if form is being saved as draft.
	 * Hooked to 'wpcf7_validate'.
	 *
	 * @since 2.0.0
	 * @param WPCF7_Validation $results Validation object.
	 * @param array            $tags    Array of CF7 tags.
	 * @return WPCF7_Validation Validation results.
	 */
	public function save_skips_wpcf7_validate( $results, $tags ) {
		if ( ! isset( $_POST['_c2p_nonce'] ) ) {
			return $results;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			return $results;
		}

		if ( ! isset( $_POST['save_cf7_2_post'] ) || 'false' === $_POST['save_cf7_2_post'] ) {
			return $results;
		}

		$cf7form  = WPCF7_ContactForm::get_current();
		$cf7_id   = $cf7form->id();
		$cf7_post = get_post( $cf7_id, ARRAY_A );
		$cf7_key  = $cf7_post['post_name'];

		/**
		 * Filter to skip validation if form is being saved as draft.
		 *
		 * @since 2.0.0
		 * @param bool   $skip_validation Default true.
		 * @param string $cf7_key         Current form's unique key identifier.
		 */
		$skip_validation = apply_filters( 'cf7_2_post_draft_skips_validation', true, $cf7_key );

		if ( $skip_validation ) {
			$results = new WPCF7_Validation();
		}

		/**
		 * Filter to skip mail sending if form is being saved as draft.
		 *
		 * @since 2.0.0
		 * @param bool   $skip_mail Default true.
		 * @param string $cf7_key   Current form's unique key identifier.
		 */
		$skip_mail = apply_filters( 'cf7_2_post_draft_skips_mail', true, $cf7_key );

		if ( $skip_mail ) {
			add_filter(
				'wpcf7_skip_mail',
				function( $skip, $contact_form ) use ( $cf7_id ) {
					if ( $cf7_id === $contact_form->id() ) {
						return true;
					}
					return $skip;
				},
				10,
				2
			);
		}

		return $results;
	}

	/**
	 * Skip CF7 validation of file fields when saving draft form.
	 * Hooked to 'wpcf7_validate_file'.
	 *
	 * @since 5.0.0
	 * @param WPCF7_Validation $result Validation object.
	 * @param WPCF7_FormTag    $tag    File tag object.
	 * @return WPCF7_Validation Validation results.
	 */
	public function save_skips_file_validation( $result, $tag ) {
		if ( ! isset( $_POST['_c2p_nonce'] ) ) {
			return $result;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			return $result;
		}

		if ( ! isset( $_POST['save_cf7_2_post'] ) || 'false' === $_POST['save_cf7_2_post'] ) {
			return $result;
		}

		$cf7form  = WPCF7_ContactForm::get_current();
		$cf7_id   = $cf7form->id();
		$cf7_post = get_post( $cf7_id, ARRAY_A );
		$cf7_key  = $cf7_post['post_name'];

		/**
		 * Filter to skip validation if form is being saved as draft.
		 *
		 * @since 2.0.0
		 * @param bool   $skip_validation Default true.
		 * @param string $cf7_key         Current form's unique key identifier.
		 */
		$skip_validation = apply_filters( 'cf7_2_post_draft_skips_validation', true, $cf7_key );

		if ( $skip_validation ) {
			$result = new WPCF7_Validation();
		}

		return $result;
	}

	/**
	 * Map author to hidden field on form load.
	 * Hooked to 'wpcf7_form_hidden_fields'.
	 *
	 * @since 3.9.0
	 * @param array $hidden Array of hidden fields.
	 * @return array Array of hidden fields.
	 */
	public function add_hidden_fields( $hidden ) {
		$author = 1; // Default to admin.

		if ( current_user_can( 'edit_posts' ) ) {
			$user   = wp_get_current_user();
			$author = $user->ID;
		}

		$hidden['_map_author'] = $author;

		// Create nonce for transient storage and validation.
		$hidden['_c2p_nonce'] = CF72Post_Mapping_Factory::noncify();

		// Setup WP REST nonce for user authentication.
		if ( ! isset( $hidden['_wpnonce'] ) ) {
			$hidden['_wpnonce'] = wp_create_nonce( 'wp_rest' );
		}

		//map id to save to an existing post.
		$hidden['map_post_id']='';

		return $hidden;
	}

	/**
	 * Filter message for draft forms.
	 *
	 * @since 4.0.0
	 * @param string $message Confirmation message.
	 * @param string $status  Form submission status.
	 * @return string Filtered message.
	 */
	public function draft_message( $message, $status ) {
		if ( ! isset( $_POST['_c2p_nonce'] ) ) {
			return $message;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			return $message;
		}

		if ( 'mail_sent_ok' !== $status ) {
			return $message;
		}

		if ( ! isset( $_POST['save_cf7_2_post'] ) || 'true' !== $_POST['save_cf7_2_post'] ) {
			return $message;
		}

		$form = wpcf7_get_current_contact_form();
		if ( ! empty( $form ) ) {
			$messages = $form->prop( 'messages' );
			if ( isset( $messages['draft_saved'] ) ) {
				$message = $messages['draft_saved'];
			}
		}

		return $message;
	}

	/**
	 * Convert array values to single for select fields.
	 * Hooked to 'wpcf7_posted_data_{$tag_type}'.
	 *
	 * @since 4.1.8
	 * @param mixed     $value CF7 plugin submitted value.
	 * @param string    $org   Original value (unused).
	 * @param WPCF7_Tag $tag   Form field object.
	 * @return mixed Single value if original was single.
	 */
	public function array_to_single( $value, $org, $tag ) {
		if ( ! isset( $_POST['_c2p_nonce'] ) ) {
			return $value;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), CF72Post_Mapping_Factory::NONCE_ACTION ) ) {
			return $value;
		}

		if ( is_array( $value ) && isset( $_POST[ $tag->name ] ) && ! is_array( $_POST[ $tag->name ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $tag->name ] ) );
		}

		return $value;
	}
}