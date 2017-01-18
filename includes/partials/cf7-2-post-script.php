(function( $ ) {
  'use strict';
   $(document).ready(function() {
 	  var fname;
    var cf7Form = $("form.wpcf7-form");

<?php
$this->load_form_fields(); //this loads the cf7 form fields and their type
foreach($this->cf7_form_fields as $field=>$type):
  $form_field = str_replace('-','_',$field);
  switch($type):
    case 'text':
    case 'password':
    case 'url':
    case 'number':
    case 'tel':
    case 'date':
    case 'datetime':
    case 'email':
    case 'time':
?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?> !== undefined){
      cf7Form.find("input[name=<?php echo $field?>]").val(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?>);
    }
  <?php break;
    case 'select':?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?> !== undefined){
      cf7Form.find("select[name=<?php echo $field?>]").val(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?>);
    }

  <?php break;
    case 'textarea':?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?> !== undefined){
      cf7Form.find("textarea[name=<?php echo $field?>]").val(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?>);
    }

  <?php break;
    case 'radio': ?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?> !== undefined){
      cf7Form.find("input[name=<?php echo $field?>]").prop("checked",true);
    }
  <?php break;
    case 'checkbox': ?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?> !== undefined){
      fname = <?php echo $field.'[]'?>;
      $.each(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?>), function(index, value){
        cf7Form.find("input[name=fname][value=cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $form_field?>."+value+"]").prop("checked",true);
      });
    }
  <?php break;
  endswitch;
endforeach;
//load the taxonomy required
foreach($this->post_map_taxonomy as $form_field => $taxonomy){
  $js_field = str_replace('-','_',$form_field);
  //if the value was filtered, let's skip it
  if( 0 === strpos($form_field,'cf7_2_post_filter-') ) continue;
  $terms_id = array();

  $field_type = $this->cf7_form_fields[$form_field];

  switch($field_type){
    case 'select':
      if( $this->field_has_option($form_field, 'multiple') ){
        $form_field = '"'.$form_field.'[]"';
      }
      if(apply_filters('cf7_2_post_filter_cf7_taxonomy_chosen_select',true, $this->cf7_post_ID, $form_field)){
    ?>
    fname = JSON.parse(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $js_field?>);
    cf7Form.find('select[name=<?php echo $form_field?>]').addClass('chosen-select').append(fname);
    <?php
        $load_chosen_script=true;
      }else{
    ?>
    fname = JSON.parse(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $js_field?>);
    cf7Form.find('select[name=<?php echo $form_field?>]').append(fname);
    <?php
      }
      break;
    case 'radio':
    ?>
    fname = JSON.parse(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $js_field?>);
    cf7Form.find('span.<?php echo $form_field?> span.wpcf7-radio').html(fname);
      <?php
      break;
    case 'checkbox':
    ?>
    fname = JSON.parse(cf7_2_post_<?php echo $this->cf7_post_ID?>.<?php echo $js_field?>);
    cf7Form.find('span.<?php echo $form_field?> span.wpcf7-checkbox').html(fname);
      <?php
      break;
  }
}
if($load_chosen_script):
  if(!apply_filters('cf7_2_post_filter_cf7_delay_chosen_launch',false, $this->cf7_post_ID)):
  ?>
    $(".chosen-select").each(function(){
      $(this).chosen({
        width: $(this).eq( 0 ).width() + "px"
      });
    })
<?php
  endif;
endif
//finally we need to cater for the post_id if there is one
?>
    if(cf7_2_post_<?php echo $this->cf7_post_ID?>.map_post_id !== undefined){
      fname = '<input type="hidden" name="_map_post_id" id="cf2_2_post_id" value="' + cf7_2_post_<?php echo $this->cf7_post_ID?>.map_post_id + '" />';
      cf7Form.find('input[type=hidden][name=_wpnonce]').parent().append(fname);
    }
  });
})( jQuery );
