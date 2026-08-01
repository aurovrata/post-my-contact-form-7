<?php
/**
 * Abstract Post Mapper Class for CF7 to Post Plugin to handle public requests such as saving submissions to posts.
 *
 * Defines the interface for mapping Contact Form 7 forms to WordPress posts.
 * This abstract class provides common functionality for both system and custom
 * post type mappers.
 *
 * @link       http://digital-tiff.in
 * @since      5.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 */

// Include dependencies.
require_once plugin_dir_path( __FILE__ ) . 'class-c2p-post-mapper-admin.php';

/**
 * Abstract class to define a general mapping interface for form to post.
 *
 * This class handles the mapping of CF7 form fields to post fields,
 * meta fields, and taxonomies. It provides methods for saving, loading,
 * and managing post mappings.
 *
 * @since 5.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/mapper
 * @author     Aurovrata V. <vrata@digital-tiff.in>
 */
abstract class C2P_Post_Mapper extends C2P_Post_Mapper_Admin {

	/**
	 * Constructor.
	 *
	 * @since 5.0.0
	 * @param int                      $cf7_id  The Contact Form 7 form ID.
	 * @param CF72Post_Mapping_Factory $factory The mapping factory instance.
	 */
	public function __construct( $cf7_id, $factory ) {
		$this->cf7_post_id = absint( $cf7_id );
		self::$factory     = $factory;
	}
	/**
	 * Get form key
	 * @since 7.1.0
	 * @return string form post slug/key.
	 */
	public function get_cf7_key(){
		if( !isset( $this->cf7_key) || empty($this->cf7_key) ){
			$this->cf7_key = cf7sg_get_form_id($this->cf7_post_id);
		}
		return $this->cf7_key;
	}
	/* ==========================================================================
	 * SAVE FORM TO POST - CORE METHOD
	 * ========================================================================== */

	/**
	 * Save the submitted form data to a new/existing post.
	 *
	 * Calling this function assumes the mapped post_type exists and is published.
	 * Hooked to 'wpcf7_before_send_mail' which calls $public->save_cf7_2_post().
	 *
	 * @since 1.0.0
	 * @param WPCF7_Submission $submission CF7 submission object.
	 * @return int|false Post ID on success, false on failure.
	 */
	public function save_form_2_post( $submission ) {
		// Validate nonce.
		if ( ! isset( $_POST['_c2p_nonce'] ) || 
			 ! wp_verify_nonce( sanitize_key( $_POST['_c2p_nonce'] ), self::$factory::NONCE_ACTION ) ) {
			$this->log_error( 'Invalid nonce while saving form to post.' );
			return false;
		}

		$cf7_form_data = $submission->get_posted_data();
		$is_submitted  = true;

		if ( isset( $cf7_form_data['save_cf7_2_post'] ) && 'true' === $cf7_form_data['save_cf7_2_post'] ) {
			$is_submitted = false;
		}

		$this->load_form_fields();

		// Check if this is a system post mapped using an action.
		if ( has_action( 'cf7_2_post_save-' . $this->get( 'type' ) ) ) {
			/**
			 * Action to bypass the form submission process.
			 *
			 * @since 1.3.0
			 * @param string $key  Unique form key.
			 * @param array  $data Array of submitted key=>value pairs.
			 * @param array  $file Array of submitted files if any.
			 */
			do_action(
				'cf7_2_post_save-' . $this->get( 'type' ),
				$this->cf7_key,
				$cf7_form_data,
				$submission->uploaded_files()
			);
			return false;
		}

		// Get the author.
		$author = $this->get_post_author( $cf7_form_data );

		// Get the post status.
		$post_status = $this->get_post_status( $is_submitted, $cf7_form_data );

		// Get the post title.
		$post_title = $this->get_post_title();

		// Prepare the post data.
		$post = array(
			'post_type'   => $this->post_properties['type'],
			'post_author' => $author,
			'post_status' => $post_status,
			'post_title'  => $post_title,
		);

		// Check for existing post ID.
		$post_id = $this->get_existing_post_id();
		$is_update = false;

		if ( ! empty( $post_id ) ) {
			$wp_post = get_post( $post_id );
			if ( $wp_post ) {
				$post['post_status'] = $post_status;
				$post['post_author'] = $wp_post->post_author;
				$post['post_title']  = $wp_post->post_title;
				$is_update = true;
			}
		}

		// If new post, set author via filter.
		if ( ! $is_update ) {
			$post['post_author'] = apply_filters(
				'cf7_2_post_author_' . $this->post_properties['type'],
				$author,
				$this->cf7_post_id,
				$cf7_form_data,
				$this->cf7_key
			);
			$post_id = wp_insert_post( $post );
		}

		$post['ID'] = $post_id;

		// Process post fields.
		$has_post_fields = $this->process_post_fields( $post, $cf7_form_data, $submission );

		// Set default slug.
		if ( empty( $post['post_name'] ) ) {
			$post['post_name'] = 'cf7_' . $this->cf7_post_id . '_to_post_' . $post_id;
			if ( isset( $post['post_title'] ) ) {
				$post['post_name'] = sanitize_title( $post['post_title'] );
			}
		}

		// Update the post if we have fields.
		if ( $has_post_fields ) {
			$post_id = wp_update_post( $post );
		}

		// Save submission flag.
		$submission_flag = $is_submitted ? 'yes' : 'no';
		update_post_meta( $post_id, '_cf7_2_post_form_submitted', $submission_flag );

		// Process meta fields.
		$this->process_meta_fields( $post_id, $cf7_form_data, $submission );

		// Process taxonomy fields.
		$this->process_taxonomy_fields( $post_id, $cf7_form_data );

		// Trigger actions.
		$this->trigger_submission_actions( $post_id, $cf7_form_data, $submission, $is_submitted );

		// Store transient for redirect.
		$this->store_submission_transient( $post_id );

		return $post_id;
	}

