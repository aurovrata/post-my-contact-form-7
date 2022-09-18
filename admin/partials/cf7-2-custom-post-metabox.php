<?php


if(!empty($mapped_fields)){
  foreach( $mapped_fields as $cf7_field => $post_field ){
 ?>
<div class="cf72post-field">
  <label><?=$cf7_field?></label>
  <?php if(false && current_user_can('edit_others_posts',$post->ID)):?>
    <input type="text" class="field-value" name="<?= $post_field?>" value="<?= get_post_meta($post->ID, $post_field , true)?>" />
  <?php else:
    $value =  get_post_meta($post->ID, $post_field , true);
    if(is_array($value)){
      echo '<div>';
      // debug_msg($value, $cf7_field);
      output_cf72post_array_field($value,'');
      
      echo '</div>';
    }else{
      output_cf72post_field($value);
    }
endif;?>
</div>
 <?php
  }
}

 function output_cf72post_field($value){
  echo '<span class="field-value">'.$value.'</span>';
 }

 function output_cf72post_array_field($value, $append){
  if(is_array(reset($value))){ 
    foreach($value as $r=>$row) output_cf72post_array_field($row, '</br>');
  }else{
    output_cf72post_field(implode(',', $value));
    echo $append;
  }
 }
