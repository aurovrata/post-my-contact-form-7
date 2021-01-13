<?php
// require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-cf7-2-post-factory.php' ;

$meta_fields = '
       <select value="%2$s" name="cf7_2_post_map_meta_value-%1$s" class="field-options post-options nice-select">
         <option class="default-option" value="">'. __('Select a form field', 'post-my-contact-form-7' ). '</option>
         <option class="filter-option" value="cf7_2_post_filter-'.$factory_mapping->get('type').'-%1$s">'.__('Hook with a filter', 'post-my-contact-form-7' ). '</option>
       </select>';
$is_new_mapping = true;
switch($factory_mapping->get('map')){
  case 'draft':
    $is_new_mapping = true;
    break;
  case 'publish':
    $is_new_mapping = false;
    break;
}
$source = $factory_mapping->get('type_source');
$published_class = '';
if(!$is_new_mapping) $published_class = ' class="mapping-published"';
?>
<li>
  <div class="post-meta-field cf7-2-post-field">
<?php
$mapped_fields = $factory_mapping->get_mapped_meta_fields();
$post_metas = $factory_mapping->get_system_post_metas();
// debug_msg($mapped_fields, "meta fields...");
// debug_msg($factory_mapping);
if(!empty($mapped_fields)):
  foreach( $mapped_fields as $cf7_field => $post_field ):
    if('system' == $source):
      ?>
      <select <?php $factory_mapping->is_published('select');?> class="nice-select cf7-2-post-map-labels options-<?= $factory_mapping->get('type')?>" value="<?=$post_field?>">
        <option value=""><?=__('Select a field','post-my-contact-form-7')?></option>
    <?php
      foreach($post_metas as $pm):
        $selected='';
        if($pm==$post_field){
          $selected = ' selected="true"';
          $found_field = true;
        }
      ?>
        <option value="<?=$post_metas?>" <?=$selected?>><?=$post_metas?></option>
    <?php
      endforeach;
      if(!$found_field):
        ?>
        <option value="<?=$post_field?>" selected="true"><?=$post_field?></option>
    <?php
      endif;
      ?>
        <option value="cf72post-custom-meta-field"><?=__('Custom field','post-my-contact-form-7')?></option>
      </select>
      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled />
  <?php
    else: ?>
      <input <?php $factory_mapping->is_published();?> name="cf7_2_post_map_meta-<?= $post_field;?>" class="cf7-2-post-map-labels" type="text" value="<?= $post_field;?>"/>
  <?php
    endif;
    //display the meta-field's form field dropdown.
    echo sprintf( $meta_fields, $post_field, $factory_mapping->get_mapped_form_field($post_field, true));
    if($is_new_mapping):?>
      <span class="dashicons dashicons-minus remove-field"></span>
  <?php
    endif;?>
  </div><span class="cf7-post-msg"></span>
</li>
<?php
  endforeach;
endif;
  ?>
  <li>
    <div class="post-meta-field cf7-2-post-field">
      <span class="spinner meta-label"></span>
    <?php if('system' == $source): //post meta field names are saved in the form field option select name?>
      <select disabled="disabled" class="nice-select cf7-2-post-map-labels options-<?= $factory_mapping->get('type')?>">
        <option value=""><?=__('Select a field', 'post-my-contact-form-7')?></option>
    <?php
      foreach($post_metas as $pm):
        $selected='';
        if($pm==$post_field){
          $selected = ' selected="true"';
          $found_field = true;
        }
      ?>
        <option value="<?=$post_metas?>" <?=$selected?>><?=$post_metas?></option>
    <?php
      endforeach;
      if(!$found_field):
        ?>
        <option value="<?=$post_field?>" selected="true"><?=$post_field?></option>
    <?php
      endif;
      ?>
        <option value="cf72post-custom-meta-field"><?=__('Custom field','post-my-contact-form-7')?></option>
      </select>
      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>
    <?php else:?>
      <input disabled="disabled" class="cf7-2-post-map-labels " type="text" name="cf7_2_post_map_meta-meta_key_1" value="meta_key_1">
    <?php endif;?>
      <select disabled="disabled" name="cf7_2_post_map_meta_value-meta_key_1" class="nice-select field-options">
        <option class="default-option" value=""><?=__('Select a form field', 'post-my-contact-form-7' )?></option>
        <option class="filter-option" value="cf7_2_post_filter"><?=__('Hook with a filter', 'post-my-contact-form-7' )?></option>
      </select>
      <span class="dashicons dashicons-plus add-more-field"></span>
  <?php //endif;?>
    <div class="clear"></div>
  </div><span class="cf7-post-msg"></span>
</li>