	/* ==========================================================================
	 * SAVE FORM HELPERS
	 * ========================================================================== */

	/**
	 * Get the post author.
	 *
	 * @since 5.3.0
	 * @param array $cf7_form_data The submitted form data.
	 * @return int Author ID.
	 */
	private function get_post_author( $cf7_form_data ) {
		$author = 1;

		if ( isset( $_POST['_map_author'] ) && is_numeric( $_POST['_map_author'] ) ) {
			$author = intval( $_POST['_map_author'] );
		} else {
			// Try to get user from form mail recipient.
			$mail = get_post_meta( $this->cf7_post_id, '_mail', true );
			if ( ! empty( $mail ) && isset( $mail['recipient'] ) ) {
				$user = get_user_by( 'email', $mail['recipient'] );
				if ( $user ) {
					$author = $user->ID;
				}
			}
		}

		return $author;
	}

	/**
	 * Get the post status.
	 *
	 * @since 5.3.0
	 * @param bool  $is_submitted   Whether the form was submitted.
	 * @param array $cf7_form_data  The submitted form data.
	 * @return string Post status.
	 */
	private function get_post_status( $is_submitted, $cf7_form_data ) {
		$post_status = 'draft';

		if ( $is_submitted ) {
			/**
			 * Filter the post status of the custom post created when a form is submitted.
			 *
			 * @since 2.0.2
			 * @param string $status  The post status. Default 'draft'.
			 * @param string $cf7_key The unique key to identify the form.
			 * @param array  $data    Array of key value pairs of submitted form fields.
			 * @return string The post status required.
			 */
			$post_status = apply_filters(
				'cf7_2_post_status_' . $this->post_properties['type'],
				$post_status,
				$this->cf7_key,
				$cf7_form_data
			);
		}

		return $post_status;
	}

	/**
	 * Get the post title.
	 *
	 * @since 5.3.0
	 * @return string Post title.
	 */
	private function get_post_title() {
		$post_type = $this->post_properties['type'];
		$post_title = 'CF7 2 Post';

		/**
		 * Filter to set the default title for a mapped post.
		 *
		 * @since 3.6.0
		 * @param string $post_title Default title to set.
		 * @param string $post_type  The post type being mapped to.
		 * @param string $cf7_key    The unique key to identify the form.
		 * @return string The default title.
		 */
		return apply_filters( 'cf72post_default_post_title', $post_title, $post_type, $this->cf7_key );
	}

