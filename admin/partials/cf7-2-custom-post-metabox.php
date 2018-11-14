<?php


if(!empty($mapped_fields)){
  foreach( $mapped_fields as $cf7_field => $post_field ){
 ?>
<div class="cf72post-field">
  <label><?=$cf7_field?></label>
  <?php if(false && current_user_can('edit_others_posts')):?>
    <input type="text" class="field-value" name="<?= $post_field?>" value="<?= get_post_meta($post->ID, $post_field , true)?>" />
  <?php else:
    $value =  get_post_meta($post->ID, $post_field , true);
    if(is_array($value)){
      echo '<div>';
      foreach($value as $key=>$avalue){
        if(is_array($avalue)){
          echo $key.":";
          foreach($avalue as $row=>$rvalue){
            $out = '';
            if(!empty($rvalue)) $out = implode(',', $rvalue);
            output_cf72post_field($out);
          }
          echo '</div><div>';
        }else{
          echo $avalue.',';
        }
      }
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
