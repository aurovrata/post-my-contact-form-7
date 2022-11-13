<?php
require_once plugin_dir_path( __FILE__ ) . 'mapper/class-cf7-2-custom-post-mapper.php';
require_once plugin_dir_path( __FILE__ ) . 'mapper/class-cf7-2-system-post-mapper.php';

class CF72Post_Mapping_Factory {
  /**
	 * cache of Form_2_Post_Mapper objects for loaded forms.
	 *
	 * @since    5.0.0
	 * @access    protected
	 * @var      array    $post_mappers    an array of Form_2_Post_Mapper objects..
	 */
  protected $post_mappers;

  /**
	 * track post types mapped to create and add dashboard functionality.
	 *
	 * @since    5.0.0
	 * @access    protected
	 * @var      array    $mapped_post_types    an array of fomr IDs=>post types..
	 */
  protected static $mapped_post_types;
  /**
	 * Factory object.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      CF72Post_Mapping_Factory  $factory object instance of this class.
	 */
  protected static $factory;
  /**
   * Default Construct a Cf7_2_Post_Factory object.
   *
   * @since    1.0.0
   * @param    int    $cf7_post_id the ID of the CF7 form.
   */
  protected function __construct(){
    $this->post_mappers = array();
    if(is_admin()) $this->get_system_posts(); //only used in dashboard.

  }
  public static function get_factory(){
    if(!isset(self::$factory)) self::$factory = new self();
    return self::$factory;
  }
  /**
  * set system posts
  *
  *@since 5.0.0
  *@return Array associative array of system post_types=>post label.
  */
  protected function get_system_posts(){
    if(!is_admin()) return false;

    $args = array(
     'show_ui'   => true
    );
    $post_types = get_post_types( $args, 'objects', 'and' );
    $html = '';
    $display = array();
    foreach($post_types as $post_type){
      switch($post_type->name){
        case 'wp_block':
        case 'wpcf7_contact_form':
          break;
        default:
          $display[$post_type->name] = $post_type->label;
          break;
      }
    }
    /**
    * add/remove system posts to which to map forms to. By defualt the plugin only lists system posts which are visible in the dashboard
    * @since 2.0.0
    * @param array $display  list of system post picked up by the plugin to display
    * @return array an array of post-types=>post-label key value pairs to display
    */
    return apply_filters('cf7_2_post_display_system_posts', $display);
  }
  /**
   *Get a list of available system post_types as <option> elements
   *
   * @since 1.3.0
   * @return     String    html list of <option> elements with existing post_types in the DB
  **/
  public function get_system_posts_options($selected){
    $system_pt = $this->get_system_posts();
    if(!isset($system_pt[$selected])) $selected = 'post';

    $html='';
    //debug_msg($display);
    foreach($system_pt as $post_type=>$post_label){
      $select = ($selected == $post_type) ? ' selected="true"':'';
      $html .='<option value="' . $post_type . '"' . $select . '>';
      $html .= $post_label . ' ('.$post_type.')';
      $html .='</option>' . PHP_EOL;
    }
    return $html;
  }
  /**
	 * Get a factory object for a CF7 form.
	 *
	 * @since    5.0.0
   * @param  int  $cf7_post_id  cf7 post id
   * @return Form_2_Post_Mapper  a factory oject
   */
  public function get_post_mapper( $cf7_post_id ){
    if($cf7_post_id===0){
      $mapper =  new Form_2_Custom_Post($cf7_post_id, $this);
      $map = $this->get_default_mapping();
      $mapper->init_default($map['type'],$map['name'],$map['names']);
      $mapper->init_default_mapping($map);
      return $mapper;
    }
    // $this = self::get_factory();
    //if mapper exists, return it.
    if(isset($this->post_mappers[$cf7_post_id])){
      return $this->post_mappers[$cf7_post_id];
    }
    //check if the cf7 form already has a mapping
    $post_type = get_post_meta($cf7_post_id,'_cf7_2_post-type',true);
    $post_type_source = 'factory';
    $mapper = null;
    $form = get_post($cf7_post_id);
    //debug_msg('type='.$post_type);
    if(empty($post_type)){ //let's create a new one
      $plural_name = $singular_name = 'Undefined';
      $slug = 'undefined';
      if(isset($form)){
        $plural_name = $singular_name = $form->post_title;
        if( 's'!= substr($plural_name,-1) ) $plural_name.='s';
        $slug = $form->post_name;
      }
      $mapper = new Form_2_Custom_Post($cf7_post_id, $this);
      $mapper->cf7_key = $post_type;
      $mapper->init_default($slug,$singular_name,$plural_name);
    }else{
      $post_type_source = get_post_meta($cf7_post_id,'_cf7_2_post-type_source',true);
      $map = get_post_meta($cf7_post_id,'_cf7_2_post-map',true);
      if( isset($this->post_mappers[$cf7_post_id]) ){
        $mapper = $this->post_mappers[$cf7_post_id];
      }else{
        switch($post_type_source){
          case 'system':
            $mapper = new Form_2_System_Post($cf7_post_id, $this);
            break;
          case 'factory':
            $mapper = new Form_2_Custom_Post($cf7_post_id, $this);
        }
        $mapper->load_post_mapping($form->post_name); //load DB values
        /** @since 3.2.0 get the form terms if any */
        $terms = wp_get_post_terms( $cf7_post_id, 'wpcf7_type', array('fields'=>'id=>slug') );
        if(!is_wp_error( $terms )){
          $mapper->form_terms = $terms;
        }
      }
     }
     return $mapper;
   }
   /**
   * Setup a default mapping for new post mappers.
   *
   *@since 5.0.0
   *@return Array array of mapping arguments.
   */
   protected function get_default_mapping(){
     return array(
       'type'=>apply_filters('c2p_default_new_form_post_type_mapping','contact_form'),
       'name'=>apply_filters('c2p_default_new_form_post_name_mapping','Contact Form'),
       'names'=>apply_filters('c2p_default_new_form_post_names_mapping','Contact Forms'),
       'post'=> apply_filters('c2p_default_new_form_post_mapping', array(
         'title'=>'your-name',
         'excerpt'=>'your-subject',
         'editor'=>'your-message'
       )),
       'meta'=> apply_filters('c2p_default_new_form_postmeta_mapping', array(
         'contact-email'=>'your-email',
       ))
     );
   }
   /**
   * Track mappers.
   *
   *@since 5.0.0
   *@param Form_2_Post_Mapper $mapper mapper object.
   */
   public function register($mapper){
     $this->post_mappers[$mapper->form_ID()] = $mapper;
   }
  /**
   *Enqueue the localised script
   *This function is called by the hook in the
   * @since 1.3.0
   * @param      string    $p1     .
   * @return     string    $p2     .
  **/
  public function enqueue_localised_script($handle, $field_and_values = array()){
    $values = array_diff($field_and_values, $this->localise_values);
    wp_localize_script($handle, 'cf7_2_post_local', $values);
  }