	/**
	 * Get existing post ID from request.
	 *
	 * @since 5.3.0
	 * @return int|false Post ID or false.
	 */
	private function get_existing_post_id() {
		// Check for Stripe payment intent.
		/** NB @since 5.5 integrate Stripe payment */
		if ( isset( $_POST['_wpcf7_stripe_payment_intent'] ) && 
			 empty( $_POST['_wpcf7_stripe_payment_intent'] ) && 
			 isset( $_POST['_cf72post_nonce'] ) ) {
			return get_transient( sanitize_key( $_POST['_cf72post_nonce'] ) );
		}

		// Check for existing post ID.
		/** NB @since 6.3 stop exposing the post ID to the user and cache it as a transient for 24 hours */
		if ( isset( $_POST['map_post_id'] ) && ! empty( $_POST['map_post_id'] ) ) {
			$post_id = get_transient( sanitize_key( $_POST['map_post_id'] ) );
			if ( false !== $post_id ) {
				return $post_id;
			}else{
				$this->log_error( 'Post ID not found in transient, likely expired.' );
			}
		}

		return false;
	}

	/**
	 * Process post fields mapping.
	 *
	 * @since 5.3.0
	 * @param array             $post        The post data array.
	 * @param array             $cf7_form_data The submitted form data.
	 * @param WPCF7_Submission $submission   The CF7 submission object.
	 * @return bool True if any post fields were processed.
	 */
	private function process_post_fields( &$post, $cf7_form_data, $submission ) {
		$has_post_fields = false;

		foreach ( $this->post_map_fields as $form_field => $post_field ) {
			$post_key = '';
			$skip_loop = false;

			switch ( $post_field ) {
				case 'title':
				case 'author':
				case 'excerpt':
					$post_key = 'post_' . $post_field;
					break;
				case 'editor':
					$post_key = 'post_content';
					break;
				case 'slug':
					$post_key = 'post_name';
					break;
				case 'thumbnail':
					$has_post_fields = $this->process_thumbnail_field( $post, $form_field, $submission );
					$skip_loop = true;
					break;
			}

			if ( $skip_loop ) {
				continue;
			}

			if ( empty( $post_key ) ) {
				continue;
			}

			if ( 0 === strpos( $form_field, 'cf7_2_post_filter-' ) ) {
				$post[ $post_key ] = apply_filters( $form_field, '', $post['ID'], $cf7_form_data, $this->cf7_key );
				$has_post_fields = true;
			} elseif ( isset( $cf7_form_data[ $form_field ] ) ) {
				$submitted = $cf7_form_data[ $form_field ];
				
				/**
				 * Filter for plugin developers to map custom plugin tag fields.
				 *
				 * @since 3.1.0
				 * @param mixed  $submitted  Submitted value for the field.
				 * @param string $field_name The field name.
				 * @return mixed Value to store for the field.
				 */
				$submitted = apply_filters(
					'cf7_2_post_saving_tag_' . $this->cf7_form_fields[ $form_field ],
					$submitted,
					$form_field
				);

				if ( is_array( $submitted ) ) {
					$post[ $post_key ] = implode( ',', $submitted );
				} else {
					$post[ $post_key ] = $submitted;
				}
				$has_post_fields = true;
			}
		}

		return $has_post_fields;
	}

