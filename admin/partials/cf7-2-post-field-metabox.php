<?php
// require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-cf7-2-post-factory.php' ;
// $is_new_mapping = true;
$select_form_fields='<select %4$s name="cf7_2_post_map_meta_value%1$s" value="%2$s" class="field-options post-options select-hybrid">
  <option class="default-option" selected="true" value="">'. __('Select a form field', 'post-my-contact-form-7' ). '</option>
  <option class="filter-option" value="c2p_filter%3$s%1$s">'.__('Hook with a filter', 'post-my-contact-form-7' ). '</option>
</select>';
//%1 - post-field name.
//%2 - form-field name.
//%3 - post type.
//%4 - disabled attr.
// switch($factory_mapping->get('map')){
//   case 'draft':
//     $is_new_mapping = true;
//     break;
//   case 'publish':
//     $is_new_mapping = false;
//     break;
// }
// $source = $factory_mapping->get('type_source');


$mapped_fields = $factory_mapping->get_mapped_meta_fields();
// debug_msg($mapped_fields, "meta fields...");
// debug_msg($factory_mapping);
foreach( $mapped_fields as $cf7_field => $post_field ):
  ?>
  <li>
    <div class="post-meta-field cf7-2-post-field">
      <div class="post-field-name">
      <?php
      if('system' == $source){
        echo $factory_mapping->get_metafield_menu($factory_mapping->get('type'),$post_field);
      }else{
        echo $factory_mapping->get_metafield_input($post_field);
      }
      ?>
      </div>
      <?php
      //display the meta-field's form field dropdown.
      echo sprintf( $select_form_fields, $post_field, $cf7_field, "-{$factory_mapping->get('type')}", '');
      ?>
      <span class="dashicons dashicons-minus remove-field"></span>
    </div><span class="cf7-post-msg"></span>
  </li>
<?php endforeach;?>
  <li class="default-meta-field">
    <div class="post-meta-field cf7-2-post-field">
      <span class="spinner meta-label"></span>
      <div class="post-field-name">
      <?php
        if('system' == $source){
          echo $factory_mapping->get_metafield_menu($factory_mapping->get('type'),'');
        }else{
          echo $factory_mapping->get_metafield_input('');
        }
      ?>
      </div>
      <?= sprintf( $select_form_fields, '-meta_key_1','','', 'disabled="true"'); //display the form field selevt.?>
      <span class="dashicons dashicons-plus add-more-field"></span>
  </div>
</li>
