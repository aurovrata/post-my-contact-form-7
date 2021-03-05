<?php
  /**
   * Get taxonomy data
   */
  // require_once plugin_dir_path(dirname(dirname( __FILE__ ))) . 'includes/class-cf7-2-post-factory.php';

  /**
   * Get post data
   */
  // if ( isset($_GET['id']) ) {
  //   $cf7_post_id = $_GET['id']; // should validate to integer?
  //
  //   if ( isset($this->post_mapping_factory) && $cf7_post_id == $this->post_mapping_factory->get_cf7_post_id() ) {
  //     $post_mapper = $this->post_mapping_factory;
  //   } else {
  //     $post_mapper = Cf7_2_Post_System::get_factory($cf7_post_id);
  //     $this->post_mapping_factory = $post_mapper;
  //   }
  // }

  /**
   * Mapping action
   */
  // $is_new_mapping = true;
  //
  // switch ( $post_mapper->get('map') ) {
  //   case 'draft':
  //     $is_new_mapping = true;
  //     break;
  //   case 'publish':
  //     $is_new_mapping = false;
  //     break;
  // }

  /**
   * styles
   */
  // $published_class = '';
  //
  // if ( !$is_new_mapping ) {
  //   $published_class = ' class="mapping-published"';
  // }

   /**
    * Loop taxonomies
    */
$mapped_taxonomy = $post_mapper->get_mapped_taxonomy();
foreach( $mapped_taxonomy as $cf7_field => $post_taxonomy ) :
  $taxonomy =  $post_mapper->get_taxonomy($post_taxonomy);
  ?>
  <li>
    <div class="custom-taxonomy-field cf7-2-post-field">
      <label class="taxonomy-label-field cf7-2-post-map-labels">
        <span class="taxonomy-name">
          <strong><?= $taxonomy['name']; ?></strong>
        </span>&nbsp;
        (<span class="enabled link-button edit-taxonomy"><?= __('Edit', 'post-my-contact-form-7')?></span>)
      </label>
      <select class="select-hybrid field-options taxonomy-options" name="cf7_2_post_map_taxonomy_value-<?=$post_taxonomy;?>" value="<?=$cf7_field?>">
        <option class="default-option" selected="true" value="">
          <?= __('Select a form field', 'post-my-contact-form-7' )?>
        </option>
        <option class="filter-option" value="c2p_filter-<?=$post_mapper->get('type')?>-<?=$post_taxonomy?>">
          <?=__('Hook with a filter', 'post-my-contact-form-7' )?>
        </option>
      </select>
      <span class="dashicons dashicons-minus remove-field"></span>
    </div>
    <span class="cf7-post-msg"></span>
    <div class="custom-taxonomy-input-fields hide-if-js">
      <p>
        <?= __('Choose a taxonomy, in blue are existing public taxonomies', 'post-my-contact-form-7'); ?>
      </p>
      <?= $post_mapper->get_taxonomy_listing($post_taxonomy); ?>
      <label for="cf7_2_post_map_taxonomy_names-<?=$post_taxonomy; ?>">
        <strong><?= __('Plural Name', 'post-my-contact-form-7')?></strong>
      </label>
      <?php $readonly = ('system'==$taxonomy['source']) ? 'readonly="true"' : '';?>
      <input class="cf7-2-post-map-labels plural-name" type="text" <?=$readonly?> name="cf7_2_post_map_taxonomy_names-<?= $post_taxonomy; ?>" value="<?= $taxonomy['name']?>" />
      <label for="cf7_2_post_map_taxonomy_name-<?= $post_taxonomy; ?>">
        <strong><?=__('Singular Name', 'post-my-contact-form-7'); ?></strong>
      </label>
      <input class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?=$post_taxonomy; ?>" <?=$readonly?> value="<?=$taxonomy['singular_name']; ?>">
      <label for="cf7_2_post_map_taxonomy_slug-<?=$post_taxonomy; ?>">
        <strong><?=__('Slug', 'post-my-contact-form-7'); ?></strong>
      </label>
      <input class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?=$post_taxonomy; ?>" <?=$readonly?> value="<?=$post_taxonomy; ?>" />
      <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?=$post_taxonomy; ?>" value="<?=$taxonomy['source']; ?>" />
      <button class="button-link close-details">
        <span class="screen-reader-text">
          <?=__('Toggle panel: Taxonomy details', 'post-my-contact-form-7'); ?>
        </span>
        <span class="focus button save-taxonomy">
          <?= __('Save', 'post-my-contact-form-7')?>
        </span>
      </button>
    </div>
  </li>

<?php endforeach; // ENDFOREACH $mapped_taxonomy as $cf7_field => $post_taxonomy ?>
<?php
    /**
     * Default new taxonomy slug
     */
    $taxonomy_slug = sanitize_title($post_mapper->get('singular_name')) . '_categories';
  ?>
<li>
  <div class="custom-taxonomy-field cf7-2-post-field">
    <label class="taxonomy-label-field cf7-2-post-map-labels">
      <span class="taxonomy-name">
        <strong><?=__('New Categories', 'post-my-contact-form-7' )?></strong>
      </span>&nbsp;(<span class="link-button edit-taxonomy disabled"><?= __('Edit', 'post-my-contact-form-7')?></span>)
    </label>
    <select disabled="true" class="field-options taxonomy-options" name="cf7_2_post_map_taxonomy_value-<?=$taxonomy_slug;?>" value="">
      <option class="default-option" selected="true" value="">
        <?= __('Select a form field', 'post-my-contact-form-7' )?>
      </option>
      <option class="filter-option" value="c2p_filter-<?=$post_mapper->get('type')?>-<?=$taxonomy_slug?>">
        <?=__('Hook with a filter', 'post-my-contact-form-7' )?>
      </option>
    </select>
    <span class="dashicons dashicons-plus add-more-field"></span>
  </div>
  <span class="cf7-post-msg"></span>
  <div class="display-none custom-taxonomy-input-fields">
    <p>
      <?= __('Choose a taxonomy, in <em>blue</em> are existing public taxonomies', 'post-my-contact-form-7'); ?>
    </p>
    <?=  $post_mapper->get_taxonomy_listing(); ?>
    <label for="cf7_2_post_map_taxonomy_names-<?=$taxonomy_slug; ?>">
      <strong><?= __('Plural Name', 'post-my-contact-form-7')?></strong>
    </label>
    <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?= $taxonomy_slug;?>" disabled="disabled" value="factory"/>
    <input disabled="disabled" class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?= $taxonomy_slug;?>" value="<?=__('New Categories', 'post-my-contact-form-7' )?>">
    <label for="cf7_2_post_map_taxonomy_name-<?= $taxonomy_slug;?>">
      <strong><?=__('Singular Name', 'post-my-contact-form-7' )?></strong>
    </label>
    <input disabled="disabled" class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?= $taxonomy_slug;?>" value="New Category">
    <label for="cf7_2_post_map_taxonomy_slug-<?= $taxonomy_slug;?>">
      <strong><?=__('Slug', 'post-my-contact-form-7' )?></strong>
    </label>
    <input disabled="disabled" class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?= $taxonomy_slug;?>" value="<?= $taxonomy_slug;?>" />
    <button class="button-link close-details">
     <span class="focus button save-taxonomy">
       <?= __('Save', 'post-my-contact-form-7')?>
     </span>
    </button>
  </div>
</li>
