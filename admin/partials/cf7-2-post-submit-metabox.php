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
 ?>

<div class="createbox" id="createpost">
  <div id="misc-publishing-actions">
    <div class="misc-pub-section misc-pub-post-type">
      <input name="cf7_post_id" id="cf7_post_id" value="<?php echo $cf7_post_id;?>" type="hidden">
      <input name="mapped_post_type" <?php $factory_mapping->is_published();?> id="mapped_post_type" value="<?php echo $factory_mapping->get('type');?>" type="hidden">
    <?php
    if('system' == $source){
      include_once(plugin_dir_path(__FILE__).'cf7-2-post-system-options.php');
    }else{
      include_once( plugin_dir_path(__FILE__).'cf7-2-post-factory-options.php');
    }
?>
    </div><!-- .misc-pub-section -->
  </div>
  <div class="clear"></div>
</div>
<?php if($is_new_mapping):?>
<div id="post-creation-actions">
  <div id="save-draft-actions">
    <div id="save-draft-action">
      <span class="spinner save_draft"></span>
      <input name="submit" onclick="this.form.submited=this.id;" id="save_draft" value="Save Draft" class="button button-large" type="submit">
    </div>
    <div class="clear"></div>
  </div>
  <div id="creation-action">
    <span class="spinner save_post"></span>
    <input name="submit" onclick="this.form.submited=this.id;" id="save_post" class="button button-primary button-large" value="Publish Map" type="submit">
  </div>
  <div class="clear"></div>
  <div id="ajax-response"></div>
</div>
<?php endif;?>