	/**
	 * Process thumbnail field.
	 *
	 * @since 5.3.0
	 * @param array             $post        The post data array.
	 * @param string            $form_field  The form field name.
	 * @param WPCF7_Submission $submission   The CF7 submission object.
	 * @return bool True if thumbnail was processed.
	 */
	private function process_thumbnail_field( &$post, $form_field, $submission ) {
		$files = array();
		$cf7_files = $submission->uploaded_files();
		if( isset( $cf7_files[$form_field] ) ){
			$files = $cf7_files[$form_field];
		}

		$file_url = array();
		foreach ( $files as $path ) {
			if ( ! file_exists( $path ) ) {
				continue;
			}
			$filename = explode( '/', $path );
			$filename = $filename[ count( $filename ) - 1 ];
			$file_arr = array(
				'name'     => $filename,
				'tmp_name' => $path,
			);
			$attachment_id = $this->save_file_as_attachment( $file_arr, $post['ID'] );
			if ( ! is_wp_error( $attachment_id ) ) {
				set_post_thumbnail( $post['ID'], $attachment_id );
			} else {
				$this->log_error( 'Unable to save Media attachment file: ' . $filename );
			}
		}

		return true;
	}

	/**
	 * Process meta fields mapping.
	 *
	 * @since 5.3.0
	 * @param int               $post_id       The post ID.
	 * @param array             $cf7_form_data The submitted form data.
	 * @param WPCF7_Submission $submission    The CF7 submission object.
	 * @return void
	 */
	private function process_meta_fields( $post_id, $cf7_form_data, $submission ) {
		foreach ( $this->post_map_meta_fields as $form_field => $post_field ) {
			if ( 0 === strpos( $form_field, 'cf7_2_post_filter-' ) ) {
				$value = apply_filters( $form_field, '', $post_id, $cf7_form_data, $this->cf7_key );
				update_post_meta( $post_id, $post_field, $value );
				continue;
			}

			// Skip if field doesn't exist in form.
			if ( ! isset( $this->cf7_form_fields[ $form_field ] ) ) {
				continue;
			}

			// Handle file fields.
			if ( 'file' === $this->cf7_form_fields[ $form_field ] ) {
				$this->process_file_meta_field( $post_id, $form_field, $post_field, $cf7_form_data, $submission );
				continue;
			}

			// Handle regular fields.
			if ( isset( $cf7_form_data[ $form_field ] ) ) {
				$submitted = $cf7_form_data[ $form_field ];
				
				/**
				 * Filter for plugin developers to map custom plugin tag fields.
				 *
				 * @since 3.1.0
				 * @param mixed  $submitted  Submitted value for the field.
				 * @param string $field_name The field name.
				 * @return mixed Value to store for the field.
				 */
				$submitted = apply_filters(
					'cf7_2_post_saving_tag_' . $this->cf7_form_fields[ $form_field ],
					$submitted,
					$form_field
				);

				update_post_meta( $post_id, $post_field, $submitted );
			}
		}
	}

	/**
	 * Process file meta field.
	 *
	 * @since 5.3.0
	 * @param int               $post_id       The post ID.
	 * @param string            $form_field    The form field name.
	 * @param string            $post_field    The post meta field name.
	 * @param array             $cf7_form_data The submitted form data.
	 * @param WPCF7_Submission $submission    The CF7 submission object.
	 * @return void
	 */
	private function process_file_meta_field( $post_id, $form_field, $post_field, $cf7_form_data, $submission ) {
		$files = array();
		$cf7_files = $submission->uploaded_files();
		if(  isset( $cf7_form_data[ $form_field ] ) && ! empty( $cf7_form_data[ $form_field ] )  && isset( $cf7_files[$form_field] ) ){
			$files = $cf7_files[$form_field];
		}

		$file_url = array();
		foreach ( $files as $path ) {
			if ( ! file_exists( $path ) ) {
				continue;
			}
			$filename = explode( '/', $path );
			$filename = $filename[ count( $filename ) - 1 ];
			$file_arr = array(
				'name'     => $filename,
				'tmp_name' => $path,
			);
			$attachment_id = $this->save_file_as_attachment( $file_arr, 0 );
			if ( ! is_wp_error( $attachment_id ) ) {
				$file_url = wp_get_attachment_url( $attachment_id );
			} else {
				$this->log_error( 'Unable to save Media attachment file: ' . $filename );
			}
		
		
			/**
			 * Filter the file URL for meta fields.
			 *
			 * @since 5.3.0
			 * @param string $file_url      The file URL.
			 * @param int    $attachment_id The attachment ID.
			 * @param int    $post_id       The post ID.
			 * @param string $post_field    The meta field name.
			 * @param string $form_field    The form field name.
			 * @param string $cf7_key       The form key.
			 * @return string The filtered file URL.
			 */
			$file_url = apply_filters(
				'cf7_2_post_metafield_file',
				$file_url,
				$attachment_id ?? 0,
				$post_id,
				$post_field,
				$form_field,
				$this->cf7_key
			);

			update_post_meta( $post_id, $post_field, $file_url );
		}
	}
	
