<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-cf7-2-post-mapper.php';

class Form_2_System_Post extends Form_2_Post_Mapper{

  protected function __construct($cf7_id, $factory){
    $this->cf7_post_ID = $cf7_id;
    $this->factory = $factory;
    $this->post_properties['type_source'] = 'system';
  }

  protected function set_post_properties(){
    //reset the properties, this is now being published
    $this->post_properties['taxonomy'] = array();
    //keep track of old mappings.


  }

}
