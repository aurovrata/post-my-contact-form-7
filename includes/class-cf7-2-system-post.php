<?php

class Cf7_2_System_Post {
  /**
	 * The properties of the mapped custom post type.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_properties    an array of properties.
	 */
  protected $post_properties;
  /**
	 * The properties of the mapped custom taxonomy.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $taxonomy_properties    an array of properties with   $taxonomy_slug=>array('source'=>$taxonomy_source, 'singular_name'=>$value, 'name'=>$plural_name)
	 */
  protected $taxonomy_properties;
  /**
	 * The the CF7 post ID.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      int    $cf7_post_ID    the CF7 post ID.
	 */
  public $cf7_post_ID;
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
  protected $cf7_form_fields;
  /**
	 * The CF7 form fields options.
	 *
	 * @since    2.0.0
	 * @access    protected
	 * @var      array    $cf7_form_fields_options    an array containing CF7 field name and its array of options, {'field name'=>array()}.
	 */
  protected $cf7_form_fields_options;
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_fields    an array mapped CF7 fields, to default
   * post fields {'form field name'=>'post field'}.
	 */
  protected $post_map_fields;
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_meta_fields    an array mapped CF7 fields, to post
   * custom meta fields  {'form field name'=>'post field'}.
	 */
  protected $post_map_meta_fields;
  /**
	 * The CF7 form fields mapped to post fields.
	 *
	 * @since    1.0.0
	 * @access    protected
	 * @var      array    $post_map_taxonomy    an array mapped CF7 fields, to post
   * custom taxonomy  {'form field name'=>'taxonomy slug'}.
	 */
   protected $post_map_taxonomy;
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
   protected $form_terms;
   /**
   * an array of post_id=>array($post_type =>[factory|system])); key value pairs.
   * @since 3.4.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $mapped_post_types;
   /**
   * boolean flag to flush rewrite rules when custom posts are created/updated.
   * @since 3.8.2
   * @access protected
   * @var boolean $flush_permalink_rules a boolean flag.
   */
   protected $flush_permalink_rules = false;
   /**
   * an array of existing system post types to which a form can be mapped.
   * @since 5.0.0
   * @access protected
   * @var array $mapped_post_types an array of key value pairs.
   */
   protected static $system_post_types;
  /**
   * Default Construct
   *
   * @since    1.0.0
   * @param    int    $cf7_post_id the ID of the CF7 form.
   */
  protected function __construct($cf7_post_id){
    $this->cf7_form_fields = array();
    $this->post_map_fields = array();
    $this->post_map_taxonomy = array();
    $this->post_properties = array();
    $this->taxonomy_properties = array();
    $this->localise_values=array();
    $this->cf7_post_ID = $cf7_post_id;
    $post = get_post($cf7_post_id);
    $this->cf7_key = $post->post_name;
    /** @since 3.2.0 get the form terms if any */
    $this->form_terms = array();
    $terms = wp_get_post_terms( $cf7_post_id, 'wpcf7_type', array('fields'=>'id=>slug') );
    if(!is_wp_error( $terms )){
      //debug_msg($terms, $cf7_post_id);
      $this->form_terms = $terms;
    }
    if(is_admin()) $this->set_system_posts(); //only used in dashboard.
  }
  /**
  * Setup system post properties for support features
  *@since 2.0.1
  */
  protected function load_system_post_properties($post_type){
    //next set system properties
    $this->post_properties['support'] = array();
    $features = get_all_post_type_supports($post_type);
    foreach($features as $feature=>$spported){
      if($spported) $this->post_properties['supports'][] = $feature;
    }
  }