	/**
	 * Process taxonomy fields mapping.
	 *
	 * @since 5.3.0
	 * @param int   $post_id       The post ID.
	 * @param array $cf7_form_data The submitted form data.
	 * @return void
	 */
	private function process_taxonomy_fields( $post_id, $cf7_form_data ) {
		$value = array();

		foreach ( $this->post_map_taxonomy as $form_field => $taxonomy ) {
			if ( ! isset( $value[ $taxonomy ] ) ) {
				$value[ $taxonomy ] = array();
			}

			if ( 0 === strpos( $form_field, 'cf7_2_post_filter-' ) ) {
				$value[ $taxonomy ] = apply_filters( $form_field, $value[ $taxonomy ], $post_id, $cf7_form_data, $this->cf7_key );
			} elseif ( isset( $cf7_form_data[ $form_field ] ) ) {
				$field_value = $cf7_form_data[ $form_field ];
				if ( is_array( $field_value ) ) {
					$value[ $taxonomy ] = array_merge( $value[ $taxonomy ], array_map( 'intval', $field_value ) );
				} else {
					$value[ $taxonomy ] = array_merge( $value[ $taxonomy ], array_map( 'intval', array( $field_value ) ) );
				}
			}
		}

		foreach ( $value as $taxonomy => $terms ) {
			$term_taxonomy_ids = wp_set_object_terms( $post_id, $terms, $taxonomy );
			if ( is_wp_error( $term_taxonomy_ids ) ) {
				$this->log_error( 'Unable to set taxonomy (' . $taxonomy . ') terms: ' . $term_taxonomy_ids->get_error_message() );
			}
		}
	}

	/**
	 * Trigger submission actions.
	 *
	 * @since 5.3.0
	 * @param int               $post_id       The post ID.
	 * @param array             $cf7_form_data The submitted form data.
	 * @param WPCF7_Submission $submission    The CF7 submission object.
	 * @param bool              $is_submitted  Whether the form was submitted.
	 * @return void
	 */
	private function trigger_submission_actions( $post_id, $cf7_form_data, $submission, $is_submitted ) {
		/**
		 * General action for other plugins to hook custom functionality.
		 *
		 * @since 2.0.0
		 * @param int    $post_id              The post ID.
		 * @param string $cf7_key              The unique form key.
		 * @param array  $post_map_fields      Form fields mapped to post fields.
		 * @param array  $post_map_meta_fields Form fields mapped to meta fields.
		 * @param array  $cf7_form_data        Submitted form data.
		 * @param array  $uploaded_files       Uploaded files.
		 */
		do_action(
			'cf7_2_post_form_posted',
			$post_id,
			$this->cf7_key,
			$this->post_map_fields,
			$this->post_map_meta_fields,
			$cf7_form_data,
			$submission->uploaded_files()
		);

		if ( $is_submitted ) {
			/**
			 * Action for submitted (not draft) forms.
			 *
			 * @since 3.3.0
			 */
			do_action(
				'cf7_2_post_form_submitted_to_' . $this->post_properties['type'],
				$post_id,
				$cf7_form_data,
				$this->cf7_key,
				$submission->uploaded_files()
			);
		}
	}

