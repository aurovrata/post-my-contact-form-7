<?php
  /**
   * Get taxonomy data
   */
  require_once plugin_dir_path(dirname(dirname( __FILE__ ))) . 'includes/class-cf7-2-post-factory.php';

  /**
   * Get post data
   */
  if ( isset($_GET['id']) ) {
    $cf7_post_id = $_GET['id']; // should validate to integer?
    
    if ( isset($this->post_mapping_factory) && $cf7_post_id == $this->post_mapping_factory->get_cf7_post_id() ) {
      $factory_mapping = $this->post_mapping_factory;
    } else {
      $factory_mapping = Cf7_2_Post_System::get_factory($cf7_post_id);
      $this->post_mapping_factory = $factory_mapping;
    }
  }
  
  /**
   * Mapping action
   */
  $is_new_mapping = true;
  
  switch ( $factory_mapping->get('map') ) {
    case 'draft':
      $is_new_mapping = true;
      break;
    case 'publish':
      $is_new_mapping = false;
      break;
  }
  
  /**
   * styles
   */
  $published_class = '';
  
  if ( !$is_new_mapping ) {
    $published_class = ' class="mapping-published"';
  }
?>
  <h2><?php echo __('Custom Taxonomy  (no spaces allowed)', 'post-my-contact-form-7'); ?></h2>
  <div id="post_taxonomy_map"<?php echo $published_class; ?>>
  <?php
   
   /**
    * Loop taxonomies
    */
   $mapped_taxonomy = $factory_mapping->get_mapped_taxonomy();
   // debug_msg($mapped_taxonomy, " taxonomy... ");
   if ( !empty($mapped_taxonomy) ) {
     foreach( $mapped_taxonomy as $cf7_field => $post_taxonomy ) {
       $taxonomy = $factory_mapping->get_taxonomy($post_taxonomy);
       //debug_msg($taxonomy, " taxonomy... ");
  ?>
    <div class="custom-taxonomy-field cf7-2-post-field">
      <label class="taxonomy-label-field cf7-2-post-map-labels">
        <span class="taxonomy-name">
          <strong><?php echo $taxonomy['name']; ?></strong>
        </span>&nbsp;(<span class="enabled link-button"><?php echo __('Edit', 'post-my-contact-form-7')?></span>)
      </label>
      <select <?php $factory_mapping->is_published('select'); ?> class="nice-select field-options" name="cf7_2_post_map_taxonomy_value-<?php echo $post_taxonomy; ?>">
        <?php echo $factory_mapping->get_taxonomy_select_options($post_taxonomy); ?>
      </select>
    <?php if ( $is_new_mapping ) { ?>
      <span class="dashicons dashicons-minus remove-field"></span>
    <?php } ?>
    </div>
    <p class="cf7-post-error-msg"><span class="select-error-msg cf7-2-post-map-labels"></span></p>
    <div class="clear"></div>
  <?php if ( !$factory_mapping->is_published('boolean', false) ) { ?>
    <div class="custom-taxonomy-input-fields hide-if-js">
      <h4><?php echo __('Choose a taxonomy, in blue are existing public taxonomies', 'post-my-contact-form-7'); ?></h4>
      <?php echo $factory_mapping->get_taxonomy_listing($post_taxonomy); ?>
      <label for="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy; ?>">
        <strong><?php echo __('Plural Name', 'post-my-contact-form-7')?></strong>
      </label>
      <input class="cf7-2-post-map-labels plural-name" type="text" <?php $factory_mapping->is_published(); ?> <?php echo ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?>" name="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy; ?>" value="<?php echo $taxonomy['name']; ?>">
      <label for="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy; ?>">
        <strong><?php echo __('Singular Name', 'post-my-contact-form-7'); ?></strong>
      </label>
      <input class="cf7-2-post-map-labels singular-name" type="text" <?php $factory_mapping->is_published(); ?> name="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy; ?>" <?php echo ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?> value="<?php echo $taxonomy['singular_name']; ?>">
      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy; ?>">
        <strong><?php echo __('Slug', 'post-my-contact-form-7'); ?></strong>
      </label>
      <input class="cf7-2-post-map-labels taxonomy-slug" type="text" <?php $factory_mapping->is_published(); ?> name="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy; ?>" <?php echo ('system'==$taxonomy['source']) ? 'readonly="true"' : ''; ?> value="<?php echo $post_taxonomy; ?>" />
      <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo $post_taxonomy; ?>" <?php $factory_mapping->is_published(); ?> value="<?php echo $taxonomy['source']; ?>" />
      <button type="wp-core-ui" class="button-link close-details" aria-expanded="true">
        <span class="screen-reader-text"><?php echo __('Toggle panel: Taxonomy details', 'post-my-contact-form-7'); ?></span>
        <span class="button button-primary focus" aria-hidden="true"><?php echo __('Save', 'post-my-contact-form-7'); ?></span>
      </button>
    </div>
  <?php } // ENDIF !$factory_mapping->is_published('boolean', false) ?>
    <?php } // ENDFOREACH $mapped_taxonomy as $cf7_field => $post_taxonomy ?>
  <?php } // ENDIF !empty($mapped_taxonomy) ?>
  <?php
    /**
     * Default new taxonomy slug
     */
    $taxonomy_slug = sanitize_title($factory_mapping->get('singular_name')) . '_categories';
    if ( $is_new_mapping ) {
  ?>
    <div class="custom-taxonomy-field cf7-2-post-field">
      <label class="taxonomy-label-field cf7-2-post-map-labels">
        <span class="taxonomy-name"><strong><?php echo __('Categories', 'post-my-contact-form-7'); ?></strong></span>&nbsp;(<span class="disabled link-button"><?php echo __('Edit', 'post-my-contact-form-7'); ?></span>)
      </label>
      <select disabled="disabled" name="cf7_2_post_map_taxonomy_value-<?php echo $taxonomy_slug; ?>" class="field-options nice-select">
        <?php echo $factory_mapping->get_taxonomy_select_options(); ?>
      </select>
      <span class="dashicons dashicons-plus add-more-field"></span>
    </div>
    <p class="cf7-post-error-msg"></p>
    <div class="clear"></div>
    <div class="custom-taxonomy-input-fields hide-if-js">
      <h3><?php echo __('Choose a taxonomy, in blue are existing public taxonomies', 'post-my-contact-form-7'); ?></h3>
      <?php echo $factory_mapping->get_taxonomy_listing()?>
      <label for="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug; ?>">
        <strong><?php echo __('Plural Name', 'post-my-contact-form-7'); ?></strong>
      </label>
      <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo $taxonomy_slug; ?>" disabled="disabled" value="factory" />
      <input disabled="disabled" class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug; ?>" value="New Categories">
      <label for="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug; ?>"><strong><?php echo __('Singular Name', 'post-my-contact-form-7'); ?></strong></label>
      <input disabled="disabled" class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug; ?>" value="New Category">
      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug; ?>"><strong><?php echo __('Slug', 'post-my-contact-form-7'); ?></strong></label>
      <input disabled="disabled" class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug; ?>" value="<?php echo $taxonomy_slug; ?>" />
      <button type="wp-core-ui button" class="button-link close-details" aria-expanded="true">
        <span class="button-primary focus button" aria-hidden="true"><?php echo __('Save', 'post-my-contact-form-7'); ?></span>
      </button>
    </div>
<?php } ?>
