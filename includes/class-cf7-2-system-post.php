<?php
/**
* Abstract factory class that handles mapping to existing system posts. 
* @since 5.0
*/

abstract class Cf72System_Mapping_Factory {

  protected function __construct($cf7_post_id){
    $this->cf7_post_ID = $cf7_post_id;
    $post = get_post($cf7_post_id);
    $this->cf7_key = $post->post_name;
    /** @since 3.2.0 get the form terms if any */
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
    $disabled=$html='';
    if(empty($selected_field)){
      $disabled=' disabled="true"';
    }
    if(false !== $metas){
      $html = '<div id="c2p-'.$post_type.'" class="system-post-metafield">'.PHP_EOL;
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
      if($has_fields){
        $select .= '<option value="cf72post-custom-meta-field">'.__('Custom field','post-my-contact-form-7').'</option>'.PHP_EOL;
        $select .='</select>'.PHP_EOL;
        $html .= $select;
        $html .= '<input'.$disabled.' class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled />'.PHP_EOL;
        $html .= '</div>';
      }else $html='';
    }
    return $html;
  }

  /**
  * set system posts
  *
  *@since 5.0.0
  *@return Array associative array of system post_types=>post label.
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

}
