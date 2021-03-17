<?php

require_once plugin_dir_path( __FILE__ ) . 'class-cf7-2-post-mapper.php';

class Form_2_System_Post extends Form_2_Post_Mapper{

  public function __construct($cf7_id, $factory){
    $this->cf7_post_ID = $cf7_id;
    self::$factory = $factory;
    $this->post_properties['type_source'] = 'system';
    $this->post_properties['default']=0; //only custom post can be default for now.
  }

  protected function set_post_properties(){

    $properties = $this->get_mapped_fields('mapped_post_');

    //properties of factory post
    foreach($properties as $prop =>$value){
      switch ($prop){
        case 'type':
        case 'map':
          $this->post_properties[$prop]=$value;
          break;
        case 'default':
          $this->post_properties[$prop]=0;
          break;
        default: //properties with boolean, unchked are blank and skipped.
          break;
      }
    }

  }

}
