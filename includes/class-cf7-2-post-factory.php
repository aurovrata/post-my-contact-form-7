<?php
class Cf7_2_Post_Factory {
  /**
	 * The properties of the mapped custom post type.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_properties    an array of properties.
	 */
  private $post_properties;
  /**
	 * The the CF7 post ID.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $cf7_post_ID    the CF7 post ID.
	 */
  private $cf7_post_ID;
  /**
	 * The CF7 form fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $cf7_form_fields    an array containing CF7 fields, {'field type'=>'field name'}.
	 */
  private $cf7_form_fields;
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_map_fields    an array mapped CF7 fields, to default
   * post fields {'form field name'=>'post field'}.
	 */
  private $post_map_fields;
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_map_meta_fields    an array mapped CF7 fields, to post
   * custom meta fields  {'form field name'=>'post field'}.
	 */
  private $post_map_meta_fields;
  /**
   * Default Construct a Cf7_2_Post_Factory object.
   *
   * @since    1.0.0
   */
  protected function __construct($cf7_post_id){
    $this->cf7_form_fields = array();
    $this->post_map_fields = array();
    $this->post_properties = array();
    $this->cf7_post_ID = $cf7_post_id;
  }
  /**
  * Initialise the factory for an existing mapping
  * @since 1.0.0
  * @param $factory_source if this mapped post was created by the factory
  */
  protected function init_factory($factory_source=true){
    if($factory_source){
      $this->post_properties['type_source']='factory';
      $this->load_post_mapping();
    }else {
      $this->post_properties['type_source']='system';
      $this->post_properties['map']='publish';
      //TODO load the system post properties.
    }
  }
  /**
  * Initialise the factory for an existing mapping
  * @since 1.0.0
  * @param $factory_source if this mapped post was created by the factory
  */
  protected function init_new_factory($post_type,$singular_name,$plural_name){
    //set some default values
    $this->post_properties=array(
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'show_in_admin_bar'     => false,
      'show_in_nav_menus'     => false,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => true,
      'publicly_queryable'    => false
    );
    $this->post_properties['map']='draft';
    $this->post_properties['type']=$post_type;
    $this->post_properties['singular_name']=$singular_name;
    $this->post_properties['plural_name']=$plural_name;
    $this->post_properties['type_source'] = 'factory';
    $this->post_properties['$cf7_title']=get_the_title($this->cf7_post_ID);

    $this->post_properties['taxonomy']=array();
    //supports
    $this->post_properties['supports'] = array(
      'title', 'editor', 'excerpt', 'author',
      'thumbnail', 'revisions', 'custom-fields' );
    //filter the support capabilities for more user customisation
    $this->post_properties['supports'] = apply_filters('cf7_2_post_supports_'.$post_type, $this->post_properties['supports']);
    //make sure we have custom-fields support
    if( !in_array('custom-fields',$this->post_properties['supports']) ){
      $this->post_properties['supports'][]='custom-fields';
    }
    //capabilities
    $reference = array(
        'edit_post' => '',
        'edit_posts' => '',
        'edit_others_posts' => '',
        'publish_posts' => '',
        'read_post' => '',
        'read_private_posts' => '',
        'delete_post' => ''
    );
    $capabilities = array_filter(apply_filters('cf7_2_post_capabilities_'.$post_type, $reference));
    $diff = array_diff_key($reference, $capabilities);
    if( empty( $diff ) ) {
      $this->post_properties['capabilities'] = $capabilities;
      $this->post_properties['map_meta_cap'] = true;
    }else{ //some keys are not set, so capabilities will not work
      //set to defaul post capabilities
      $this->post_properties['capability_type'] = 'post';
    }
  }
  /**
	 * Get a factory object for a CF7 form.
	 *
	 * @since    1.0.0
   * @param  int  $cf7_post_id  cf7 post id
   * @return Cf7_2_Post_Factory  a factory oject
   */
  public static function get_factory( $cf7_post_id ){
    //check if the cf7 form already has a mapping
    $post_type = get_post_meta($cf7_post_id,'_cf7_2_post-type',true);
    $post_type_source = get_post_meta($cf7_post_id,'_cf7_2_post-type_source',true);
    $factory = null;
    //debug_msg('type='.$post_type);
    if(empty($post_type)){ //let's create a new one
      $form = WPCF7_ContactForm::get_instance($cf7_post_id);
      $post_type_source = 'factory';
      $post_type = $form->name();
      $singular_name = ucfirst( preg_replace('/[-_]+/',' ',$post_type) );
      $plural_name = $singular_name;
      if( 's'!= substr($plural_name,-1) ) $plural_name.='s';
      $factory = new self($cf7_post_id);
      $factory->init_new_factory($post_type,$singular_name,$plural_name);
    }else{
      //debug_msg('source='.$post_type_source);
      switch($post_type_source){
        case 'factory':
          $factory = new self($cf7_post_id);
          $factory->init_factory(true);
          break;
        case 'system':
          $factory = new self($cf7_post_id);
          $factory->init_factory(false);
          break;
       }
     }
     return $factory;
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
	 * Store the mapping in the CF7 post & create the custom post mapping.
	 *
	 * @since    1.0.0
   * @param   array   $data   an array containing the admin form data, $_POST
   * @param   boolean   $create_post_mapping  if false it will only save the mapping but not
   * create the custom post for saving user form inputs.  If it is a system post, this flag is ignored.
   * @return  boolean   true if successful
   */
  public function save($data, $create_post_mapping){
    //check if we need to create a post
    $create_post = false;
    if( isset($data['mapped_post_type_source']) &&
    'factory'==$data['mapped_post_type_source'] &&
    $create_post_mapping){
      $create_post = true;
    }
    //let's  update the properties
    //is this a factory post or a system post?
    if( isset($data['mapped_post_type_source']) && isset($data['mapped_post_type']) ) {
      $this->post_properties['type_source'] = $data['mapped_post_type_source'];
      $this->post_properties['type'] = $data['mapped_post_type'];
      $is_factory_map = false;
      switch($this->post_properties['type_source']){
        case 'system': //reset the properties
          $this->set_system_post_properties($data['mapped_post_type']);
          break;
        case 'factory':
          $is_factory_map = true;
          //reset, as only checked input field are submitted and will set to true
          $this->post_properties=array_merge( $this->post_properties,
            array('hierarchical'          => false,
                  'public'                => false,
                  'show_ui'               => false,
                  'show_in_menu'          => false,
                  'menu_position'         => 5,
                  'show_in_admin_bar'     => false,
                  'show_in_nav_menus'     => false,
                  'can_export'            => false,
                  'has_archive'           => false,
                  'exclude_from_search'   => false,
                  'publicly_queryable'    => false
                )
            );
          //initial supports, set the rest from the admin $_POST
          $this->post_properties['supports'] = array('custom-fields' );
          $this->post_properties['taxonomy']=array();
          break;
      }
    }else{
      return false;
    }
    //lets load all fields
    $len_mapped_post = strlen('mapped_post_');
    $len_cf7_2_post_map = strlen('cf7_2_post_map-');
    $len_cf7_2_post_map_meta = strlen('cf7_2_post_map_meta_value-');
    foreach($data as $field => $value){
      if(empty($value)) continue;
      //debug_msg($field."=".$value,"saving...");
      switch (true){
        case ('mapped_post_menu_position'==$field && $is_factory_map): //properties:
        case ('mapped_post_type'==$field && $is_factory_map):
        case ('mapped_post_type_source'==$field && $is_factory_map):
        case ('mapped_post_singular_name'==$field && $is_factory_map):
        case ('mapped_post_plural_name'==$field && $is_factory_map):
          $this->post_properties[substr($field,$len_mapped_post)]=$value;
          break;
        case ( (0 === strpos($field,'mapped_post_')) && $is_factory_map ): //properties
          $this->post_properties[substr($field,$len_mapped_post)]=true;
          //debug_msg("PROPERTIES: ".$value."=".substr($field,$len_mapped_post).",".strpos($field,'mapped_post_'));
          break;
        case ( 0 === strpos($field,'cf7_2_post_map-') ): //cf7 form field => post field
          $post_field = substr($field,$len_cf7_2_post_map);
          $this->post_map_fields[$value]=$post_field;
          //capture the supports settings
          switch($post_field){
            case 'title':
            case 'excerpt':
            case 'author':
            case 'thumbnail':
            case 'editor':
              $this->post_properties['supports'][]=$post_field;
              break;
            default:
              break;
          }
          break;
        case (0 === strpos($field,'cf7_2_post_map_meta_value-') ): //cf7 form field => post meta field
          $this->post_map_meta_fields[$value]=substr($field,$len_cf7_2_post_map_meta);
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
      }
    }
    //let's save the properties if this is a factory mapping
    if($is_factory_map){
      //let's get the capabilities
      $reference = array(
          'edit_post' => '',
          'edit_posts' => '',
          'edit_others_posts' => '',
          'publish_posts' => '',
          'read_post' => '',
          'read_private_posts' => '',
          'delete_post' => ''
      );
      $capabilities = array_filter(apply_filters('cf7_2_post_capabilities_'.$this->post_properties['type'], $reference));
      $diff=array_diff_key($reference, $capabilities);
      if( empty( $diff ) ) {
        $this->post_properties['capabilities'] = $capabilities;
        $this->post_properties['map_meta_cap'] = true;
      }else{ //some keys are not set, so capabilities will not work
        //set to defaul post capabilities
        $this->post_properties['capability_type'] = 'post';
      }
      //is this a daft save?
      if($create_post){
        $this->post_properties['map']='publish';
      }else{
        $this->post_properties['map']='draft';
      }
      foreach($this->post_properties as $key=>$value){
        //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        update_post_meta($this->cf7_post_ID, '_cf7_2_post-'.$key,$value);
      }
    }
    //let'save the mapping of cf7 form fields to post fields
    foreach($this->post_map_fields as $cf7_field=>$post_field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map-'.$post_field,$cf7_field);
    }
    foreach($this->post_map_meta_fields as $cf7_field=>$post_field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_meta-'.$post_field,$cf7_field);
    }

    return true;
  }
  /**
	 * Load custom post properties from the CF7 post.
	 *
	 * @since    1.0.0
   */
  private function load_post_mapping(){
    $this->post_map_fields = array();
    $this->post_map_meta_fields = array();
    $this->post_properties = array();
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    $fields = get_post_meta ($this->cf7_post_ID);
    //debug_msg("found post meta,");
    //debug_msg($fields);
    $start = strlen('_cf7_2_post-');
    $start2 = strlen('cf7_2_post_map-');
    $start3 = strlen('cf7_2_post_map_meta-');
    foreach ( $fields as $key=>$value ) {
      //debug_msg($key.'=>'.$value[0]);
      switch (true){
        case '_cf7_2_post-taxonomy' == $key;
        case '_cf7_2_post-supports' == $key:
        case '_cf7_2_post-capabilities' == $key: //use array value.
          $this->post_properties[substr($key,$start)]=unserialize($value[0]);
          break;
        case (0 === strpos($key,'_cf7_2_post-')): //for the others we want to get single values only
          $this->post_properties[substr($key,$start)]=$value[0];
          break;
        case (0 === strpos($key,'cf7_2_post_map-')): //this is post mapping field
          $this->post_map_fields[$value[0]]= substr($key,$start2);
          break;
        case (0 === strpos($key,'cf7_2_post_map_meta-')): //this is post meta mapping field
          $this->post_map_meta_fields[$value[0]]= substr($key,$start3);
          break;
      }
    }
    //set Title
    $this->post_properties['cf7_title'] = get_the_title($this->cf7_post_ID);
    //debug_msg($this->post_map_meta_fields,"loaded meta... ");
    //debug_msg($this->post_map_meta_fields);
  }
  /**
	 * Return htlm <option></option> for field mapping .
	 *
	 * @since    1.0.0
   * @param String $post_map_field optional post meta field if already mapped.
   * @param boolean $is_meta whether is custom meta field, default is false
   * @return String htlm <option></option> for field mapping
   */
  public function get_select_options( $field_to_map=null, $is_meta = false){
    if( !class_exists('WPCF7_ContactForm') ){
      return '<option>No CF7 Form class found</option>';
    }
    //load teh form fields from the cf7 post
    $this->load_form_fields();
    //find the corresponding mapped field, stored in the cf7 post
    $cf7_maped_field_name = null;
    if(!empty($field_to_map)){
      //get_post_meta ( int $post_id, string $key = '', bool $single = false )
      $prefix = ($is_meta ? 'cf7_2_post_map_meta-' : 'cf7_2_post_map-');
      $cf7_maped_field_name = get_post_meta($this->cf7_post_ID, $prefix.$field_to_map,true);
      //debug_msg("found meta, ".$prefix.$field_to_map."=".$cf7_maped_field_name,"loading...");
    }
    $options = '  <option value="">Select a form field to map</option>';
    foreach ($this->cf7_form_fields as $field => $type) {
      //skip submit buttons
      if( 'submit' == $type) continue;
      //if the field to map is the thumbnail, use file type as options
      if( 'thumbnail' == $field_to_map && 'file' != $type ) continue;

        if(!empty($cf7_maped_field_name) && $field==$cf7_maped_field_name){
          $options .= '  <option selected="selected" value="'.$field.'">'.$field.'  ['.$type.']</option>';
        }else{
          $options .= '  <option value="'.$field.'">'.$field.'  ['.$type.']</option>';

      }
    }
    //filter option
    //debug_msg($field_to_map."=>".$cf7_maped_field_name.", ".(strpos($cf7_maped_field_name,'cf7_2_post_filter-')) );
    if( !empty($cf7_maped_field_name) && 0 === strpos($cf7_maped_field_name,'cf7_2_post_filter-') ){
      $options .= '  <option class="filter-option" selected="selected" value="'.$cf7_maped_field_name.'">Hook with a filter</option>';
    }else if(!empty($field_to_map)){
      $options .= '  <option class="filter-option" value="cf7_2_post_filter-'.$this->post_properties['type'].'-'.$field_to_map.'">Hook with a filter</option>';
    }else{
      $options .= '  <option class="filter-option" value="cf7_2_post_filter">Hook with a filter</option>';
    }
    return $options;
  }
  /**
  * Get the mapping of cf7 form field to post fields
  * @since 1.0.0
  * @return array array of {'form field'=>'post field'} mappings.
  */
  public function get_mapped_meta_fields(){
    return $this->post_map_meta_fields;
  }
  /**
	 * Check if a post attribute is supported.
	 *
	 * @since    1.0.0
   * @param String $post_attribute mapped post attribute to check
   * @return boolean true if it is supported
   */
  public function supports($post_attribute){
    if('draft'==$this->post_properties['map']) return true;
    return in_array( $post_attribute, $this->post_properties['supports'] );
  }
  /**
	 * Set an existing taxonomy for this post.
	 *
	 * @since    1.0.0
   * @param String $taxonomy registered taxonomy to set, if taxonomy does not exist, it will not set.
   */
  public function set_taxonomy($taxonomy){
    if( !in_array( $taxonomy, $this->post_properties['taxonomy'] ) && taxonomy_exists($taxonomy) ){
      $this->post_properties['taxonomy'][]=$taxonomy;
    }
  }
  /**
  * Load the cf7 forms fields
  * fields are loaded in the internal array.
  * @since 1.0.0
  */
  protected function load_form_fields(){
    //get all the fields of the form
    if(empty($this->cf7_form_fields)){
      $form = WPCF7_ContactForm::get_instance($this->cf7_post_ID);
      $form_elements = $form->form_scan_shortcode();
      foreach ($form_elements as $element) {
          $type = $element['type'];
          $type = str_replace('*', '', $type);
          $this->cf7_form_fields[$element['name']]=$type;
      }
    }
  }
  /**
	 * Set the post singular name.
	 *
	 * @since    1.0.0
   * @param String $name post type singular name
   */
  public function has_file_field(){
    $this->load_form_fields();
    //debug_msg($this->cf7_form_fields,', has file');
    return in_array('file',$this->cf7_form_fields);
  }
  /**
	 * Set the post type capability.
	 *
	 * @since    1.0.0
   * @param String $capability post type capability such as 'hierarchical'
   * @param boolean $flag true or false
   */
  public function set_post_capability($capability, $flag=false){
    $this->post_properties[$capability]=$flag;
  }
  /**
	 * Set the post support attributes.
	 *
	 * @since    1.0.0
   * @param Array $supports post type supports attribues such as array('title','editor','')
   */
  public function set_supports($supports){
    $this->post_properties['supports']=$supports;
  }
  /**
	 * Get mapped custom post property.
	 *
	 * @since    1.0.0
   * @param String $property post property attribute
   * @return Srting value of the property, else null if not set
   */
  public function get($property='type'){
    if( isset( $this->post_properties[$property] ) ){
      return $this->post_properties[$property];
    }else{
      return null;
    }
  }
  /**
	 * Get mapped custom post property.
	 *
	 * @since    1.0.0
   * @param String $property post property attribute
   * @param String $echo_string_or_value_if_null optional string to echo is the property is set
   * @return String if the property is set/true echo of the 2nd parameter if passed
   * else the property value is the 2nd parameter is ommited.
   */
  public function is($property, $echo_string_or_value_if_null=null){
    switch (true){
      case ( isset( $this->post_properties[$property] )
              && false != $this->post_properties[$property]
              && null != $echo_string_or_value_if_null ):
        echo $echo_string_or_value_if_null;
        break;
      case  ( isset( $this->post_properties[$property] )
              && false == $this->post_properties[$property]
              && null != $echo_string_or_value_if_null ):
        echo '';
        break;
      case ( isset( $this->post_properties[$property] )
              && null == $echo_string_or_value_if_null ):
        echo $this->post_properties[$property];
        break;
      case !isset( $this->post_properties[$property] ):
        echo 'FACTORY_ERROR:PROPERTY-'.$property.'-NOT_SET';
        break;
      default:
        echo '';
        break;
    }
    $this->is_published();
  }
  /**
  * Disables field if the post is published
  * @since 1.0.0
  */
  public function is_published($element='input', $echo=true){
    if('publish' == $this->post_properties['map']){
      switch($element){
        case 'input':
          echo ' disabled="disabled" ';
          break;
        case 'select':
          echo ' disabled ';
          break;
      }
    }else{
      echo '';
    }
  }
  /**
  * Register Custom Post Type
  *
  * @since 1.0.0
  */
  protected function create_cf7_post_type() {

  	$labels = array(
  		'name'                  => $this->post_properties['plural_name'],
  		'singular_name'         => $this->post_properties['singular_name'],
  		'menu_name'             => $this->post_properties['plural_name'],
  		'name_admin_bar'        => $this->post_properties['singular_name'],
  		'archives'              => $this->post_properties['singular_name'].' Archives',
  		'parent_item_colon'     => 'Parent '.$this->post_properties['singular_name'].':',
  		'all_items'             => 'All '.$this->post_properties['plural_name'],
  		'add_new_item'          => 'Add New '.$this->post_properties['singular_name'],
  		'add_new'               => 'Add New',
  		'new_item'              => 'New '.$this->post_properties['singular_name'],
  		'edit_item'             => 'Edit '.$this->post_properties['singular_name'],
  		'update_item'           => 'Update '.$this->post_properties['singular_name'],
  		'view_item'             => 'View '.$this->post_properties['singular_name'],
  		'search_items'          => 'Search '.$this->post_properties['singular_name'],
  		'not_found'             => 'Not found',
  		'not_found_in_trash'    => 'Not found in Trash',
  		'featured_image'        => 'Featured Image',
  		'set_featured_image'    => 'Set featured image',
  		'remove_featured_image' => 'Remove featured image',
  		'use_featured_image'    => 'Use as featured image',
  		'insert_into_item'      => 'Insert into '.$this->post_properties['singular_name'],
  		'uploaded_to_this_item' => 'Uploaded to this '.$this->post_properties['singular_name'],
  		'items_list'            => $this->post_properties['plural_name'].' list',
  		'items_list_navigation' => $this->post_properties['plural_name'].' list navigation',
  		'filter_items_list'     => 'Filter '.$this->post_properties['plural_name'].' list',
  	);
  	$args = array(
  		'label'                 => $this->post_properties['singular_name'],
  		'description'           => 'Post for CF7 Form'. $this->post_properties['cf7_title'],
  		'labels'                => $labels,
      'supports'              => $this->post_properties['supports'],
  		'taxonomies'            => $this->post_properties['taxonomy'],
  		'hierarchical'          => !empty($this->post_properties['hierarchical']),
  		'public'                => !empty($this->post_properties['public']),
  		'show_ui'               => !empty($this->post_properties['show_ui']),
  		'show_in_menu'          => !empty($this->post_properties['show_in_menu']),
  		'menu_position'         => $this->post_properties['menu_position'],
  		'show_in_admin_bar'     => !empty($this->post_properties['show_in_admin_bar']),
  		'show_in_nav_menus'     => !empty($this->post_properties['show_in_nav_menus']),
  		'can_export'            => !empty($this->post_properties['can_export']),
  		'has_archive'           => !empty($this->post_properties['has_archive']),
  		'exclude_from_search'   => !empty($this->post_properties['exclude_from_search']),
  		'publicly_queryable'    => !empty($this->post_properties['publicly_queryable']),
  		'capability_type'       => $this->post_properties['capability_type'],
  	);
    //debug_msg($args,'register_post_type '.$this->post_properties['type'].' ');
  	register_post_type( $this->post_properties['type'], $args );
    //register_post_type($this->post_properties['type'],array('public'=> true,'label'=>$this->post_properties['singular_name']));
  }
  /**
  * Dynamically registers new custom post
  * Hooks 'init'action in the admin section
  * @since 1.0.0
  */
  public static function register_cf7_post_maps(){
    global $wpdb;
    $cf7_post_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_cf7_2_post-map' AND meta_value='publish'");
    //debug_msg($cf7_post_ids);
    foreach($cf7_post_ids as $post_id){
      $cf7_2_post_map = self::get_factory($post_id);
      //debug_msg("Registering ".$cf7_2_post_map->get('type'));
      //debug_msg($cf7_2_post_map);
      $cf7_2_post_map->create_cf7_post_type();
    }
  }
  /**
  * Map a submitted form data to its post
  * calling this funciton assumes the mapped post_type exists and is published
  *@since 1.0.0
  *@param Array $cf7_form_data data submitted from cf7 form
  */
  public function save_form_2_post($cf7_form_data){
    //debug_msg($cf7_form_data, 'form data submission... ');
    //create a new post
    //get the form email recipient
    $author = 1;
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    $mail = get_post_meta ($this->cf7_post_ID,'_mail',true);
    if( !empty($mail) &&  isset($mail['recipient']) ){
      $user_email = $mail['recipient'];
      //get_user_by ( string $field, int|string $value )
      $user = get_user_by ( 'email', $user_email );
      if($user) $author = $user->ID;
    }
    $post = array('post_type'  =>$this->post_properties['type'],
                  'post_author'=>$author,
                  'post_status'=>'draft',
                  'post_title'  => 'CF7 2 Post'
                );
    //wp_insert_post ( array $postarr, bool $wp_error = false )
    $post_id = wp_insert_post ( $post );
    $post['ID'] = $post_id;
    //debug_msg("Creating a new post... ".$post_id);
    foreach($this->post_map_fields as $form_field => $post_field){
      $post_key ='';
      $skip_loop = false;
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
          //
          //debug_msg($form_field, 'uploaded file...');
          //debug_msg($_FILES, 'files... ');
          $file = $_FILES[$form_field]['tmp_name'];
          $filename = $_FILES[$form_field]['name'];
          //wp_upload_bits( $name, $deprecated, $bits, $time )
          $upload_file = wp_upload_bits($filename, null, file_get_contents($file));
          if (!$upload_file['error']) {
          	$wp_filetype = wp_check_filetype($filename, null );
          	$attachment = array(
          		'post_mime_type' => $wp_filetype['type'],
          		'post_parent' => $post_id,
          		'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
          		'post_content' => '',
          		'post_status' => 'inherit'
          	);
            //wp_insert_attachment( $attachment, $filename, $parent_post_id );
          	$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );
          	if (!is_wp_error($attachment_id)) {
          		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
              //wp_generate_attachment_metadata( $attachment_id, $file ); for images
          		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
          		wp_update_attachment_metadata( $attachment_id,  $attachment_data );
              set_post_thumbnail( $post_id, $attachment_id );
              //debug_msg($attachment,'attached file '.$attachment_id);
              //debug_msg($attachment_data,'attached file data '.$attachment_id);
          	}else{
              debug_msg($attachment_id, 'error while attaching to post '.$post_id.'... ');
            }
          }else{
            debug_msg($upload_file, 'error while uploading the file, '.$filename.' to the Media Gallery... ');
          }
          //at this point skip the rest of the loop as the file is saved
          $skip_loop = true;
          //we need a special treatment
          break;
      }

      if($skip_loop) continue;

      if( empty($post_key) ){
        debug_msg("Unable to map form field=".$form_field." to post field= ".$post_field);
        continue;
       }

      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        $post[$post_key] = apply_filters($form_field,'', $cf7_form_data);
      }else{
        if( isset($cf7_form_data[$form_field]) ){
          if(is_array($cf7_form_data[$form_field])){
            $post[$post_key] = implode(',', $cf7_form_data[$form_field] );
          }else{
            $post[$post_key] = $cf7_form_data[$form_field];
          }
        }
      }
    }
    //update the post
    if( empty($post['post_name']) ){
      if( isset($post['post_title']) ){
        //sanitize_title( $title, $fallback_title, $context )
        $post['post_name'] = sanitize_title( $post['post_title'] );
      }else{
        $post['post_name'] = 'cf7_'.$this->cf7_post_ID.'_to_post_'.$post_id;
      }
    }
    //debug_msg($post, 'updating post... ');
    $post_id = wp_insert_post ( $post );
    //debug_msg("Updated post... ".$post_id);
    //next set the meta fields
    $this->load_form_fields();
    foreach($this->post_map_meta_fields as $form_field => $post_field){
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        $value = apply_filters($form_field,'',$cf7_form_data);
        //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        update_post_meta($post_id, $post_field, $value);
      }else{
        if( isset($cf7_form_data[$form_field]) ){
          if( 'file' == $this->cf7_form_fields[$form_field] ){
            $file = $_FILES[$form_field]['tmp_name'];
            $filename = $_FILES[$form_field]['name'];
            //wp_upload_bits( $name, $deprecated, $bits, $time )
            $upload_file = wp_upload_bits($filename, null, file_get_contents($file));
            debug_msg($upload_file, "uploading file to meta field... ");
            if (!$upload_file['error']) {
              update_post_meta($post_id, $post_field, $upload_file['file']);
            }
          }else{
            update_post_meta($post_id, $post_field, $cf7_form_data[$form_field]);
          }
        }
      }
    }
    do_action('cf7_2_post_form_mapped_to_'.$this->post_properties['type'],$cf7_form_data, $post_id);
  }

}