  /**
   *Get a list of available system post_types as <option> elements
   *
   * @since 1.3.0
   * @return     String    html list of <option> elements with existing post_types in the DB
  **/
  public function get_system_posts_options(){
    $selected = 'post';
    if('system' == $this->get('type_source')){
      $selected = $this->get('type');
    }
    $html='';
    //debug_msg($display);
    foreach(self::$system_post_types as $post_type=>$post_label){
      $select = ($selected == $post_type) ? ' selected="true"':'';
      $html .='<option value="' . $post_type . '"' . $select . '>';
      $html .= $post_label . ' ('.$post_type.')';
      $html .='</option>' . PHP_EOL;
    }
    return $html;
  }
  /**
  *
  *
  *@since 5.0.0
  *@param string $param text_description
  *@return string text_description
  */
  public function get_all_metafield_menus(){
    $html = '<div class="system-posts-metafields display-none">'.PHP_EOL;
    foreach(self::$system_post_types as $post_type=>$label){
      $html .= $this->get_metafield_menu($post_type,'');
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
    $has_fields = false;
    $html = '<div id="c2p-'.$post_type.'" class="system-post-metafield">'.PHP_EOL;
    $disabled='';
    if(empty($selected_field)){
      $disabled=' disabled="true"';
    }
    if(false !== $metas){
      $select = '<select'.$disabled.' class="existing-fields">'.PHP_EOL;
      $select .= '<option value="">'.__('Select a field','post-my-contact-form-7').'</option>'.PHP_EOL;
      foreach($metas as $row){
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
        $selected = ($selected_field == $row->meta_key) ? ' selected="true"':'';
        $select .= '<option value="'.$row->meta_key.'"'.$selected.'>'.$row->meta_key.'</option>'.PHP_EOL;
        $has_fields = true;
      }
      if($has_fields) $select .= '<option value="cf72post-custom-meta-field">'.__('Custom field','post-my-contact-form-7').'</option>'.PHP_EOL;
      $select .='</select>'.PHP_EOL;
    }
    if(!$has_fields) $select = '';
    $html .= $select;
    $html .= '<input'.$disabled.' class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled />'.PHP_EOL;
    $html .= '</div>';
    return $html;
  }

  /**
  * set system posts
  *
  *@since 5.0.0
  *@return Array assocaitive array of system post types=>post label.
  */
  protected function set_system_posts(){
    if(!empty(self::$system_post_types)) return ;

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
    * @param string $form_id  the post id of the cf7 form currently being mapped
    * @return array an array of post-types=>post-label key value pairs to display
    */
    self::$system_post_types = apply_filters('cf7_2_post_display_system_posts', $display, $this->cf7_post_ID);
  }

  /**
  * Setups the form to existing post mapping
  * @since 1.2.7
  * @param   array   $data   an array containing the admin form data, $_POST
  * @param   boolean   $create_post_mapping  if false it will only save the mapping but not
  * create the custom post for saving user form inputs.  If it is a system post, this flag is ignored.
  * @return  boolean   true if successful
  */
  protected function set_system_mapping($data, $create_post_mapping){
    $this->post_properties = array(); //reset.
    $this->post_properties['type_source'] = $data['mapped_post_type_source'];
    $this->post_properties['type'] = $data['mapped_post_type'];
    $this->post_properties['version'] = CF7_2_POST_VERSION;
    //reset the properties, this is now being published
    $this->post_properties['taxonomy'] = array();
    //keep track of old mappings.
    $old_cf7_post_metas = get_post_meta($this->cf7_post_ID);

    if($create_post_mapping){
      $this->post_properties['map']='publish';
    }else{
      $this->post_properties['map']='draft';
    }
    //debug_msg($this->post_properties, 'saving system post ');
    //debug_msg($this->post_properties, 'saving properties ');
    foreach($this->post_properties as $key=>$value){
      //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
      update_post_meta($this->cf7_post_ID, '_cf7_2_post-'.$key,$value);
      //clear previous values.
      if(isset($old_cf7_post_metas['_cf7_2_post-'.$key]) ){
        unset($old_cf7_post_metas['_cf7_2_post-'.$key]);
      }
    }
    $is_action = ('filter' == $data['mapped_post_type_source'] || has_action('cf7_2_post_save-'.$this->post_properties['type']));
    if(!$is_action){
      //save post fields
      $this->post_map_fields = $this->get_mapped_fields('cf7_2_post_map-', $data);
      //debug_msg($this->post_map_fields, 'saving post fields');
      foreach($this->post_map_fields as $cf7_field=>$post_field){
        //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        update_post_meta($this->cf7_post_ID, 'cf7_2_post_map-'.$post_field,$cf7_field);
        if(isset($old_cf7_post_metas['cf7_2_post_map-'.$post_field]) ){
          unset($old_cf7_post_metas['cf7_2_post_map-'.$post_field]);
        }
      }

      //save meta fields
      $this->post_map_meta_fields = $this->get_mapped_fields('cf7_2_post_map_meta_value-', $data);
      //debug_msg($this->post_map_meta_fields, 'saving meta fields');
      foreach($this->post_map_meta_fields as $cf7_field=>$post_field){
        //update_post_meta($post_id, $meta_key, $meta_value, $prev_value);
        update_post_meta($this->cf7_post_ID, 'cf7_2_post_map_meta-'.$post_field,$cf7_field);
        if(isset($old_cf7_post_metas['cf7_2_post_map_meta-'.$post_field]) ){
          unset($old_cf7_post_metas['cf7_2_post_map_meta-'.$post_field]);
        }
      }

      //save the taxonomy mapping
      $this->save_taxonomies($data, false, $old_cf7_post_metas);
    }
    //clear any old values left.
    //debug_msg($old_cf7_post_metas , 'deleting');
    foreach($old_cf7_post_metas as $key=>$value){
      switch(true){
        case (0 === strpos($key,'_cf7_2_post-')):
        case (0 === strpos($key,'cf7_2_post_map-')):
        case (0 === strpos($key,'cf7_2_post_map_meta-')):
          delete_post_meta($this->cf7_post_ID, $key);
          //debug_msg('deleting: '.$key);
          break;
      }
    }
    return true;
  }
}
