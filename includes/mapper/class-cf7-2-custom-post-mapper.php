<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-cf7-2-post-mapper.php';

class Form_2_Custom_Post extends Form_2_Post_Mapper{

  protected function __construct($cf7_id){
    $this->cf7_post_ID = $cf7_id;
    $this->post_properties['type_source'] = 'factory';
  }

  protected function set_post_properties(){
    //setup default custom post properties.
    $this->post_properties = array_merge(
      $this->post_properties,
      array(
        'hierarchical'          => false,
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
    //make sure the arrays are initialised
    $len_mapped_post = strlen('mapped_post_');
    $properties = $this->get_mapped_fields('mapped_post_', $data);

    //properties of factory post
    foreach($properties as $value => $field){
      switch ($field){
        case 'menu_position': //properties with values.
        case 'type':
        case 'type_source':
        case 'singular_name':
        case 'plural_name':
          $this->post_properties[$field]=$value;
          break;
        default: //properties with boolean, unchked are blank and skipped.
          $this->post_properties[$field]=true;
          break;
      }
    }
    //let's save the properties if this is a factory mapping

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
    //enable support for selected post fields.
    $fields = $this->get_mapped_fields('cf7_2_post_map-');
    foreach($fields as $cf7_field=>$post_field){
      //keep track of custom post type support
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
    }

    //set flush rules flag. @since 3.8.2.
    if($this->post_properties['public'] || $this->post_properties['publicly_queryable']){
      $this->flush_permalink_rules = true;
    }
    return true;
  }

}
