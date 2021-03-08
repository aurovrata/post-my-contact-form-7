<?php
require_once plugin_dir_path( __FILE__ ) . 'class-cf7-2-post-mapper.php';

class Form_2_Custom_Post extends Form_2_Post_Mapper{

  public function __construct($cf7_id, $factory){
    $this->cf7_post_ID = $cf7_id;
    self::$factory = $factory;
    $this->post_properties['type_source'] = 'factory';
  }
  /**
  * Initialises a default custom post for a new form mapper.
  *
  *@since 5.0.0
  *@param String $post_type post type id.
  *@param String $singular_name post singular name.
  *@param String $plural_name post plural name.
  *@return string text_description
  */
  public function init_default($cf7_key, $singular_name, $plural_name){
    //set some default values
    $this->post_properties=array(
      'hierarchical'          => false, //like post.
      'public'                => true, //visible on front-end.
      'show_ui'               => true, //visible in admin dashboard.
      'show_in_menu'          => true, //visible in admin menu.
      'menu_position'         => 5, //position in admin menu.
      'show_in_admin_bar'     => false, //visible in admin bar menu.
      'show_in_nav_menus'     => false, //available in navigation menu.
      'can_export'            => true, //can be exported.
      'has_archive'           => true, //can be archived.
      'exclude_from_search'   => true, //cannot be searched from front-end.
      'publicly_queryable'    => false //can be queried from front-end.
    );
    $this->cf7_key = $cf7_key;
    $this->post_properties['map']='draft';
    $this->post_properties['type']=$cf7_key;
    $this->post_properties['version'] = CF7_2_POST_VERSION;
    $this->post_properties['singular_name']=$singular_name;
    $this->post_properties['plural_name']=$plural_name;
    $this->post_properties['type_source'] = 'factory';
    $this->post_properties['cf7_title']=get_the_title($this->cf7_post_ID);

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
  * set custom properties from $_POST admin submission page.
  * @since 5.0.0
  */
  protected function set_post_properties(){
    //setup default custom post properties.
  //   $this->post_properties = array_merge(
  //     $this->post_properties,
  //     array(
  //       'hierarchical'          => false,
  //       'public'                => false,
  //       'show_ui'               => false,
  //       'show_in_menu'          => false,
  //       'menu_position'         => 5,
  //       'show_in_admin_bar'     => false,
  //       'show_in_nav_menus'     => false,
  //       'can_export'            => false,
  //       'has_archive'           => false,
  //       'exclude_from_search'   => false,
  //       'publicly_queryable'    => false
  //     )
  // );
    //initial supports, set the rest from the admin $_POST
    $this->post_properties['supports'] = array('custom-fields' );
    //make sure the arrays are initialised
    $len_mapped_post = strlen('mapped_post_');
    $properties = $this->get_mapped_fields('mapped_post_');

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