  /**
	 * Store the mapping in the CF7 post & create the custom post mapping.
	 * This is
	 * @since    5.0.0
   * @return  boolean   true if successful
   */
  public function save($post_id){
    $mapped = false;
    if( isset($_POST['mapped_post_type_source']) ) {
      $source = sanitize_text_field($_POST['mapped_post_type_source']);
      $mapper=null;
      switch($source){
        case 'system':
          $mapper = new Form_2_System_Post($post_id, $this);
          break;
        case 'factory':
          $mapper = new Form_2_Custom_Post($post_id, $this);
          break;
      }
      if(isset($mapper) && is_a($mapper, 'Form_2_Post_Mapper' )) $mapped = $mapper->save_mapping();
      else{
        debug_msg('CF&_2_POST ERROR: Unable to determine mapped_post_type_source while saving');
      }
    }else{
      debug_msg('CF&_2_POST ERROR: mapped_post_type_source missing, unable to save.');
    }
    //
    return $mapped;
  }


   /**
 	 * Get the CF7 post id.
 	 *
 	 * @since    1.0.0
   * @return int the cf7 form post ID
    */
   public function get_cf7_post_id(){
     return $this->cf7_post_ID;
   }

  /**
  * Register Custom Post Type based on CF7 mapped properties
  *
  * @since 1.0.0
  * @param Form_2_Post_Mapper $mapper mapper object.
  */
  protected function create_cf7_post_type(Form_2_Post_Mapper $mapper) {
    //register any custom taxonomy
    if( !empty($mapper->post_properties['taxonomy']) ){
      foreach($mapper->post_properties['taxonomy'] as $taxonomy_slug){
        if('system' == $mapper->taxonomy_properties[$taxonomy_slug]['source']){
          continue;
        }
        $taxonomy = array(
      		'hierarchical'               => true,
      		'public'                     => true,
      		'show_ui'                    => true,
      		'show_admin_column'          => true,
      		'show_in_nav_menus'          => true,
      		'show_tagcloud'              => true,
          'show_in_quick_edit'         => true,
          'menu_name'                  => $mapper->taxonomy_properties[$taxonomy_slug]['name'],
          'description'                =>'',
      	);
        //debug_msg($mapper->taxonomy_properties[$taxonomy_slug]," taxonomy properties: ".$taxonomy_slug);
        $taxonomy =  array_merge( $mapper->taxonomy_properties[$taxonomy_slug], $taxonomy );
        $taxonomy_filtered = apply_filters('cf7_2_post_filter_taxonomy_registration-'.$taxonomy_slug, $taxonomy);
        //ensure we have all the key defined.
        $taxonomy =  $taxonomy_filtered + $taxonomy; //this will give precedence to filtered keys, but ensure we have all required keys.
        $this->register_custom_taxonomy($taxonomy, $mapper);
      }
    }
  	$labels = array(
  		'name'                  => $mapper->post_properties['plural_name'],
  		'singular_name'         => $mapper->post_properties['singular_name'],
  		'menu_name'             => $mapper->post_properties['plural_name'],
  		'name_admin_bar'        => $mapper->post_properties['singular_name'],
  		'archives'              => $mapper->post_properties['singular_name'].' Archives',
  		'parent_item_colon'     => 'Parent '.$mapper->post_properties['singular_name'].':',
  		'all_items'             => 'All '.$mapper->post_properties['plural_name'],
  		'add_new_item'          => 'Add New '.$mapper->post_properties['singular_name'],
  		'add_new'               => 'Add New',
  		'new_item'              => 'New '.$mapper->post_properties['singular_name'],
  		'edit_item'             => 'Edit '.$mapper->post_properties['singular_name'],
  		'update_item'           => 'Update '.$mapper->post_properties['singular_name'],
  		'view_item'             => 'View '.$mapper->post_properties['singular_name'],
  		'search_items'          => 'Search '.$mapper->post_properties['singular_name'],
  		'not_found'             => 'Not found',
  		'not_found_in_trash'    => 'Not found in Trash',
  		'featured_image'        => 'Featured Image',
  		'set_featured_image'    => 'Set featured image',
  		'remove_featured_image' => 'Remove featured image',
  		'use_featured_image'    => 'Use as featured image',
  		'insert_into_item'      => 'Insert into '.$mapper->post_properties['singular_name'],
  		'uploaded_to_this_item' => 'Uploaded to this '.$mapper->post_properties['singular_name'],
  		'items_list'            => $mapper->post_properties['plural_name'].' list',
  		'items_list_navigation' => $mapper->post_properties['plural_name'].' list navigation',
  		'filter_items_list'     => 'Filter '.$mapper->post_properties['plural_name'].' list',
  	);
    //labels can be modified post taxonomy registratipn
    //ensure author is supported,
    if(!isset($mapper->post_properties['supports']['author'])) $mapper->post_properties['supports'][]='author';
  	$args = array(
  		'label'                 => $mapper->post_properties['singular_name'],
  		'description'           => 'Post for CF7 Form '. $mapper->post_properties['cf7_title'],
  		'labels'                => $labels,
      'supports'              => apply_filters('cf7_2_post_supports_'.$mapper->post_properties['type'], $mapper->post_properties['supports']),
  		'taxonomies'            => $mapper->post_properties['taxonomy'],
  		'hierarchical'          => !empty($mapper->post_properties['hierarchical']),
  		'public'                => !empty($mapper->post_properties['public']),
  		'show_ui'               => !empty($mapper->post_properties['show_ui']),
  		'show_in_menu'          => !empty($mapper->post_properties['show_in_menu']),
  		'menu_position'         => $mapper->post_properties['menu_position'],
  		'show_in_admin_bar'     => !empty($mapper->post_properties['show_in_admin_bar']),
  		'show_in_nav_menus'     => !empty($mapper->post_properties['show_in_nav_menus']),
  		'can_export'            => !empty($mapper->post_properties['can_export']),
  		'has_archive'           => !empty($mapper->post_properties['has_archive']),
  		'exclude_from_search'   => !empty($mapper->post_properties['exclude_from_search']),
  		'publicly_queryable'    => !empty($mapper->post_properties['publicly_queryable']),
  	);
    $reference = array(
      'edit_post' => '',
      'edit_posts' => '',
      'edit_others_posts' => '',
      'publish_posts' => '',
      'read_post' => '',
      'read_private_posts' => '',
      'delete_post' => ''
    );
    $capabilities = array_filter(apply_filters('cf7_2_post_capabilities_'.$mapper->post_properties['type'], $reference));
    $diff=array_diff_key($reference, $capabilities);
    if( empty( $diff ) ) {
      $args['capabilities'] = $capabilities;
      $args['map_meta_cap'] = true;
    }else{ //some keys are not set, so capabilities will not work
      //set to defaul post capabilities
      $args['capability_type'] = 'post';
    }

    //allow additional settings
    $args = apply_filters('cf7_2_post_register_post_'.$mapper->post_properties['type'], $args );
  	register_post_type( $mapper->post_properties['type'], $args );
    //link the taxonomy and the post
    foreach($mapper->post_properties['taxonomy'] as $taxonomy_slug){
      register_taxonomy_for_object_type( $taxonomy_slug, $mapper->post_properties['type'] );
    }
  }
  /**
  * Return the post_types to which forms are mapped
  *@since 3.4.0
  *@return array $cf7_post_id=>array($psot_type=>[factory|system|filter]) key value pairs
  */
  public static function get_mapped_post_types(){
    if(isset(self::$mapped_post_types)){
      return self::$mapped_post_types;
    }
    global $wpdb;
    $cf7_posts = $wpdb->get_results(
      "SELECT pm.post_id AS ID, pm.meta_value AS origin, pt.meta_value as type FROM $wpdb->postmeta pm
        INNER JOIN $wpdb->postmeta pt ON pt.post_id = pm.post_id INNER JOIN $wpdb->posts p ON p.ID=pm.post_id
        WHERE pm.meta_key='_cf7_2_post-type_source'
        AND pt.meta_key='_cf7_2_post-type'
        AND p.post_status like 'publish'"
    );
    self::$mapped_post_types = array();
    foreach($cf7_posts as $post){
      if('filter'==$post->origin) continue; //skip as not mapped by plugin.
      self::$mapped_post_types[$post->ID]=array($post->type=>$post->origin);
    }
    return self::$mapped_post_types;
  }
  /**
  * Function to check post types to which forms have been mapped.
  *
  *@since 3.4.0
  *@param string $post_type post type to check
  *@param string $source origin of post, default is 'factory', ie the origin is this class.
  *@return mixed form post_ID or false is not mapped.
  */
  public function is_mapped_post_types($post_type, $source=null){
    $is_mapped = false;
    if(isset(self::$mapped_post_types)){
      foreach(self::$mapped_post_types as $post_id=>$type){
        $ptype = key($type);
        if($post_type == $ptype){
          if(empty($source)){
            $is_mapped = $post_id;
          }else if( $source == $type[$ptype] ){
            $is_mapped = $post_id;
          }
        }
      }
    }
    return $is_mapped;
  }
  /**
  * Update the mapped post types when their status change.
  * @since 3.4.0.
  * @param $cf7_post_id form post id.
  * @param $status mapping status, publish|draft|delete, defaults to delete.
  */
  public static function update_mapped_post_types($cf7_post_id, $status='delete'){
    switch($status){
      case 'delete':
        unset( self::$mapped_post_types[$cf7_post_id] );
        break;
      case 'publish':
        update_post_meta($cf7_post_id, '_cf7_2_post-map', $status);
        $type = get_post_meta($cf7_post_id, '_cf7_2_post-type', true);
        $source = get_post_meta($cf7_post_id, '_cf7_2_post-type_source', true);
        self::$mapped_post_types[$cf7_post_id]=array($type, $source);
        break;
      case 'draft':
        update_post_meta($cf7_post_id, '_cf7_2_post-map', $status);
        unset( self::$mapped_post_types[$cf7_post_id] );
        break;
    }
  }
  /**
  * Dynamically registers new custom post.
  * Hooks 'init' action.
  * @since 1.0.0
  */
  public function register_cf7_post_maps(){
    $cf7_post_ids = self::get_mapped_post_types();
    foreach($cf7_post_ids as $post_id=>$type){
      $system = true;
      $post_type = key($type);
      $mapper = $this->get_post_mapper($post_id);
      switch($type[$post_type]){
        case 'factory':
          $this->create_cf7_post_type($mapper);
          /**
          * Flush the permalink rules to ensure the public posts are visible on the front-end.
          * @since 3.8.2.
          */
          if($mapper->flush_permalink_rules){
            flush_rewrite_rules();
            update_post_meta($post_id,'_cf7_2_post_flush_rewrite_rules', false);
            $mapper->flush_permalink_rules = false;
          }
          $system = false;
          break;
        case 'system': /** @since 3.3.1 link system taxonomy*/
          //link the taxonomy and the post
          $taxonomies = get_post_meta($post_id, '_cf7_2_post-taxonomy', true);
          foreach($taxonomies as $taxonomy_slug){
            register_taxonomy_for_object_type( $taxonomy_slug, $post_type );
          }
          break;
      }
      /**
      * action to notify other plugins for mapped post creation
      * @since 2.0.4
      * @param string $post_type   the post type being mapped to
      * @param boolean $system   true if form is mapped to an existing post, false if it is being registered by this plugin.
      * @param string $cf7_key   the form key value which is being mapped to the post type
      * @param string $post_id   the form post ID value which is being mapped to the post type
      */
      do_action('cf72post_register_mapped_post', $post_type, $system, $mapper->cf7_key, $post_id);
      //add a filter for newly saved posts of this type.
      add_action('save_post_'.$post_type, function($post_id, $post, $update){
        if($update) return $post_id;
        $cf7_flag = get_post_meta($post_id, '_cf7_2_post_form_submitted', true);
        if(empty($cf7_flag)){ /** @since 4.1.9 default to yes */
          update_post_meta($post_id, '_cf7_2_post_form_submitted', 'yes');
        }
        return $post_id;
      }, 10,3);
    }
  }

