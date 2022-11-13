<?php
/**
* Abstract class to defined a general mapping interface for form -> post.
* @since 5.0.0
*/

abstract class Form_2_Post_Mapper {
  /**
  * reference to mapper factory.
  * @since    1.0.0
  * @access    protected
  * @var      CF72Post_Mapping_Factory    factory object.
  */
  protected static $factory;
  /**
	 * The properties of the mapped custom post type.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_properties    an array of properties.
	 */
  public $post_properties=array();
  /**
	 * The properties of the mapped custom taxonomy.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $taxonomy_properties    an array of properties with   $taxonomy_slug=>array('source'=>$taxonomy_source, 'singular_name'=>$value, 'name'=>$plural_name)
	 */
  public $taxonomy_properties=array();
  /**
	 * The the CF7 post ID.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      int    $cf7_post_ID    the CF7 post ID.
	 */
  public $cf7_post_ID=0;
  /**
   * The the CF7 post unique key.
   *
   * @since    1.2.7
   * @access    protected
   * @var      string    $cf7_key    the unique key which can be used to identfy this form.
   */
  public $cf7_key;
  /**
	 * The CF7 form fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields    an array containing CF7 fields, {'field name'=>'field type'}.
	 */
  protected $cf7_form_fields = array();

  /**
	 * The CF7 form fields.
	 *
	 * @since    5.0.0
	 * @access    protected
	 * @var      array    $old_db_fields    an array containing CF7 fields, {'field name'=>'field type'}.
	 */
  protected $old_db_fields =array();
  /**
	 * The CF7 form fields options.
	 *
	 * @since    2.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields_options    an array containing CF7 field name and its array of options, {'field name'=>array()}.
	 */
  protected $cf7_form_fields_options =array();

