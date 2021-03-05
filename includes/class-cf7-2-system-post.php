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

  




}
