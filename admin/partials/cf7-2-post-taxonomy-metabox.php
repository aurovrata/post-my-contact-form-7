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
$published_class = '';
if(!$is_new_mapping) $published_class = ' class="mapping-published"';
 ?>
 <h2>Custom Taxonomy  (no spaces allowed)</h2>
 <div id="post_taxonomy_map"<?=$published_class?>>
   <?php
     $mapped_taxonomy = $factory_mapping->get_mapped_taxonomy();
     // debug_msg($mapped_taxonomy, " taxonomy... ");
     if(!empty($mapped_taxonomy)){
       foreach( $mapped_taxonomy as $cf7_field => $post_taxonomy ){
         $taxonomy = $factory_mapping->get_taxonomy($post_taxonomy);
         //debug_msg($taxonomy, " taxonomy... ");
   ?>
   <div class="custom-taxonomy-field cf7-2-post-field">
     <label class="taxonomy-label-field cf7-2-post-map-labels">
       <span class="taxonomy-name">
         <strong><?= $taxonomy['name']; ?></strong>
       </span>&nbsp;(<span class="enabled link-button">Edit</span>)
     </label>
       <select <?php $factory_mapping->is_published('select');?> class="nice-select field-options" name="cf7_2_post_map_taxonomy_value-<?= $post_taxonomy;?>">
       <?= $factory_mapping->get_taxonomy_select_options($post_taxonomy);?>
       </select>
       <?php if($is_new_mapping):?>
       <span class="dashicons dashicons-minus remove-field"></span>
     <?php endif;?>
   </div>
   <p class="cf7-post-error-msg"><span class="select-error-msg cf7-2-post-map-labels"></span></p>
   <div class="clear"></div>
 <?php if( !$factory_mapping->is_published('boolean',false) ): ?>
   <div class="custom-taxonomy-input-fields hide-if-js">
     <h4>
       Choose a taxonomy, in blue are existing public taxonomies
     </h4>
     <?= $factory_mapping->get_taxonomy_listing($post_taxonomy)?>
     <label for="cf7_2_post_map_taxonomy_names-<?= $post_taxonomy;?>">
       <strong>Plural Name</strong>
     </label>

     <input class="cf7-2-post-map-labels plural-name" type="text" <?php $factory_mapping->is_published();?> <?= ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?>" name="cf7_2_post_map_taxonomy_names-<?= $post_taxonomy;?>" value="<?= $taxonomy['name'];?>">
     <label for="cf7_2_post_map_taxonomy_name-<?= $post_taxonomy;?>">
       <strong>Singular Name</strong>
     </label>
     <input class="cf7-2-post-map-labels singular-name" type="text" <?php $factory_mapping->is_published();?> name="cf7_2_post_map_taxonomy_name-<?= $post_taxonomy;?>" <?= ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?> value="<?= $taxonomy['singular_name'];?>">
     <label for="cf7_2_post_map_taxonomy_slug-<?= $post_taxonomy;?>">
       <strong>Slug</strong>
     </label>
     <input class="cf7-2-post-map-labels taxonomy-slug" type="text" <?php $factory_mapping->is_published();?> name="cf7_2_post_map_taxonomy_slug-<?= $post_taxonomy;?>" <?= ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?> value="<?= $post_taxonomy;?>" />
     <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?= $post_taxonomy;?>" <?php $factory_mapping->is_published();?> value="<?= $taxonomy['source'];?>"/>
     <button type="button" class="button-link close-details" aria-expanded="true">
       <span class="screen-reader-text">Toggle panel: Taxonomy details</span>
       <span class="wp-core-ui button" aria-hidden="true">Save</span>
     </button>
   </div>
<?php endif;
     }
   }
   //default new taxonomy slug
   $taxonomy_slug = sanitize_title( $factory_mapping->get('singular_name') ).'_categories';
   if($is_new_mapping):
   ?>
   <div class="custom-taxonomy-field cf7-2-post-field">
     <label class="taxonomy-label-field cf7-2-post-map-labels">
       <span class="taxonomy-name"><strong>Categories</strong></span>&nbsp;(<span class="disabled link-button">Edit</span>)
     </label>
     <select disabled="disabled" name="cf7_2_post_map_taxonomy_value-<?= $taxonomy_slug;?>" class="field-options nice-select">
         <?= $factory_mapping->get_taxonomy_select_options();?>
     </select>
     <span class="dashicons dashicons-plus add-more-field"></span>
   </div>
   <p class="cf7-post-error-msg"></p>
   <div class="clear"></div>
   <div class="custom-taxonomy-input-fields hide-if-js">
     <h3>
       Choose a taxonomy, in blue are existing public taxonomies
     </h3>
     <?= $factory_mapping->get_taxonomy_listing()?>
     <label for="cf7_2_post_map_taxonomy_names-<?= $taxonomy_slug;?>">
       <strong>Plural Name</strong>
     </label>
     <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?= $taxonomy_slug;?>" disabled="disabled" value="factory"/>
     <input disabled="disabled" class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?= $taxonomy_slug;?>" value="New Categories">
     <label for="cf7_2_post_map_taxonomy_name-<?= $taxonomy_slug;?>"><strong>Singular Name</strong></label>
     <input disabled="disabled" class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?= $taxonomy_slug;?>" value="New Category">
     <label for="cf7_2_post_map_taxonomy_slug-<?= $taxonomy_slug;?>"><strong>Slug</strong></label>
     <input disabled="disabled" class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?= $taxonomy_slug;?>" value="<?= $taxonomy_slug;?>" />
     <button type="button" class="button-link close-details" aria-expanded="true">
       <span class="wp-core-ui button" aria-hidden="true">Save</span>
     </button>
   </div>
 <?php endif;?>