  /**
	 * The CF7 form fields options.
	 *
	 * @since    5.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields_classes    an array containing CF7 field name and its array of options, {'field name'=>array()}.
	 */
  protected $cf7_form_fields_classes = array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_fields    an array mapped CF7 fields, to default
   * post fields {'form field name'=>'post field'}.
	 */
  protected $post_map_fields = array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_meta_fields    an array mapped CF7 fields, to post
   * custom meta fields  {'form field name'=>'post field'}.
	 */
  protected $post_map_meta_fields=array();
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_taxonomy    an array mapped CF7 fields, to post
   * custom taxonomy  {'form field name'=>'taxonomy slug'}.
	 */
   protected $post_map_taxonomy=array();
  /**
	 * The CF7 form fields values to set as localize_script.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $localise_values    an array CF7 fields=>values.
	 */
  protected $localise_values;
  /**
   * An array of wpcf7_type taxonomy terms this form belongs to.
   * @since 3.2.0
   * @access protected
   * @var array $form_terms an array of terms.
   **/
   public $form_terms=array();
   /**
   * an array of post_id=>array($post_type =>[factory|system])); key value pairs.
   * @since 3.4.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $mapped_post_types=array();
   /**
   * boolean flag to flush rewrite rules when custom posts are created/updated.
   * @since 3.8.2
   * @access protected
   * @var boolean $flush_permalink_rules a boolean flag.
   */
   public $flush_permalink_rules = false;
   /**
   * an array of existing system post types to which a form can be mapped.
   * @since 5.0.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $system_post_types=array();
  /**
   * Default Construct
   *
   * @since    1.0.0
   * @param    int    $cf7_post_id the ID of the CF7 form.
   */
   /**
   * Getter for $post_map_fields
   *
   *@since 5.0.0
   *@return Array return $post_map_fields property.
   */
   public function get_post_map_fields(){
     return $this->post_map_fields;
   }
   /**
   * Getter for $post_map_meta_fields
   *
   *@since 5.0.0
   *@return Array return $post_map_meta_fields property.
   */
   public function get_post_map_meta_fields(){
     return $this->post_map_meta_fields;
   }
   /**
   * Getter for $post_map_taxonomy
   *
   *@since 5.0.0
   *@return Array return $post_map_taxonomy property.
   */
   public function get_post_map_taxonomy(){
     return $this->post_map_taxonomy;
   }
   /**
   * Getter for $cf7_form_fields
   *
   *@since 5.0.0
   *@return Array return $cf7_form_fields property.
   */
   public function get_cf7_form_fields(){
     return $this->cf7_form_fields;
   }
  /**
   * Strips mapped fields from the admin $_POST form saving.
   *
   * @since 5.0.0
   * @param      String    $field_prefix     field prefix used to identify where this field is mapped to.
   * @return     Array    array of fields mapped to post values.
  **/
  protected function get_mapped_fields($field_prefix){
    $prefix_length = strlen($field_prefix);
    $fields = array();
    foreach($_POST as $field => $value){
      if(empty($value)) continue;
      if (0===strpos($field, $field_prefix)){
        $post_field = substr($field, $prefix_length);
        switch($field_prefix){
          case 'mapped_post_':
          case 'cf7_2_post_map_taxonomy_':
            $fields[$post_field]=sanitize_text_field($value);
            break;
          default:
            $fields[sanitize_text_field($value)]=$post_field;
            break;
        }
      }
    }
    return $fields;
  }
  protected function _save_post_fields(){
    //save post fields
    $this->post_map_fields = $this->get_mapped_fields('cf7_2_post_map-');
    $this->_save_to_DB($this->post_map_fields, 'cf7_2_post_map-');

  }
  protected function _save_post_meta_fields(){
    // debug_msg($_POST, '$_POST ');
    $this->post_map_meta_fields = $this->get_mapped_fields('cf7_2_post_map_meta_value-');
    // debug_msg($this->post_map_meta_fields, 'update metas ');
    $this->_save_to_DB($this->post_map_meta_fields, 'cf7_2_post_map_meta-');
  }
  protected function _save_to_DB($fields_and_value, $prefix){
    // debug_msg($fields_and_value, "saving $prefix ");
    foreach($fields_and_value as $post_field=>$form_field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, $prefix.$form_field, $post_field);
      if(isset($this->old_db_fields[$prefix.$form_field]) ){
        unset($this->old_db_fields[$prefix.$form_field]);
      }
    }
  }
  protected function _save_taxonomy_fields(){
    /*
    the taxonomy field names are built using the slug, such as
      cf7_2_post_map_taxonomy_names-<taxonomy_slug>
      so we need to keep track of the field name prefix length to strip the slug
    */
    // debug_msg($_POST, 'POST ');
    $fields = $this->get_mapped_fields('cf7_2_post_map_taxonomy_');
    // debug_msg($fields, 'taxonomy mapped fields ');
    $len_c2p_taxonomy = strlen('value-');
    $len_c2p_name = strlen('name-');
    $len_c2p_names = strlen('names-');
    $len_c2p_source = strlen('source-');
    //keep track of all the taxonomy slugs in the the slugs array
    $slugs=array();

    foreach($fields as $field=>$value){
      if(empty($value)) continue; //skip empty mappings.
      switch(true){
        case (0 === strpos($field,'source-') ): //taxonomy source.
          $slug = substr($field,$len_c2p_source);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['source'] = $value;
          if(!isset($slugs[$slug])) $slugs[$slug]=array();
          break;
        case (0 === strpos($field,'names-') ): //Plural name.
          $slug = substr($field,$len_c2p_names);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['name'] = $value;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
        case (0 === strpos($field,'name-') ): //singular name.
          $slug = substr($field,$len_c2p_name);
          if( !isset($this->taxonomy_properties[$slug]) ){
            $this->taxonomy_properties[$slug] =  array();
          }
          $this->taxonomy_properties[$slug]['singular_name'] = $value;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
        case (0 === strpos($field,'value-') ): //form field mapped to taxonomy.
          $slug = substr($field,$len_c2p_taxonomy);
          if(0===strpos($value,'cf7_2_post_filter-')) $slug = str_replace('/','',$slug);
          else $slug = str_replace("/$value",'',$slug);
          /** @since 5.1 allow multiple fields to map a given taxonomy */
          if(!isset($slugs[$slug])) $slugs[$slug]=array();
          $slugs[$slug][]=$value;
          $this->post_map_taxonomy[$value] = $slug;
          //debug_msg("POST FIELD: ".$value."=".substr($field,$len_cf7_2_post_map_meta));
          break;
      }
    }
    // debug_msg($slugs, 'slugs ');
    //save the taxonomy mappings so they can be created
    foreach($slugs as $slug=>$field){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      if(isset($this->old_db_fields['cf7_2_post_map_taxonomy_source-'.$slug]) ){
        unset($this->old_db_fields['cf7_2_post_map_taxonomy_source-'.$slug]);
        if('factory'==$this->taxonomy_properties[$slug]['source']){
          unset($this->old_db_fields['cf7_2_post_map_taxonomy_names-'.$slug]);
          unset($this->old_db_fields['cf7_2_post_map_taxonomy_name-'.$slug]);
        }
      }
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_source-'.$slug,$this->taxonomy_properties[$slug]['source']);
      if('factory'==$this->taxonomy_properties[$slug]['source']){
        update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_names-'.$slug,$this->taxonomy_properties[$slug]['name']);
        update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_name-'.$slug,$this->taxonomy_properties[$slug]['singular_name']);
      }
      //map the cf7 fields to the taxonomies
      update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy-'.$slug,$field);
    }

    //save the taxonomy properties
    $this->post_properties['taxonomy'] = array_unique( array_merge( $this->post_properties['taxonomy'], array_keys($slugs) ) );
    update_post_meta($this->cf7_post_ID, '_cf7_2_post-taxonomy', $this->post_properties['taxonomy']);

    return true;
  }
  /**
  * method to save a mapping to the DB.
  * @since 5.0.0
  */
  public function save_mapping(){
    //taxonomy associated with this post type for this mapping.
    $this->post_properties['taxonomy'] = array();
    //set the version of this plugin.
    $this->post_properties['version'] = CF7_2_POST_VERSION;
    //setup additional properties.
    $this->set_post_properties();
    //track old settings to remove surplus.
    $this->old_db_fields = get_post_meta($this->cf7_post_ID);
    // debug_msg($this->old_db_fields, "old: ");

    foreach($this->old_db_fields as $name=>$value){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      if( false===strpos($name, 'cf7_2_post') ){
        unset($this->old_db_fields[$name]);
      }
    }
    // debug_msg($this->post_properties, 'props ');

    //save properties to DB.
    // $this->_save_to_DB($this->post_properties, '_cf7_2_post-');
    foreach($this->post_properties as $prop=>$value){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, '_cf7_2_post-'.$prop, $value);
      if(isset($this->old_db_fields['_cf7_2_post-'.$prop]) ){
        unset($this->old_db_fields['_cf7_2_post-'.$prop]);
      }
    }
    //save post fields
    $this->_save_post_fields();
    //save meta fields
    $this->_save_post_meta_fields();
    //taxonomy mapping.
    $this->_save_taxonomy_fields();

    //clear DB of any surplus old values.
    foreach($this->old_db_fields as $key=>$value){
      switch(true){
        case (0 === strpos($key,'_cf7_2_post-')):
        case (0 === strpos($key,'cf7_2_post_map-')):
        case (0 === strpos($key,'cf7_2_post_map_meta-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_source-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_names-')):
        case (0 === strpos($key,'cf7_2_post_map_taxonomy_name-')):
          delete_post_meta($this->cf7_post_ID, $key);
          break;
      }
    }
    //reset old fields.
    $this->old_db_fields = null;
    //set flush rules flag. @since 3.8.2.
    update_post_meta($this->cf7_post_ID,'_cf7_2_post_flush_rewrite_rules', $this->flush_permalink_rules);
    //register the mapper with the factory.
    self::$factory->register($this);
    return true;
  }
  /**
  * Ger teh form post ID.
  *
  *@since 5.0.0
  *@return String form post ID
  */
  public function form_ID(){
    return $this->cf7_post_ID;
  }
  /**
  * an abstract function to be defined by child classed used to set post properties.
  * existing system posts dont need properties to be set/tracked as they are defined elsewhere.
  * @since 5.0.0
  */
  abstract protected function set_post_properties();

  /**
  * load mapping properties from DB.
  * @since 5.0.0
  * @param String $cf7_key form unique key.
  */
  public function load_post_mapping($cf7_key){
    $this->cf7_key = $cf7_key;
    $this->post_map_fields = array();
    $this->post_map_meta_fields = array();
    $this->post_map_taxonomy = array();
    $this->post_properties = array();
    //get_post_meta ( int $post_id, string $key = '', bool $single = false )
    $fields = get_post_meta ($this->cf7_post_ID);
    $start = strlen('_cf7_2_post-');
    $start2 = strlen('cf7_2_post_map-');
    $start3 = strlen('cf7_2_post_map_meta-');
    $start4 = strlen('cf7_2_post_map_taxonomy-');
    foreach ( $fields as $key=>$value ) {
      //debug_msg($key.'=>'.$value[0]);
      switch (true){
       case '_cf7_2_post_flush_rewrite_rules' == $key:
        $this->flush_permalink_rules = $value[0];
        break;
       case '_cf7_2_post-taxonomy' == $key;
       case '_cf7_2_post-supports' == $key:
       case '_cf7_2_post-capabilities' == $key: //use array value.
         //debug_msg(unserialize($value[0]), $key);
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
      //debug_msg($this->post_properties['taxonomy'], "taxonomy ");
    }
    //get taxonomies
    foreach($this->post_properties['taxonomy'] as $slug){
      $taxonomy_array = array(
        'slug'=> $slug,
        'name'=>'',
        'singular_name'=>'',
        'source'=>''
       );
      /**
      * Load the source of the taxonomy, 'factor' if created by this plugin, 'system' if existing
      *@since 1.1.0
      */
      $source = get_post_meta ($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_source-'.$slug, true);
      if(!$source){ //for pre-1.1 version we need to ensure we set some defaults
       $source = 'factory';
       $taxonomy_array['name'] = get_post_meta ($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_names-'.$slug, true);
       $taxonomy_array['singular_name'] = get_post_meta ($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_name-'.$slug, true);
     }else{ //load labels from system props.
       $system_tax = get_taxonomies( array('public'=>true, '_builtin' => true),'objects' );
       if(isset($system_tax[$slug])){
         $taxonomy_array['name'] = $system_tax[$slug]->labels->name;
         $taxonomy_array['singular_name'] = $system_tax[$slug]->labels->singular_name;
       }
     }
      $taxonomy_array['source'] = $source;



      $this->taxonomy_properties[ $slug ] = $taxonomy_array;
      $cf7_field = get_post_meta ($this->cf7_post_ID, 'cf7_2_post_map_taxonomy-'.$slug, true);
      if(!is_array($cf7_field)) $cf7_field = array($cf7_field); /** @since 5.1.0 */
      foreach($cf7_field as $f) $this->post_map_taxonomy[$f] = $taxonomy_array['slug'];
    }
     //debug_msg($this->post_map_taxonomy,"mapped taxonomies... ");
    //set Title
    $this->post_properties['cf7_title'] = get_the_title($this->cf7_post_ID);
    //for old version plugin mapped post
    if(!isset($this->post_properties['version'])){
      $this->post_properties['version'] = '1.2.0';
    }
  }
  /**
  * Return form field mapped..
  *
  * @since    5.0.0
  * @param   String   $post_field   optional post meta field if already mapped.
  * @param   boolean   $is_meta whether is custom meta field, default is false
  * @return   String form field this taxonomy is mapped to.
  */
  public function get_mapped_form_field( $post_field, $is_meta = false){
    return $this->_form_field( $post_field, ($is_meta ? 'meta-field' : 'field') );
  }
  /**
  * Return form field mapped.
  *
  * @since    5.0.0
  * @param String  $taxonomy taxonomy id..
  * @return Mixed single or array of form field this taxonomy is mapped to.
  */
  public function get_taxonomy_mapped_form_field( $taxonomy=null){
   return $this->_form_field( $taxonomy, 'taxonomy' );
  }
  /**
  * get form field mapped to post field
  *
  * @since 5.0.0
  * @param String $post_field optional post meta field if already mapped.
  * @param String  $data_type   the type of mapping, 'field' | 'meta-field' | 'taxonomy'
  * @return Mixed form field this post field is ampped to or an array of form fields this taxonomy is mapped to.
  */
  private function _form_field($post_field, $data_type){
   switch($data_type){
     case 'meta-field':
       $prefix = 'cf7_2_post_map_meta-' ;
       $search = $this->post_map_meta_fields;
       break;
     case 'taxonomy':
       $prefix =  'cf7_2_post_map_taxonomy-';
       $search = $this->post_map_taxonomy;
       break;
     case 'field':
     default:
       $prefix =  'cf7_2_post_map-';
       $search = $this->post_map_fields;
       break;
   }
   $form_field = false;
   if( empty($search)){
     $form_field = get_post_meta($this->cf7_post_ID, $prefix.$post_field,true);
   }else{
     $form_field = array_search($post_field, $search);
   }
   return $form_field;
  }
  /**
  * Get the mapping of cf7 form field to taxonomy
  * @since 2.0.0
  * @return array array of {'form field'=>'taxonomy name'} mappings.
  */
  public function get_mapped_taxonomy(){
   return $this->post_map_taxonomy;
  }
  /**
  * Get the cf7 form fields
  *@since 2.5.0
  *@return array  of field=>type pairs.
  */
  public function get_form_fields(){
   if(empty($this->cf7_form_fields)) $this->load_form_fields();
   return $this->cf7_form_fields;
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
   * Set an existing taxonomy for this post.
   *
   * @since    1.0.0
  * @param String $taxonomy registered taxonomy to set, if taxonomy does not exist, it will not set.
  * @return Array   an array with $taxonomy_slug=>array('source'=>$taxonomy_source, 'singular_name'=>$value, 'name'=>$plural_name) value.
  */
  public function get_taxonomy($taxonomy){
     if( isset( $this->taxonomy_properties[$taxonomy] ) ){
       return $this->taxonomy_properties[$taxonomy];
     }else{
       return array();
     }
   }

  /**
  * Load the cf7 forms fields
  * fields are loaded in the internal array.
  * @since 1.0.0
  */
  public function load_form_fields(){
    //get all the fields of the form
    if(empty($this->cf7_form_fields)){
      $form = WPCF7_ContactForm::get_instance($this->cf7_post_ID);
      $form_elements = $form->scan_form_tags();
      //debug_msg($form_elements, " scanning cf7 form elements ");
      foreach ($form_elements as $element) {
        $type = $element['type'];
        if('' == $element['name']) continue; //save | submit type.
        $type = str_replace('*', '', $type);
        $this->cf7_form_fields[$element['name']]=$type;
        $this->cf7_form_fields_options[$element['name']]=$element['options'];
        $this->cf7_form_fields_classes[$element['name']]=(array)$element->get_option('class', 'class');
      }
    }
  }
  /**
  * Check if a cf7 field name has an option
  * @since 2.0.0
  * @param String $field_name name of cf7 field
  * @param String $option option to check e.g. 'multiple'
  * @return boolean true if the option is set, false otherwise
  */
  public function field_has_option($field_name, $option){
   return in_array($option, $this->cf7_form_fields_options[$field_name] );
  }
  /**
  * Check if a cf7 field name has a class
  * @since 5.0.0
  * @param String $field_name name of cf7 field
  * @param String $class class to check e.g. 'hybrid-select'
  * @return boolean true if the class is set, false otherwise
  */
  public function field_has_class($field_name, $class){
   return (isset($this->cf7_form_fields_classes[$field_name])) ? in_array($class, $this->cf7_form_fields_classes[$field_name] ) : false;
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
     return '';
   }
  }
 /**
 * print the input text field for custom meta fields.
 *
 *@since 5.0.0
 *@param string $param text_description
 *@return string text_description
 */
 public function get_metafield_input($post_field){
   $disabled='';
   if(empty($post_field)){
     $disabled=' disabled="true"';
     $post_field = 'meta_key_1';
   }
   return '<input'.$disabled.' name="cf7_2_post_map_meta-'.$post_field.'" class="cf7-2-post-map-labels" type="text" value="'.$post_field.'"/>';
 }

  /**
  * Function to display a dropdown list of taxonomies
  * Called by the dashbaord page.
  * @since 1.1.0
  * @param      string    $taxonomy_slug  slug of taxonomy to show as selected    .
  * @return     string    html select element.
  **/
  public function get_taxonomy_listing($taxonomy_slug=null){
    $result = '';
    // if('publish' == $this->post_properties['map']){
    //   $result .= '<select disabled>';
    // }else{
    $result .= '<select class="taxonomy-list'.(empty($taxonomy_slug)?'':' select-hybrid').'">';
    // }
    if(empty($taxonomy_slug)){
      $result .= '<option value="" data-name="" >'.__('Choose a Taxonomy', 'post-my-contact-form-7' ). '</option>';
    }
    $default_slug = sanitize_title( $this->get('singular_name') ).'_categories';
    $result .= '<option class="factory-taxonomy" value="'.$default_slug.'" data-name="'.__('New Category', 'post-my-contact-form-7' ). '" class="factory-taxonomy">'.__('New Categories', 'post-my-contact-form-7' ). '</option>';
    if(!empty($taxonomy_slug) &&
    isset($this->taxonomy_properties[$taxonomy_slug])){
      $taxonomy = $this->taxonomy_properties[$taxonomy_slug];
      $result .= '<option selected data-name="'.$taxonomy['singular_name'].'" value="'.$taxonomy_slug.'" class="'.$taxonomy['source'].'-taxonomy">';
      $result .= $taxonomy['name'];
      $result .= '</option>';
    }

    $system_taxonomies = get_taxonomies( array('public'=>true, '_builtin' => false), 'objects' );
    // debug_msg($system_taxonomies, 'system tax ');
    //inset the default post tags and category
    if('post_tag' !=$taxonomy_slug){
      $result .= '<option value="post_tag" data-name="'.__('Post Tag', 'post-my-contact-form-7' ). '" class="system-taxonomy">'.__('Post Tags', 'post-my-contact-form-7' ). '</option>';
    }
    if('category' !=$taxonomy_slug){
      $result .= '<option value="category" data-name="'.__('Post Category', 'post-my-contact-form-7' ). '" class="system-taxonomy">'.__('Post Categories', 'post-my-contact-form-7' ). '</option>';
    }
    foreach($system_taxonomies as $taxonomy){
      if( !empty($taxonomy_slug) && $taxonomy_slug==$taxonomy->name ) continue;
      $result .= '<option value="'.$taxonomy->name.'" data-name="'.$taxonomy->labels->singular_name.'" class="system-taxonomy">';
      $result .= $taxonomy->labels->name;
      $result .= '</option>';
    }
    $result .= '</select>';

    return $result;
  }

  /**
  * Save the submitted form data to a new/existing post
  * calling this function assumes the mapped post_type exists and is published
  * hooked to 'wpcf7_before_send_mail' which calls $public->save_cf7_2_post() and in turn calls this.
  *@since 1.0.0
  *@param WPCF7_Submission $submission cf7 submission object.
  */
  public function save_form_2_post($submission){
    $cf7_form_data = $submission->get_posted_data();
    $is_submitted = true;
    if(isset($cf7_form_data['save_cf7_2_post']) && 'true'==$cf7_form_data['save_cf7_2_post']){
      $is_submitted = false;
    }
    $this->load_form_fields(); //this loads the form fields and their type
    // debug_msg($cf7_form_data, 'saving submission ');
    //check if this is a system post which are mapped using an action.
    if( has_action('cf7_2_post_save-'.$this->get('type')) ){
      /**
      * Action to by-pass the form submission process altogether.
      * @since v1.3.0
      * @param string $key unique form key.
      * @param array $data array of submitted key=>value pairs.
      * @param array $file array of submitted files if any.
      */
      do_action( 'cf7_2_post_save-'.$this->get('type'), $this->cf7_key, $cf7_form_data, $submission->uploaded_files());
      return;
    }

    //create a new post
    //get the form email recipient
    $author = 1;

    //$msg = (is_user_logged_in())?'yes':'no';
    if(isset($_POST['_map_author']) && is_numeric($_POST['_map_author'])){
      $author = intval($_POST['_map_author']);
    }else{
      //try to get a usesr form the form mail recipient if no one logged in.
      //get_post_meta ( int $post_id, string $key = '', bool $single = false )
      $mail = get_post_meta ($this->cf7_post_ID,'_mail',true);
      if( !empty($mail) &&  isset($mail['recipient']) ){
        $user_email = $mail['recipient'];
        //get_user_by ( string $field, int|string $value )
        $user = get_user_by ( 'email', $user_email );
        if($user) $author = $user->ID;
      }
    }
    $post_status = 'draft';
    if($is_submitted){ //allow programs to publish directly.
      /**
      * Filter the post status of the cusotm post created when a form is submitted, default ot 'draft';
      * @since 2.0.2
      * @param  string  $status  the post status values,default 'draft'
      * @param  string  $cf7_key  the unique key to indetify the form
      * @param  string  $data  array of key value pairs of submitted form fields
      * @return string  the post status required.
      */
      $post_status = apply_filters('cf7_2_post_status_'.$this->post_properties['type'], $post_status, $this->cf7_key, $cf7_form_data);
    }
    /**
    * Filter to set the default title for a mapped post.
    * @param  string  $post_title  default title to set.
    * @param  string  $post_type  the post type being mapped to.
    * @param  string  $cf7_key  the unique key to indetify the form.
    * @since 3.6.0
    */
    $post_type = $this->post_properties['type'];
    $post_title = 'CF7 2 Post';
    $post_title = apply_filters('cf72post_default_post_title', $post_title,  $post_type, $this->cf7_key);

    $post = array('post_type'  =>$post_type,
                  'post_author'=>$author,
                  'post_status'=> $post_status,
                  'post_title'  => $post_title
                );
    /** @since 5.5 integrate Stripe payment */
    $post_id = false;
    if ( isset($_POST['_wpcf7_stripe_payment_intent']) && empty( $_POST['_wpcf7_stripe_payment_intent'] ) && isset($_POST['_cf72post_nonce'])) {
  		$post_id = get_transient( $_POST['_cf72post_nonce'] );
  	}
    $is_update = false;
    if(isset($_POST['_map_post_id']) && !empty($_POST['_map_post_id'])){
      $post_id = $_POST['_map_post_id']; //this is an existing post being updated
    }
    if(!empty($post_id)){
      $wp_post = get_post($post_id);
      $post['post_status'] = $post_status;
      $post['post_author'] = $wp_post->post_author;
      $post['post_title'] = $wp_post->post_title;
      $is_update = true;
    }else{
      //this is a new mapping.
      $post['post_author'] = apply_filters('cf7_2_post_author_'.$this->post_properties['type'], $author, $this->cf7_post_ID, $cf7_form_data, $this->cf7_key );
      //wp_insert_post ( array $postarr, bool $wp_error = false )
      $post_id = wp_insert_post ( $post );
    }
    $post['ID'] = $post_id;
    $hasPostFields=false;
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
          //debug_msg($form_field, 'uploaded file...');
          $file=$filename='';
          $cf7_files = $submission->uploaded_files();
          if(defined('CF7_GRID_VERSION') && version_compare(CF7_GRID_VERSION, '4.9.0','>=')){
            $cf7_files = $cf7_form_data[$form_field]; //file path stored in posted data as of v4.9.
            if(!empty($cf7_files)){
              $file = cf7sg_extract_submitted_files($cf7_files);
            }
          }else{
            if(!empty($cf7_files[$form_field])){ //if set handle upload.
              $file = $cf7_files[$form_field][0]; /** file path... @since 4.1.10 cf7 5.4 is now in an array!?!*/

              $file = array($_FILES[$form_field]['name']=>$file); //file name
            }
          }
          if(!empty($file)){
            foreach($file as $filename=>$path){
              if(!file_exists($path)) continue;
              //wp_upload_bits( $name, $deprecated, $bits, $time )
              $upload_file = wp_upload_bits($filename, null, @file_get_contents($path));
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
              	}else{
                  debug_msg($attachment_id, 'error while attaching to post '.$post_id.'... ');
                }
              }else{
                debug_msg($upload_file, 'error while uploading the file, '.$filename.' to the Media Gallery... ');
              }
              //at this point skip the rest of the loop as the file is saved
              $skip_loop = true;
              //we need a special treatment
            }
          }
          break;
      }

      if($skip_loop){
        continue;
      }

      if( empty($post_key) ){
        debug_msg("Unable to map form field=".$form_field." to post field= ".$post_field);
        continue;
       }

      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        $post[$post_key] = apply_filters($form_field,'', $post_id, $cf7_form_data);
        $hasPostFields = true;
      }else{
        if( isset($cf7_form_data[$form_field]) ){
          $submitted = $cf7_form_data[$form_field];

          /**
          * Filter introduced for plugin developers to map custom plugin tag fields, allows for submitted values to be filtered before being stored.
          * @since 3.1.0
          * @param mixed $submitted  submitted value for the field
          * @param string $field_name  the field name
          * @return mixed value to store for the field.
          */
          $submitted = apply_filters('cf7_2_post_saving_tag_'.$this->cf7_form_fields[$form_field], $submitted, $form_field);
          if(is_array($submitted)){
            $post[$post_key] = implode(',', $submitted );
          }else{
            $post[$post_key] = $submitted;
          }
          $hasPostFields = true;
        }
      }
    }
    //update the post
    if( empty($post['post_name']) ){
      $post['post_name'] = 'cf7_'.$this->cf7_post_ID.'_to_post_'.$post_id;
      if( isset($post['post_title']) ){
        //sanitize_title( $title, $fallback_title, $context )
        $post['post_name'] = sanitize_title( $post['post_title'] );
      }
    }
    /*If $hasPostFields is false we have no post fields to update.*/
    if($hasPostFields){
      $post_id = wp_update_post ( $post );
    }
    //
    //-------------- meta fields
    //
    if(!$is_submitted){
      update_post_meta($post_id, '_cf7_2_post_form_submitted','no'); //form is saved
    }else{
      update_post_meta($post_id, '_cf7_2_post_form_submitted','yes'); //form is submitted
    }

    // debug_msg($this->post_map_meta_fields, "submitted data ");
    foreach($this->post_map_meta_fields as $form_field => $post_field){
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ){
        $value = apply_filters($form_field,'', $post_id, $cf7_form_data);
        //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        update_post_meta($post_id, $post_field, $value);
      }else{
        /**
        * Fix issue of conditional fields in CF7 Smart Grid toggle sections not being submitted.
        *@since 3.4.6
        */
        if(!isset($this->cf7_form_fields[$form_field])){
          continue;
        }
        if( 'file' == $this->cf7_form_fields[$form_field] ){
          $cf7_files = $submission->uploaded_files();
          $file_url = '';
          $files = array();

          if(defined('CF7_GRID_VERSION') && version_compare(CF7_GRID_VERSION, '4.9.0','>=')){
             //file path stored in posted data as of v4.9.
            if(isset($cf7_form_data[$form_field]) && !empty($cf7_form_data[$form_field])){
              $files = cf7sg_extract_submitted_files($cf7_form_data[$form_field]);
            }
          }else{
            if(!empty($cf7_files[$form_field])){ //if set handle upload.
              $files = $cf7_files[$form_field][0]; /** file path... @since 4.1.10 cf7 5.4 is now in an array!?!*/

              $files = array($_FILES[$form_field]['name']=>$files); //file name
            }
          }
          $file_url = array();
          foreach($files as $filename=>$path){
            if(!file_exists($path)) continue;
            //wp_upload_bits( $name, $deprecated, $bits, $time )
            $upload_file = wp_upload_bits($filename, null, @file_get_contents($path));

            if (!$upload_file['error']) {
              $file_url[] = $upload_file['url'];
            }else{
              debug_msg($file, "Unable to upload file ".$filename);
            }
          }
          //if(isset($cf7_form_data[$form_field])){ //if not submitted=disabled.
          if(count($file_url)==1) $file_url = $file_url[0];
          if(empty($file_url)) $file_url = '';
          update_post_meta($post_id, $post_field, $file_url);
          //}
        }else{
          if( isset($cf7_form_data[$form_field]) ){
            $submitted = $cf7_form_data[$form_field];
            /**
            * Filter introduced for plugin developers to map custom plugin tag fields, allows for submitted values to be filtered before being stored.
            * @since 3.1.0
            * @param mixed $submitted  submitted value for the field
            * @param string $field_name  the field name
            * @return mixed value to store for the field.
            */
            $submitted = apply_filters('cf7_2_post_saving_tag_'.$this->cf7_form_fields[$form_field], $submitted, $form_field);

            update_post_meta($post_id, $post_field, $submitted);
          }
        }
      }
    }
    //
    //--------------- taxonomies
    //
    $value = array(); /** @since 5.2 collect all mapped taxonomies in case multiple amppings */
    foreach($this->post_map_taxonomy as $form_field => $taxonomy){
      if(!isset($value[$taxonomy])) $value[$taxonomy] = array();
      if( 0 === strpos($form_field,'cf7_2_post_filter-') ) {
        $value[$taxonomy] = apply_filters($form_field, $value[$taxonomy], $post_id, $cf7_form_data);
      }else if(isset( $cf7_form_data[$form_field] )){
        if( is_array( $cf7_form_data[$form_field] ) ){
          $value[$taxonomy] = array_merge( $value[$taxonomy], array_map( 'intval', $cf7_form_data[$form_field]));
        }else{
          //debug_msg($cf7_form_data[$form_field], $taxonomy." values ");
          $value[$taxonomy] = array_merge( $value[$taxonomy], array_map( 'intval',  array( $cf7_form_data[$form_field] ) ));
        }
      }
    }
    foreach($value as $taxonomy=>$terms) {
      $term_taxonomy_ids = wp_set_object_terms( $post_id , $terms, $taxonomy );
      if ( is_wp_error( $term_taxonomy_ids ) ) {
        debug_msg($term_taxonomy_ids, " Unable to set taxonomy (".$taxonomy.") terms");
        debug_msg($value, "Attempted to set these term values ");
      }
    }
    /**
    * action to notify submission is mapped to post.
    */
    do_action('cf7_2_post_form_mapped_to_'.$this->post_properties['type'],$post_id, $cf7_form_data, $this->cf7_key);
    /**
    * action introduced for plugin developers to map custom plugin fields
    * @since 2.0.0
    * general action for other plugins to hook custom functionality
    * @param string $post_id  the id of the post to which this submission was mapped
    * @param string $cf7_key  the unique form key to identity the form being submitted
    * @param array $post_map_fields form fields mapped to post fields, form-field-name => post-field-name key value pairs
    * @param array $post_map_meta_fields form fields mapped to post meta fields,  form-field-name => post-meta-field-name key value pairs
    * @param array $cf7_form_data data submited in the form, form-field-name => submitted-value key value pairs
    * @param array $uploaded_files an array of uploaded files if any file submission fields are available in this form.
    */
    do_action('cf7_2_post_form_posted', $post_id, $this->cf7_key, $this->post_map_fields, $this->post_map_meta_fields, $cf7_form_data, $submission->uploaded_files());

    /**
    *@since 3.1.0 - store the post_id in a transietn field for page redirect.
    */
    if( isset($_POST['_cf72post_nonce']) && !empty($_POST['_cf72post_nonce'])){
      $time = apply_filters('cf7_2_post_transient_submission_expiration', 300, $this->cf7_key);
      if(!is_numeric($time)) $time = 300;
      set_transient( $_POST['_cf72post_nonce'], $post_id, $time );
    }
    if($is_submitted){
      /**
      * @since 3.3.0
      */
      do_action('cf7_2_post_form_submitted_to_'.$this->post_properties['type'],$post_id, $cf7_form_data, $this->cf7_key, $submission->uploaded_files());
    }
    /** @since 4.1.0 reutnr post id to handle post link mail tags in public class */
    return $post_id;
  }
  /**
  * Delete a post mapping
  *
  * @since 1.0.0
  */
  public function delete_mapping(){
    $post_type = $this->post_properties['type'];
    $source = $this->post_properties['type_source'];
     foreach($this->post_properties as $key=>$value){
       delete_post_meta($this->cf7_post_ID, '_cf7_2_post-'.$key);
     }
    delete_post_meta($this->cf7_post_ID, '_cf7_2_post_flush_rewrite_rules');
    foreach($this->post_map_fields as $cf7_field=>$post_field){
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map-'.$post_field);
    }
    foreach($this->post_map_meta_fields as $cf7_field=>$post_field){
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map_meta-'.$post_field);
    }
    //taxonomy mapping
    delete_post_meta($this->cf7_post_ID, '_cf7_2_post-taxonomy');
    foreach($this->post_properties['taxonomy'] as $slug){
     //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_names-'.$slug);
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_name-'.$slug);
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy-'.$slug);
     delete_post_meta($this->cf7_post_ID, 'cf7_2_post_map_taxonomy_source-'.$slug);
    }
    if(apply_filters('c2p_delete_all_submitted_posts', false, $post_type, $this->cf7_key)){
      $query = apply_filters('c2p_delete_all_submitted_posts_query',array('numberposts'=>-1), $post_type, $this->cf7_key);
      $query['post_type'] = $post_type;
      $allposts= get_posts( $query );
      foreach ($allposts as $eachpost) {
        wp_delete_post( $eachpost->ID, true );
      }
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
    if( !isset($this->post_properties[$property]) ) return '';
    $echo = isset($echo_string_or_value_if_null) ? $echo_string_or_value_if_null: $this->post_properties[$property];
    return $this->post_properties[$property] ? $echo : '';
  }
}
