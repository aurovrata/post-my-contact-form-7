<?php
require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-cf7-2-post-factory.php' ;
if( isset($_GET['id']) ){
  $cf7_post_id = $_GET['id'];
  if( isset($this->post_mapping_factory) && $cf7_post_id == $this->post_mapping_factory->get_cf7_post_id() ){
    $factory_mapping = $this->post_mapping_factory;
  }else{
    $factory_mapping = Cf7_2_Post_System::get_factory($cf7_post_id);
    $this->post_mapping_factory = $factory_mapping;
  }
}
//action
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
  <h2>Custom meta fields key (no spaces allowed)</h2>
  <div id="custom-meta-fields"<?=$published_class?>>
<?php
  $mapped_fields = $factory_mapping->get_mapped_meta_fields();
  //debug_msg($mapped_fields, "meta fields...");
  //debug_msg($mapped_fields);
  if(!empty($mapped_fields)){
    foreach( $mapped_fields as $cf7_field => $post_field ){
?>
    <div class="custom-meta-field cf7-2-post-field">
      <span class="spinner meta-label"></span>
    <?php if('system' == $source):?>
      <select <?php $factory_mapping->is_published('select');?> class="nice-select cf7-2-post-map-labels options-<?php echo $factory_mapping->get('type')?>">
        <option value="">Select a field</option>
        <?= $factory_mapping->get_system_post_metas($factory_mapping->get('type'), $post_field)?>
        <option value="cf72post-custom-meta-field">Custom field</option>
      </select>
      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>
    <?php else: ?>
      <input <?php $factory_mapping->is_published();?> name="cf7_2_post_map_meta-<?php echo $post_field;?>" class="cf7-2-post-map-labels" type="text" value="<?php echo $post_field;?>">
    <?php endif; ?>
      <select <?php $factory_mapping->is_published('select');?> class="nice-select field-options" name="cf7_2_post_map_meta_value-<?php echo $post_field;?>">
          <?php echo $factory_mapping->get_select_options($post_field,true);?>
      </select>
      <?php if($is_new_mapping):?>
      <span class="dashicons dashicons-minus remove-field"></span>
      <?php endif;?>
    </div>
    <p class="cf7-post-error-msg"><span class="select-error-msg cf7-2-post-map-labels"></span></p>
    <div class="clear"></div>
  <?php
    }
  }
  if($is_new_mapping):
  ?>
    <div class="custom-meta-field cf7-2-post-field">
      <span class="spinner meta-label"></span>
    <?php if('system' == $source): //post meta field names are saved in the form field option select name?>
      <select disabled="disabled" class="nice-select cf7-2-post-map-labels options-<?php echo $factory_mapping->get('type')?>">
        <option value="">Select a field</option>
        <?= $factory_mapping->get_system_post_metas($factory_mapping->get('type'))?>
        <option value="cf72post-custom-meta-field">Custom field</option>
      </select>
      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>
    <?php else:?>
      <input disabled="disabled" class="cf7-2-post-map-labels " type="text" name="cf7_2_post_map_meta-meta_key_1" value="meta_key_1">
    <?php endif;?>
      <select disabled="disabled" name="cf7_2_post_map_meta_value-meta_key_1" class="nice-select field-options">
          <?php echo $factory_mapping->get_select_options();?>
      </select>
      <span class="dashicons dashicons-plus add-more-field"></span>
    </div>
    <p class="cf7-post-error-msg"></p>
  <?php endif;?>
    <div class="clear"></div>
  </div>
  <p>Custom fields can be used to add extra metadata to a post that you can <a href="https://codex.wordpress.org/Using_Custom_Fields">use in your theme</a>.</p>
</div>