  /**
  * Checks if a form mapping is published
  * @since 2.0.0
  */
  public function is_mapped($cf7_post_ID){
    $map = get_post_meta($cf7_post_ID, '_cf7_2_post-map', true);
    switch($map){
      case 'draft':
      case 'publish':
        return true;
      default:
        return false;
    }
  }
  /**
  * Checks if a form mapping is published
  * @since 2.0.0
  * @param int $cf7_post_ID form ID
  * @return boolean true if mapping is live and accpeting submissions.
  */
  public function is_live($cf7_post_ID){
    $map = get_post_meta($cf7_post_ID, '_cf7_2_post-map', true);
    switch($map){
      case 'publish':
        return true;
      default:
        return false;
    }
  }
  /**
  * Backward compatibility for filtered mappings.
  *
  * @since 5.4.3
  * @param int $cf7_post_ID form ID
  * @return boolean true if mapped using a filter.
  */
  public function is_filter($cf7_post_ID){
    return 'filter' === get_post_meta($cf7_post_ID, '_cf7_2_post-type_source',true);
  }
  /**
  * Builds a set of field=>value pairs to pre-populate a mapped form
  * Called by Cf7_2_Post_Public::load_cf7_script()
  * @since 1.3.0
  * @param   Int  $cf7_2_post_id   a specific post to which this form submission is mapped/saved
  * @return    Array  cf7 form field=>value pairs.
  */
  public function get_form_values($form_id, $cf7_2_post_id=''){
    //is user logged in?
    $load_saved_values = false;
    $post=null;
    $mapper = $this->get_post_mapper($form_id);
    $field_and_values = array();
    $unmapped_fields = array();
    $mapper->load_form_fields(); //this loads the cf7 form fields and their type

    //find out if this user has a post already created/saved
    $args = array(
    	'posts_per_page'   => 1,
    	'post_type'        => $mapper->post_properties['type'],
    	'post_status'      => 'any'
    );
    if(!empty($cf7_2_post_id)){ //search for the sepcific mapped/saved post
      $args['post__in']=array($cf7_2_post_id);
    }
    //filter by submission value for newer version so as not to break older version
    if( version_compare( CF7_2_POST_VERSION , $mapper->post_properties['version'] , '>=') ){
      $args['meta_query'] = array(
  		array(
  			'key'     => '_cf7_2_post_form_submitted',
  			'value'   => 'no',
  			'compare' => 'LIKE',
  		));
    }
    if(is_user_logged_in()){ //let's see if this form is already mapped for this user
      $user = wp_get_current_user();
      $args['author'] = $user->ID;
    }else $args = array();

    $args = apply_filters('cf7_2_post_filter_user_draft_form_query', $args, $mapper->post_properties['type'], $mapper->cf7_key);

    if(!empty($args)){
      $posts_array = get_posts( $args );
      // debug_msg($args, "looking for posts.... found, ".sizeof($posts_array));
      if(!empty($posts_array)){
        $post = $posts_array[0];
        $load_saved_values = true;
        $field_and_values['map_post_id']= $post->ID;
        wp_reset_postdata();
      }
    }

    //we now need to load the save meta field values
    foreach($mapper->get_post_map_fields() as $form_field => $post_field){
      $post_key ='';
      $post_value = '';
      $skip_loop = false;
      //if the value was filtered, let's skip it
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        continue;
      }

      switch($post_field){
        case 'title':
        case 'author':
        case 'excerpt':
          $post_key = 'post_'.$post_field;
          break;
        case 'editor':
          $post_key ='post_content';
          break;
        case 'slug':
          $post_key ='post_name';
          break;
        case 'thumbnail': 
          break;
      }
      if($load_saved_values && !empty($post_key)) {
        $post_value = $post->{$post_key};
      }else{
        $post_value = apply_filters('cf7_2_post_filter_cf7_field_value', $post_value, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key, $mapper->form_terms);
      }

      if(!empty($post_value)){
        $field_and_values[$form_field] = $post_value;
      }
    }
    //
    //----------- meta fields
    //
    $cf7_form_fields = $mapper->get_cf7_form_fields();
    foreach($mapper->get_post_map_meta_fields() as $form_field => $post_field){
      $post_value='';
      //if the value was filtered, let's skip it
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ) {
        continue;
      }
      //get the meta value
      if($load_saved_values) {
        $post_value = get_post_meta($post->ID, $post_field, true);
      }else{
        //debug_msg('spllygin filter cf7_2_post_filter_cf7_field_value'. $form_field);
        $post_value = apply_filters('cf7_2_post_filter_cf7_field_value', $post_value, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key, $mapper->form_terms);
      }
      if(!empty($post_value)){
        $field_and_values[$form_field] = $post_value;
      }
    }
    /*
     Finally let's also allow a user to load values for unammaped fields
    */

    $unmapped_fields = array_diff_key( $cf7_form_fields, $mapper->get_post_map_meta_fields(), $mapper->get_post_map_fields(), $mapper->get_post_map_taxonomy() );
    foreach($unmapped_fields as $form_field=>$type){
      if('submit' == $type){
        continue;
      }
      $post_value='';
      $post_value = apply_filters('cf7_2_post_filter_cf7_field_value', $post_value, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key, $mapper->form_terms);
      //$script .= $mapper->get_field_script($form_field, $post_value);
      if(!empty($post_value)){
        $field_and_values[$form_field] = $post_value;
      }
    }
    //
    // ------------ taxonomy fields
    //
    $load_chosen_script=false;
    foreach($mapper->get_post_map_taxonomy() as $form_field => $taxonomy){
      //if the value was filtered, let's skip it
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        continue;
      }
      $terms_id = array();
      if( $load_saved_values ) {
        $terms = get_the_terms($post, $taxonomy);
        if(empty($terms)) $terms = array();
        foreach($terms as $term){
          $terms_id[] = $term->term_id;
        }
      }else{
        $terms_id = apply_filters('cf7_2_post_filter_cf7_taxonomy_terms',$terms_id, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key);
        if( is_string($terms_id) ){
          $terms_id = array($terms_id);
        }
      }
      //load the list of terms
      // debug_msg($terms_id, "buidling options for taxonomy ".$taxonomy." ");
      $field_type = $cf7_form_fields[$form_field];
      /** @since 5.0 allow hybrid dropdown fields */
      $isHybrid = $mapper->field_has_class($form_field, 'hybrid-select');
      if($isHybrid){
        wp_enqueue_script('hybriddd-js'); //previously registered.
        wp_enqueue_style('hybriddd-style');
      }
      /** @since 5.1.1 track branch for taxonomy filter */
      $branch = 0;
      if(is_taxonomy_hierarchical($taxonomy)) $branch = array(0);

      if( $isHybrid &&  $field_type != 'select'){
        $limit = ($field_type == 'checkbox')? -1:1;
        $hdd = array(
          'limitSelection' => $limit,
          'fieldName' => $form_field,
          'selectedValues'=>$terms_id,
          'dataSet'=>array(''=>__('Select an item','post-my-contact-form-7'))
        );
        $hdd = (array) apply_filters('cf72post_filter_hybriddd_options', $hdd,$form_field,$mapper->cf7_key);

        $hdd['dataSet']=$hdd['dataSet']+$this->build_hybrid_dropdown($taxonomy, $branch, '' , $form_field, $mapper);
        $options = $hdd;
        // debug_msg($options, $form_field);
      }else{
        $options = $this->get_taxonomy_terms($taxonomy, $branch, $terms_id, $form_field, $field_type, 0, $mapper);
        $options = wp_json_encode($options);
        switch($field_type){
          case 'checkbox':
          case 'radio':
            wp_enqueue_style('c2p-css',plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/cf7-2-post-styling.css', array(),CF7_2_POST_VERSION);
            break;
          case 'select':
        }
        //for legacy purpose
        $apply_jquery_select = apply_filters('cf7_2_post_filter_cf7_taxonomy_chosen_select',true, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key) && apply_filters('cf7_2_post_filter_cf7_taxonomy_select2',true, $mapper->cf7_post_ID, $form_field, $mapper->cf7_key);
        if( $apply_jquery_select ){
          wp_enqueue_script('jquery-select2',plugin_dir_url( dirname( __FILE__ ) ) . 'assets/select2/js/select2.min.js', array('jquery'),CF7_2_POST_VERSION,true);
          wp_enqueue_style('jquery-select2',plugin_dir_url( dirname( __FILE__ ) ) . 'assets/select2/css/select2.min.css', array(),CF7_2_POST_VERSION);
        }
      }
      $field_and_values[$form_field] = $options;
    }
    //filter the values
    /** 
     * This filter is specifically for plugin authors who want to extend this plugin to map/prefill their custom cf7 fields.
     * @since
     * @var Array $field_and_values an array of field-names => values that are being prefilled.
     * @var String $cf7_post_ID the post ID to which the submission is mapped
     * @var String $cf7_post_type the post type to which the submission is mapped
     * @var String $cf7_key the current form's unique key
     * @var WP_Post $cf7_post the post object to which the submission is mapped
     * @var Array $cf7_form_fields an array of form field-names => field-types
     * @return Array of field-names => values to prefill, ideally with the custom fields removed to be handled by another plugin.
     */
    $field_and_values = apply_filters('cf7_2_post_form_values', $field_and_values, $mapper->cf7_post_ID , $mapper->post_properties['type'], $mapper->cf7_key, $post, $cf7_form_fields);
    //make sure the field names are with underscores
    $return_values = array();
    foreach($field_and_values as $field=>$value){
      $f = str_replace('-','_',$field);
      $return_values[$f]=$value;
    }
    return $return_values;
  }

  /**
  * Function to print jquery script for form field initialisation
  *
  * @since 1.3.0
  * @param   Array  $field_and_values   array of $field_name=>$values pairs
  * @param   Int  $cf7_2_post_id   a specific post to which this form submission is mapped/saved
  */
  public function get_form_field_script($nonce, $mapper){
    ob_start();
    $factory = $this;
    include( plugin_dir_path( __FILE__ ) . '/partials/cf7-2-post-script.php');
    $script = ob_get_contents ();
    ob_end_clean();
    return $script;
  }
  /**
  * method to return an array of value=>labels for constructing a hybrid dropdown.
  * (https://aurovrata.github.io/hybrid-html-dropdown/)
  * @since 5.0.0
  * @param   String $taxonomy  the taxonomy slug for which to return the list of terms
  * @param   Mixed  $branch  array of parent IDs for hierarchical taxonomies, else 0
  * @param   String   $pslug parent slug.
  * @param   String   $field form field name for which this taxonomy is mapped to.
  * @param   Form_2_Post_Mapper $mapper post mapping object.
  * @return  Array value->label pairs for hybrid dropdown..
  */
  protected function build_hybrid_dropdown($taxonomy, $branch, $pslug, $field, $mapper){
    $terms = $this->filter_taxonomy_query($taxonomy, $branch, $field, $mapper);

    $options = array();
    if( is_wp_error( $terms ) ){
      debug_msg('Taxonomy '.$taxonomy.' does not exist');
      return $options;
    }else if( empty($terms) ){
      //debug_msg("No Terms found for taxonomy: ".$taxonomy.", parent ".$parent);
      return $options;
    }
    foreach($terms as $t){
      $id = $t->term_id;
      $label = apply_filters('cf72post_filter_hybriddd_term_attributes',array(), $t, $field, $mapper->cf7_key);
      if(!is_array($label)) $label = array();
      $classes = 'term-'.$id.' slug-'.$t->slug;
      $kids = array();
      if(is_array($branch)){
        array_pop($branch);
        $branch[]=$t->parent;
        $classes .= ($t->parent>0 ? ' parent-slug-'.$pslug.' parent-term-' . $t->parent : '');
        $kids = $this->build_hybrid_dropdown($taxonomy, array_merge($branch,array($id)), $t->slug, $field, $mapper);
      }
      $options[$id] = array('label'=>(array($t->name, $classes) + $label) ) + $kids;
    }
    return $options;
  }

  /**
  * method to filter the taxonomy query for mapped taxonomy fields.
  *
  * @since 5.0.0
  * @param   String $taxonomy  the taxonomy slug for which to return the list of terms
  * @param   Array  $branch  the parent ID of child terms to fetch
  * @param   String   $field form field name for which this taxonomy is mapped to.
  * @param   Form_2_Post_Mapper $mapper post mapping object.
  *@return Array|WP_Error a collectoin of WP_Term objects or an error.
  */
  protected function filter_taxonomy_query($taxonomy, $branch, $field, $mapper){
    $args = array('hide_empty' => 0);
    //for hierarchical taxonomy...
    if(is_array($branch)) $args['parent'] = end($branch);
    $args = apply_filters('cf7_2_post_filter_taxonomy_query', $args, $mapper->cf7_post_ID, $taxonomy, $field, $mapper->cf7_key, $branch);
    /**
    * allows for more felxibility in filtering taxonomy options.
    *@since 3.5.0
    */
    if(empty($args)){
      return '';
    }
    //check the WP version
    global $wp_version;
	  if ( $wp_version >= 4.5 ) {
      $args['taxonomy'] = $taxonomy;
	    $terms = get_terms($args); //WP>= 4.5 the get_terms does not take a taxonomy slug field
    }else{
      $terms = get_terms($taxonomy, $args);
    }
    return $terms;
  }
  /**
  * Function to retrieve jquery script for form field taxonomy capture
  * Request: public/
  * @since 1.2.0
  * @param   String $taxonomy  the taxonomy slug for which to return the list of terms
  * @param   Mixed  $branch  array of parent IDs, else 0
  * @param   Array  $post_terms an array of terms which a post has been tagged with
  * @param   String   $field form field name for which this taxonomy is mapped to.
  * @param   String $field_type the type of field in which the tersm are going to be listed
  * @param   int $level a 0-based integer to denote the child-nesting level of the hierarchy terms being collected.
  * @param   Form_2_Post_Mapper $mapper post mapping object.
  * @return  String a jquery code to be executed once the page is loaded.
  */
   protected function get_taxonomy_terms( $taxonomy, $branch, $post_terms, $field, $field_type, $level, $mapper){
    $terms = $this->filter_taxonomy_query($taxonomy, $branch, $field, $mapper);

    if(is_array($branch))  $parent = end($branch);
    else $parent = $branch;

    if( is_wp_error( $terms ) ){
      debug_msg('Taxonomy '.$taxonomy.' does not exist');
      return '';
    }else if( empty($terms) ){
      //debug_msg("No Terms found for taxonomy: ".$taxonomy.", parent ".$parent);
      return'';
    }
    //build the list
    $script = $term_class = ''; //html text to insert into field.
    //if conventional checkbox or radio field, wrap it in a fieldset.
    if('select' != $field_type){
      $term_class = 'cf72post-'.$taxonomy;
      $nl = '';//PHP_EOL;
      $script = '<fieldset class="c2p-top-level '.$term_class.'">';
      if($parent > 0){
        $script = '<fieldset class="cf72post-child-terms parent-term-'.$parent.'">';
        $term_class .= ' cf72post-child-term';
      }
    }

    //loop over all terms
    foreach($terms as $term){
      $term_id = $term->term_id;
      $is_optgroup=false;
      $custom_classes = array();
      $custom_attributes = array();
      $custom_class = $term_class;
      /**
      * filter classes for terms to allow addition of custom classes.
      * @param Array $custom_classes an array of strings.
      * @param WP_Term $term current term object being setup.
      * @param int $level a 0-based integer to denote the child-nesting level of the hierarchy terms being.
      * @param $field string form field being mapped.
      * @param $formKey string unique key of form being mapped.
      * @return Array an array of strings.
      * @since 3.8.0
      */
      $custom_classes = apply_filters('cf72post_filter_taxonomy_term_class', $custom_classes, $term, $level, $field, $mapper->cf7_key);

      if($custom_classes && is_array($custom_classes)){
        $custom_class .= ' '.implode(' ', $custom_classes);
      }
      /**
      * filter attributes for terms <input/> or <option> elemets to allow addition of custom attributes.
      * @param Array $custom_attributes an array of $attribute=>$value pairs.
      * @param WP_Term $term current term object being setup.
      * @param int $level a 0-based integer to denote the child-nesting level of the hierarchy terms being.
      * @param $field string form field being mapped.
      * @param $formKey string unique key of form being mapped.
      * @return Array an array of $attribute=>$value pairs.
      * @since 3.8.0
      */
      $custom_attributes = apply_filters('cf72post_filter_taxonomy_term_attributes',$custom_attributes, $term, $level, $field, $mapper->cf7_key);
      $attributes = '';
      if($custom_attributes && is_array($custom_attributes)){
        foreach($custom_attributes as $attr=>$value){
          $attributes .= ' '.$attr.'="'.(string)$value.'"';
        }
      }
      switch($field_type){
        case 'select':
          //debug_msg("Checking option: ".$mapper->cf7_post_ID." field(".$field."), term ".$term->name);
          //check if we group these terms
          if(0==$parent){
            //do we group top level temrs as <optgroup/> ?
            $groupOptions = false;
            $children = get_term_children($term_id, $taxonomy);
            if($children) $groupOptions = true;
            //let's filter this choice
            $groupOptions = apply_filters('cf7_2_post_filter_cf7_taxonomy_select_optgroup',$groupOptions, $mapper->cf7_post_ID, $field, $term, $mapper->cf7_key);

             if($groupOptions){
              $script .='<optgroup label="'.$term->name.'">';
              $is_optgroup=true;
            }
          }
          if(!$is_optgroup){
            if( in_array($term_id, $post_terms) ){
              $script .='<option'.$attributes.' class="'.$custom_class.'" value="'.$term_id.'" selected="selected">'.$term->name.'</option>';
            }else{
              $script .='<option'.$attributes.' class="'.$custom_class.'" value="'.$term_id.'" >'.$term->name.'</option>';
            }
          }
          break;
        case 'radio':
          $check = '';
          if( in_array($term_id, $post_terms) ){
            $check = 'checked';
          }
          $script .='<div id="'.$term->slug.'" class="radio-term"><label><input'.$attributes.' type="radio" name="'.$field.'" value="'.$term_id.'" class="'.$custom_class.'" '.$check.'/>';
          $script .=$term->name.'</label></div>'.$nl;
          break;
        case 'checkbox':
          $check = '';
          if( in_array($term_id, $post_terms) ){
            $check = 'checked';
          }
          $field_name = $field;
          if( !$mapper->field_has_option($field, 'exclusive') ){
            $field_name = $field.'[]';
          }
          $script .='<div id="'.$term->slug.'" class="checkbox-term"><label><input'.$attributes.' type="checkbox" name="'.$field_name.'" value="'.$term_id.'" class="'.$custom_class.'" '.$check.'/>';
          $script .=$term->name.'</label></div>'.$nl;
          break;
        default:
          return ''; //nothing more to do here
          break;
      }
      if(is_array($branch)){
        array_pop($branch); //in case it was reset in the query filter.
        $branch[]=$term->parent;
        //get children
        $parent_level = $level;
        $script .= $this->get_taxonomy_terms($taxonomy, array_merge($branch,array($term_id)), $post_terms, $field, $field_type, $level+1, $mapper);
      }
      if($is_optgroup) $script .='</optgroup>';
    }
    if('select' != $field_type) $script .='</fieldset>';

    return $script;
  }
  /**
  * regsiter a custom taxonomy
  * @since 2.0.0
  * @param  Array  $taxonomy  a, array of taxonomy arguments
  * @param  Form_2_Post_Mapper  $mapper mapper pbject
  */
  protected function register_custom_taxonomy(Array $taxonomy, Form_2_Post_Mapper $mapper) {
  	$labels = array(
  		'name'                       =>  $taxonomy["name"],
  		'singular_name'              =>  $taxonomy["singular_name"],
  		'menu_name'                  =>  $taxonomy["menu_name"],
  		'all_items'                  =>  'All '.$taxonomy["name"],
  		'parent_item'                =>  'Parent '.$taxonomy["singular_name"],
  		'parent_item_colon'          =>  'Parent '.$taxonomy["singular_name"].':',
  		'new_item_name'              =>  'New '.$taxonomy["singular_name"].' Name',
  		'add_new_item'               =>  'Add New '.$taxonomy["singular_name"],
  		'edit_item'                  =>  'Edit '.$taxonomy["singular_name"],
  		'update_item'                =>  'Update '.$taxonomy["singular_name"],
  		'view_item'                  =>  'View '.$taxonomy["singular_name"],
  		'separate_items_with_commas' =>  'Separate '.$taxonomy["name"].' with commas',
  		'add_or_remove_items'        =>  'Add or remove '.$taxonomy["name"],
  		'choose_from_most_used'      =>  'Choose from the most used',
  		'popular_items'              =>  'Popular '.$taxonomy["name"],
  		'search_items'               =>  'Search '.$taxonomy["name"],
  		'not_found'                  =>  'Not Found',
  		'no_terms'                   =>  'No '.$taxonomy["name"],
  		'items_list'                 =>  $taxonomy["name"].' list',
  		'items_list_navigation'      =>  $taxonomy["name"].' list navigation',
  	);
    //labels can be modified post registration
  	$args = array(
  		'labels'                     => $labels,
  		'hierarchical'               => $taxonomy["hierarchical"],
  		'public'                     => $taxonomy["public"],
  		'show_ui'                    => $taxonomy["show_ui"],
  		'show_admin_column'          => $taxonomy["show_admin_column"],
  		'show_in_nav_menus'          => $taxonomy["show_in_nav_menus"],
  		'show_tagcloud'              => $taxonomy["show_tagcloud"],
      'show_in_quick_edit'         => $taxonomy["show_in_quick_edit"],
      'description'                => $taxonomy["description"],
  	);
    if(isset($taxonomy['meta_box_cb'])){
      $args['meta_box_cb'] = $taxonomy['meta_box_cb'];
    }
    if(isset($taxonomy['update_count_callback'])){
      $args['update_count_callback'] = $taxonomy['update_count_callback'];
    }
    if(isset($taxonomy['capabilities'])){
      $args['capabilities'] = $taxonomy['capabilities'];
    }
    $post_types = apply_filters('cf7_2_post_filter_taxonomy_register_post_type', array( $mapper->post_properties["type"] ), $taxonomy["slug"]);
  	register_taxonomy( $taxonomy["slug"], $post_types, $args );

  }

  /**
  *  Retrieves select dropdpwn fields populated with existing emta fields
  * for each system post visible in the form mapping admin page.
  *
  *@since 5.0.0
  *@param string $param text_description
  *@return string text_description
  */
  public static function get_all_metafield_menus(){
    $factory = self::get_factory();
    $html = '<div class="system-posts-metafields display-none">'.PHP_EOL;
    foreach($factory->get_system_posts() as $post_type=>$label){
      $html .= '<div id="c2p-'.$post_type.'" class="system-post-metafield">'.PHP_EOL;
      $html .= $factory->get_metafield_menu($post_type,'');
      $html .= '</div>'.PHP_EOL;
    }
    $html .= '</div>'.PHP_EOL;
    return $html;
  }

  /**
   * Get a list of meta fields for the requested post_type
   * @since 5.0.0
   * @param      String    $post_type     post_type for which meta fields are requested.
   * @return     String    a list of option elements for each existing meta field in the DB.
  **/
  public function get_metafield_menu($post_type, $selected_field){
    global $wpdb;
    $metas = $wpdb->get_results($wpdb->prepare(
      "SELECT DISTINCT meta_key
      FROM {$wpdb->postmeta} as wpm, {$wpdb->posts} as wp
      WHERE wpm.post_id = wp.ID AND wp.post_type = %s",
      $post_type
    ));
    $has_fields = $found_existing=false;
    $disabled=$html=$display='';
    $display_select=' select-hybrid';
    $input = 'custom_meta_key';

    if(empty($selected_field)){
      $disabled=' disabled="true"';
    }

    if(false !== $metas){
      $html = '';
      $select = '<option value="">'.__('Select a field','post-my-contact-form-7').'</option>'.PHP_EOL;
      foreach($metas as $row){
        if(empty(trim($row->meta_key))) continue; /** @since 5.4.6 */
        if( 0=== strpos( $row->meta_key, '_') &&
        /**
        * filter plugin specific (internal) meta fields starting with '_'. By defaults these are skiupped by this plugin.
        * @since 2.0.0
        * @param boolean $skip true by default
        * @param string $post_type post type under consideration
        * @param string $meta_key meta field name
        */
        apply_filters('cf7_2_post_skip_system_metakey',true, $post_type, $row->meta_key) ){
          //skip _meta_keys, assuming system fields.
          continue;
        }//end if
        $selected='';
        if($selected_field == $row->meta_key){
          $selected = ' selected="true"';
          $disabled='';
          $found_existing = true;
        }
        $select .= '<option value="'.$row->meta_key.'"'.$selected.'>'.$row->meta_key.'</option>'.PHP_EOL;
        $has_fields = true;
      }
      if($has_fields){
        $display = ' display-none';
        $input = 'custom_meta_key';
        if(!empty($selected_field) && !$found_existing){
          $input = $selected_field;
          $disabled =' disabled="true"';
          $display_select = ' display-none';
          $display = '';
        }
        $select = '<select'.$disabled.' class="existing-fields'.$display_select.'">'.PHP_EOL.$select;
        $select .= '<option value="cf72post-custom-meta-field">'.__('Custom field','post-my-contact-form-7').'</option>'.PHP_EOL;
        $select .='</select>'.PHP_EOL;
        $html .= $select;

      }
      $html .= '<input class="cf7-2-post-map-label-custom'.$display.'" type="text" value="'.$input.'" '.(empty($display) ? '' : 'disabled ').'/>'.PHP_EOL;
    }
    return $html;
  }
}
if(!function_exists('c2p_get_factory')){
  function c2p_get_factory(){
    return CF72Post_Mapping_Factory::get_factory();
  }
}
if(!function_exists('c2p_mapped_post_types')){
  function c2p_mapped_post_types(){
    return CF72Post_Mapping_Factory::get_mapped_post_types();
  }
}
