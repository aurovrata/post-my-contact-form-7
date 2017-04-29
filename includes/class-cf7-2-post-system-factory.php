<?php
class Cf7_2_Post_System extends Cf7_2_Post_Factory {
  /**
   * Default Construct a Cf7_2_Post_Factory object.
   *
   * @since    1.0.0
   * @param    int    $cf7_post_id the ID of the CF7 form.
   */
  protected function __construct($cf7_post_id){
    parent::__construct($cf7_post_id);
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
    $map = get_post_meta($cf7_post_id,'_cf7_2_post-map',true);
    $post_type_source = get_post_meta($cf7_post_id,'_cf7_2_post-type_source',true);
    $factory = null;
    //debug_msg('type='.$post_type);
    if(empty($post_type)){ //let's create a new one
      $factory = new self($cf7_post_id);
      $form = WPCF7_ContactForm::get_instance($cf7_post_id);
      $post_type_source = 'factory';
      $post_type = $factory->cf7_key;
      $singular_name = ucfirst( preg_replace('/[-_]+/',' ',$form->name()) );
      $plural_name = $singular_name;
      if( 's'!= substr($plural_name,-1) ) $plural_name.='s';
      $factory->init_new_factory($post_type,$singular_name,$plural_name);
    }else{

      $factory = new self($cf7_post_id);
      if('system' == $post_type_source && 'draft' == $map){
        $form = WPCF7_ContactForm::get_instance($cf7_post_id);
        $singular_name = ucfirst( preg_replace('/[-_]+/',' ',$form->name()) );
        $plural_name = $singular_name;
        $factory->init_new_factory($post_type, $singular_name, $plural_name);
      }
      $factory->post_properties['type_source']=$post_type_source;
      $factory->load_post_mapping();

     }
     return $factory;
   }
  /**
   *Get a list of available system post_types as <option> elements
   *
   * @since 1.3.0
   * @return     String    html list of <option> elements with existing post_types in the DB
  **/
  public function get_system_posts_options(){
    $remove_post_types = array('revision','attachment','nav_menu_item','wpcf7_contact_form');
    $remove_post_types = apply_filters('cf7_2_post_filter_system_posts', $remove_post_types, $this->cf7_post_ID);
    $not_in = "'".implode("','", $remove_post_types)."'";
    global $wpdb;
    $posts = $wpdb->get_results(
      "SELECT DISTINCT post_type
      FROM {$wpdb->posts}
      WHERE post_type NOT IN ({$not_in})"
    );
    $html = '';
    $display = array();
    foreach($posts as $row){
      $display[] = $row->post_type;
    }
    $display = apply_filters('cf7_2_post_display_system_posts', $display, $this->cf7_post_ID);
    $selected = 'post';
    if('system' == $this->get('type_source')){
      $selected = $this->get('type');
    }
    foreach($display as $post_type){
      $select = ($selected == $post_type) ? ' selected="true"':'';
      $html .='<option value="' . $post_type . '"' . $select . '>' . $post_type . '</option>' . PHP_EOL;
    }
    return $html;
  }
  /**
   * Get a list of meta fields for the requested post_type
   *
   * @since 2.0.0
   * @param      String    $post_type     post_type for which meta fields are requested.
   * @return     String    a list of option elements for each existing meta field in the DB.
  **/
  public static function get_system_post_metas($post_type, $selected=''){
    global $wpdb;
    $metas = $wpdb->get_results($wpdb->prepare(
      "SELECT DISTINCT meta_key
      FROM {$wpdb->postmeta} as wpm, {$wpdb->posts} as wp
      WHERE wpm.post_id = wp.ID AND wp.post_type = %s",
      $post_type
    ));
    $html = '';
    if(false !== $metas){
      foreach($metas as $row){
        if( 0=== strpos( $row->meta_key, '_') &&
        apply_filters('cf7_2_post_skip_system_metakey',true, $post_type, $row->meta_key) ){ //skip _meta_keys, assuming system fields.
          continue;
        }
        $selected_option = ($selected==$row->meta_key)? ' selected="true"':'';
        $html.='<option value="' . $row->meta_key . '"' . $selected_option . '>' . $row->meta_key . '</option>' . PHP_EOL;
      }
    }
    return $html;
  }
}
