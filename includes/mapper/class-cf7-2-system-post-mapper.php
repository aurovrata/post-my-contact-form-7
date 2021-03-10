<?php

require_once plugin_dir_path( __FILE__ ) . 'class-cf7-2-post-mapper.php';

class Form_2_System_Post extends Form_2_Post_Mapper{

  public function __construct($cf7_id, $factory){
    $this->cf7_post_ID = $cf7_id;
    self::$factory = $factory;
    $this->post_properties['type_source'] = 'system';
  }

  protected function set_post_properties(){
    //reset the properties, this is now being published
    $this->post_properties['taxonomy'] = array();
    //keep track of old mappings.
    $properties = $this->get_mapped_fields('mapped_post_');

    //properties of factory post
    foreach($properties as $value => $prop){
      switch ($prop){
        case 'type':
        case 'map':
          $this->post_properties[$prop]=$value;
          break;
        default: //properties with boolean, unchked are blank and skipped.
          break;
      }
    }

  }

}