	/**
	 * Store submission transient for redirect.
	 *
	 * @since 5.3.0
	 * @param int $post_id The post ID.
	 * @return void
	 */
	private function store_submission_transient( $post_id ) {
		if ( isset( $_POST['_cf72post_nonce'] ) && ! empty( $_POST['_cf72post_nonce'] ) ) {
			/**
			 * Filter the transient expiration time.
			 *
			 * @since 3.1.0
			 * @param int    $time   Time in seconds. Default 300.
			 * @param string $cf7_key The form key.
			 * @return int The expiration time.
			 */
			$time = apply_filters( 'cf7_2_post_transient_submission_expiration', 300, $this->cf7_key );
			if ( ! is_numeric( $time ) ) {
				$time = 300;
			}
			set_transient( sanitize_key( $_POST['_cf72post_nonce'] ), $post_id, $time );
		}
	}

	/* ==========================================================================
	 * FILE ATTACHMENT METHOD
	 * ========================================================================== */

	/**
	 * Save a file as a media attachment.
	 *
	 * Copies the CF7 uploaded file to the WordPress uploads folder as a media attachment post.
	 *
	 * @since 6.0.1
	 * @param array  $file_arr File array containing the filename and path.
	 * @param int    $post_id  The parent post ID to which to attach the media.
	 * @return int|WP_Error Attachment ID or WP_Error on failure.
	 */
	private function save_file_as_attachment( $file_arr, $post_id ) {
		
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$action = 'c2p_file_copy';

		// Add filter to handle file copy instead of move.
		add_filter(
			"{$action}_prefilter",
			function( $file ) {
				add_filter(
					'pre_move_uploaded_file',
					function( $move, $file, $new_file, $type ) {
						// Copy the file instead of moving.
						$move = @copy( $file['tmp_name'], $new_file );
						if ( false === $move && WP_DEBUG ) {
							trigger_error(
								esc_html(
									sprintf(
										/* translators: %1$s: source path, %2$s: destination path */
										__( 'Unable to copy uploaded %1$s to %2$s', 'post-my-contact-form-7' ),
										$file['tmp_name'],
										$new_file
									)
								),
								E_USER_NOTICE
							);
						}
						return $move;
					},
					10,
					4
				);
				return $file;
			},
			10,
			1
		);

		$new_file_arr = wp_handle_sideload(
			$file_arr,
			array(
				'action'    => $action,
				'test_form' => false,
			),
			current_time( 'mysql' )
		);

		if ( isset( $new_file_arr['error'] ) ) {
			return new WP_Error( 'upload_error', $new_file_arr['error'] );
		}

		$url      = $new_file_arr['url'];
		$type     = $new_file_arr['type'];
		$new_file = $new_file_arr['file'];
		$title    = preg_replace( '/\.[^.]+$/', '', wp_basename( $new_file ) );
		$content  = '';

		// Use image EXIF/IPTC data for title and caption if possible.
		$image_meta = wp_read_image_metadata( $new_file );
		if ( $image_meta ) {
			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
				$title = $image_meta['title'];
			}
			if ( trim( $image_meta['caption'] ) ) {
				$content = $image_meta['caption'];
			}
		}

		// Construct the attachment array.
		$attachment = array(
			'post_mime_type' => $type,
			'guid'           => $url,
			'post_parent'    => $post_id,
			'post_title'     => $title,
			'post_content'   => $content,
		);

		// Save the attachment.
		$attachment_id = wp_insert_attachment( $attachment, $new_file, $post_id, true );

		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $new_file )
			);
		}

		return $attachment_id;
	}

	/* ==========================================================================
		* HELPER METHODS
		* ========================================================================== */

	/**
	 * Log an error message.
	 *
	 * @since 5.3.0
	 * @param string $message The error message.
	 * @return void
	 */
	protected function log_error( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'C2P_Post_Mapper: ' . $message );
			wpg_debug( $message, 'C2P_Post_Mapper: ', 10 );
		}
	}

	/* ==========================================================================
	 * ABSTRACT METHODS
	 * ========================================================================== */

	/**
	 * Set post properties.
	 *
	 * This abstract method must be defined by child classes.
	 * Existing system posts don't need properties to be set/tracked
	 * as they are defined elsewhere.
	 *
	 * @since 5.0.0
	 * @return void
	 */
	abstract protected function set_post_properties();
}