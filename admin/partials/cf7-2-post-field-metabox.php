<?php
// require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-cf7-2-post-factory.php' ;
// $is_new_mapping = true;
$select_form_fields='<select %4$s name="cf7_2_post_map_meta_value%1$s" value="%2$s" class="field-options post-options select-hybrid">
  <option class="default-option" selected="true" value="">'. __('Select a form field', 'post-my-contact-form-7' ). '</option>
  <option class="filter-option" value="cf7_2_post_filter%3$s%1$s">'.__('Hook with a filter', 'post-my-contact-form-7' ). '</option>
</select>';
//%1 - post-field name.
//%2 - form-field name.
//%3 - post type.
//%4 - disabled attr.

$mapped_fields = $post_mapper->get_mapped_meta_fields();
// debug_msg($mapped_fields, "meta fields...");
// debug_msg($post_mapper);
foreach( $mapped_fields as $cf7_field => $post_field ):
  ?>
  <li>
    <div class="post-meta-field cf7-2-post-field">
      <div class="post-field-name">
      <?php
      if('system' == $source){
        echo $factory->get_metafield_menu($post_mapper->get('type'),$post_field);
      }else{
        echo $post_mapper->get_metafield_input($post_field);
      }
      ?>
      </div>
      <?php
      //display the meta-field's form field dropdown.
      echo sprintf( $select_form_fields, "-$post_field", $cf7_field, "-{$post_mapper->get('type')}", '');
      ?>
      <span class="dashicons dashicons-remove remove-field"></span>
    </div><span class="cf7-post-msg"></span>
  </li>
<?php endforeach;?>
  <li class="default-meta-field">
    <div class="post-meta-field cf7-2-post-field">
      <span class="spinner meta-label"></span>
      <div class="post-field-name">
      <?php
        if('system' == $source){
          echo $factory->get_metafield_menu($post_mapper->get('type'),'');
        }else{
          echo $post_mapper->get_metafield_input('');
        }
      ?>
      </div>
      <?= sprintf( $select_form_fields, '-meta_key_1','','', 'disabled="true"'); //display the form field selevt.?>
      <span class="dashicons dashicons-insert add-more-field"></span>
  </div>
</li>
