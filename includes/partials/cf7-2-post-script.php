<?php
$form_values = $this->get_form_values($cf7_2_post_id);
?>
<script type="text/javascript">
(function( $ ) {
  'use strict';
   (function(){ //make scope local to this script
     $( document).ready(function() {
       var fname;
       var data = <?php echo wp_json_encode($form_values);?>;
       var cf7Form = $("div#<?php echo $nonce ?> form.wpcf7-form");
       if(cf7Form.is('div.cf7-smart-grid form.wpcf7-form')){
         //if the smart grid is enabled, execute the loading once the grid is ready
         cf7Form.on('cf7SmartGridReady', function(){
           preloadForm($(this));
         });
       }else{
         preloadForm(cf7Form);
       }
       // function to load all the data into the form
       function preloadForm(cf7Form){
         <?php
        /**
        * filter fields mapped to taxonomy (in case mapping is done on a system post)
        * @since 1.3.2
        */
        $taxonomies = array();
        $taxonomies = apply_filters('cf7_2_post_map_extra_taxonomy', $taxonomies , $this->cf7_key );
        $taxonomies = array_merge($this->post_map_taxonomy, $taxonomies);

        $this->load_form_fields(); //this loads the cf7 form fields and their type
        foreach($this->cf7_form_fields as $field=>$type):
          if(isset($taxonomies[$field]) ) continue;

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
            case 'hidden':
            ?>
        if(data.<?php echo $form_field?> !== undefined){
          cf7Form.find("input[name=<?php echo $field?>]").val(data.<?php echo $form_field?>);
        }
    <?php break;
      case 'select':?>
        if(data.<?php echo $form_field?> !== undefined){
          cf7Form.find("select[name=<?php echo $field?>]").val(data.<?php echo $form_field?>);
        }
    <?php break;
      case 'dynamic_select':?>
        if(data.<?php echo $form_field?> !== undefined){
          cf7Form.find("select[name=<?php echo $field?>]").val(data.<?php echo $form_field?>);
        }
    <?php break;
      case 'textarea':?>
        if(data.<?php echo $form_field?> !== undefined){
          cf7Form.find("textarea[name=<?php echo $field?>]").val(data.<?php echo $form_field?>);
        }
    <?php break;
      case 'radio': ?>
        if(data.<?php echo $form_field?> !== undefined){
          cf7Form.find("input[name=<?php echo $field?>]").prop("checked",true);
        }
    <?php break;
      case 'checkbox': ?>
        if(data.<?php echo $form_field?> !== undefined){
          fname = <?php echo $field.'[]'?>;
          $.each(data.<?php echo $form_field?>), function(index, value){
            cf7Form.find("input[name=fname][value=data.<?php echo $form_field?>."+value+"]").prop("checked",true);
          });
        }
    <?php break;
    endswitch;
  endforeach;
  /*
  Taxonomy fields
  */
  $load_chosen_script = false;

  foreach($taxonomies as $form_field => $taxonomy){
    //load the taxonomy required
    //legacy

    $load_chosen = apply_filters('cf7_2_post_filter_cf7_taxonomy_chosen_select',true, $this->cf7_post_ID, $form_field) && apply_filters('cf7_2_post_filter_cf7_taxonomy_select2',true, $this->cf7_post_ID, $form_field);

    if($load_chosen){
      $load_chosen_script = true;
    }
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
        if($load_chosen){
      ?>
      fname = JSON.parse(data.<?php echo $js_field?>);
      cf7Form.find('select[name=<?php echo $form_field?>]').addClass('js-select2').append(fname);
      <?php

        }else{
      ?>
      fname = JSON.parse(data.<?php echo $js_field?>);
      cf7Form.find('select[name=<?php echo $form_field?>]').append(fname);
      <?php
        }
        break;
      case 'radio':
      ?>
      fname = JSON.parse(data.<?php echo $js_field?>);
      cf7Form.find('span.<?php echo $form_field?> span.wpcf7-radio').html(fname);
        <?php
        break;
      case 'checkbox':
      ?>
      fname = JSON.parse(data.<?php echo $js_field?>);
      cf7Form.find('span.<?php echo $form_field?> span.wpcf7-checkbox').html(fname);
        <?php
        break;
    }
  }
  if($load_chosen_script):
    $delay_chosen_script = apply_filters('cf7_2_post_filter_cf7_delay_chosen_launch',false, $this->cf7_post_ID) || apply_filters('cf7_2_post_filter_cf7_delay_select2_launch',false, $this->cf7_post_ID);
    if(!$delay_chosen_script):
    ?>
      $(".js-select2", cf7Form).each(function(){
        $(this).select2();
      })
      <?php
    endif;
  endif
  //finally we need to cater for the post_id if there is one
    ?>
        if(data.map_post_id !== undefined){
          fname = '<input type="hidden" name="_map_post_id" id="cf2_2_post_id" value="' + data.map_post_id + '" />';
          cf7Form.find('input[type=hidden][name=_wpnonce]').parent().append(fname);
        }
        /* trigger the formMapped event to let other scripts that the form is now ready */
        cf7Form.trigger("<?php echo $nonce ?>");
      }
    });
  })(); //call local function to execute it.
})( jQuery );
</script>
