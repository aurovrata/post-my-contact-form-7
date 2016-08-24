<?php

/**
 * Provide a admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 */

require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-cf7-2-post-factory.php' ;
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

$default_post_fields = '<div class="post-meta-field cf7-2-post-field"><label class="cf7-2-post-map-labels" for="cf7_2_post_map-%2$s"><strong>%1$s</strong></label>';
$default_post_fields .= '<select '.($is_new_mapping ? '':'disabled').' name="cf7_2_post_map-%2$s" class="field-options post-options">';
$default_post_fields .= '%3$s';
$default_post_fields .= '</select></div>';
$default_post_fields .= '<p class="cf7-post-error-msg"></p>';
$default_post_fields .= '<div class="clear"></div>';
?>
<div class="wrap">
  <h1>Save Form as Post</h1>
  <form id="cf7-post-mapping-form" method="post">
    <input type="hidden" name="action" value="save_post_mapping"/>
<?php  //wp_nonce_field( $action, $field_name, $show_referer_field, $echo_field )
    wp_nonce_field('cf7_2_post_mapping', 'cf7_2_post_nonce', false, true);
?>
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <div id="postbox-container-1" class="postbox-container">
            <!-- Post the post type and udpate button here -->
          <div style="" id="side-sortables" class="meta-box-sortables ui-sortable">
            <div id="submitdiv" class="postbox ">
              <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button>
              <h2 class="hndle ui-sortable-handle"><span>Create</span></h2>
              <div class="inside">
                <div class="createbox" id="createpost">
                  <div id="minor-publishing">
                    <div id="save-draft-actions" <?php echo ($is_new_mapping ? '': 'style="display:none;"');?>>
                      <div id="save-draft-action">
                        <span class="spinner save_draft"></span>
                        <input name="submit" onclick="this.form.submited=this.id;" id="save_draft" value="Save Draft" class="button" type="submit">
                      </div>
                      <div class="clear"></div>
                    </div><!-- #minor-publishing-actions -->
                    <div id="misc-publishing-actions">
                      <div class="misc-pub-section misc-pub-post-type">
                        <label for="post_type">Post Type:</label>
                        <span id="post-type-display"><?php echo $factory_mapping->get('type');?></span>
                        <a href="#post_type" class="edit-post-type hide-if-no-js"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">Edit status</span></a>
                        <div id="post-type-select" class="hide-if-js">
                          <label for="mapped_post_type" class="post_type_labels">Post type</label>
                          <input name="mapped_post_type" <?php $factory_mapping->is_published();?> id="mapped_post_type" value="<?php echo $factory_mapping->get('type');?>" type="text">
                          <input name="mapped_post_type_source" id="mapped_post_type" value="<?php echo $factory_mapping->get('type_source');?>" type="hidden">
                          <input name="cf7_post_id" id="cf7_post_id" <?php $factory_mapping->is_published();?>  value="<?php echo $cf7_post_id;?>" type="hidden">
                          <label for="mapped_post_singular_name" class="post_type_labels">Singular name</label>
                          <input name="mapped_post_singular_name"  <?php $factory_mapping->is_published();?> id="post_singular_name" value="<?php echo $factory_mapping->get('singular_name');?>" type="text">
                          <label for="mapped_post_plural_name" class="post_type_labels">Plural name</label>
                          <input name="mapped_post_plural_name" <?php $factory_mapping->is_published();?>  id="post_plural_name" value="<?php echo $factory_mapping->get('plural_name');?>" type="text">
                          <p class="post-type-display">
                            Capabilities
                          </p>
                          <input type="checkbox" <?php $factory_mapping->is('hierarchical','checked="checked"');?> name="mapped_post_hierarchical"/>
                          <label class="post_type_cb_labels">hierarchical</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('public','checked="checked"');?> name="mapped_post_public"/>
                          <label class="post_type_cb_labels">public</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('show_ui','checked="checked"');?> name="mapped_post_show_ui"/>
                          <label class="post_type_cb_labels">show_ui</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('show_in_menu','checked="checked"');?> name="mapped_post_show_in_menu"/>
                          <label class="post_type_cb_labels">show_in_menu</label><br />
                          <div id="post_type_is_menu_position"><label>menu_position</label>
                            <input style="width:45px;" type="number" value="<?php $factory_mapping->get('menu_position');?>" size="3" name="mapped_post_menu_position"/>
                          </div>
                          <input type="checkbox" <?php $factory_mapping->is('show_in_admin_bar','checked="checked"');?> name="post_type_is_show_in_admin_bar"/>
                          <label class="post_type_cb_labels">show_in_admin_bar</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('show_in_nav_menus','checked="checked"');?> name="post_type_is_show_in_nav_menus"/>
                          <label class="post_type_cb_labels">show_in_nav_menus</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('can_export','checked="checked"');?> name="mapped_post_can_export"/>
                          <label class="post_type_cb_labels">can_export</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('has_archive','checked="checked"');?> name="mapped_post_has_archive"/>
                          <label class="post_type_cb_labels">has_archive</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('exclude_from_search','checked="checked"');?> name="mapped_post_exclude_from_search"/>
                          <label class="post_type_cb_labels">exclude_from_search</label><br />
                          <input type="checkbox" <?php $factory_mapping->is('publicly_queryable','checked="checked"');?> name="mapped_post_exclude_publicly_queryable"/>
                          <label class="post_type_cb_labels">publicly_queryable</label><br />
                          <div class="clear"></div>
                        </div>
                      </div><!-- .misc-pub-section -->
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div id="post-creation-actions">
                    <div id="ajax-response"></div>
                    <div id="creation-action">
                      <span class="spinner save_post"></span>
                      <input name="submit" onclick="this.form.submited=this.id;" id="save_post" class="button button-primary button-large" value="<?php echo ($is_new_mapping ? 'Create': 'Update');?>" type="submit">
                    </div>
                    <div class="clear"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- #postbox-container-1 end -->
        <div id="postbox-container-2" class="postbox-container">
          <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div style="display: block;" id="postcustom" class="postbox  hide-if-js">
              <button type="button" class="handlediv button-link" aria-expanded="true">
                <span class="screen-reader-text">Toggle panel: Custom Fields</span>
                <span class="toggle-indicator" aria-hidden="true"></span>
              </button>
              <h2 class="hndle ui-sortable-handle"><span>Map Contact Form 7 Fields to Post Meta Fields</span></h2>
              <div class="inside">
                <div id="postcustomstuff">
                  <h2 class="hndle ui-sortable-handle"><span> Default post fields</span></h2>
                <?php
                    if($factory_mapping->supports('title')){
                      echo sprintf($default_post_fields, __('Post title', 'cf7_2_post'), 'title', $factory_mapping->get_select_options('title'));
                    }
                    echo sprintf($default_post_fields, __('Post slug', 'cf7_2_post'), 'slug', $factory_mapping->get_select_options('slug'));
                    if($factory_mapping->supports('author')){
                      echo sprintf($default_post_fields, __('Post author', 'cf7_2_post'), 'author', $factory_mapping->get_select_options('author'));
                    }
                    if($factory_mapping->supports('thumbnail') && $factory_mapping->has_file_field()){
                      echo sprintf($default_post_fields, __('Featured image', 'cf7_2_post'), 'thumbnail', $factory_mapping->get_select_options('thumbnail'));
                    }
                    if($factory_mapping->supports('editor')){
                      echo sprintf($default_post_fields, __('Post Content', 'cf7_2_post'), 'editor', $factory_mapping->get_select_options('editor'));
                    }
                    if($factory_mapping->supports('excerpt')){
                      echo sprintf($default_post_fields, __('Post Excerpt', 'cf7_2_post'), 'excerpt', $factory_mapping->get_select_options('excerpt'));
                    }
                ?>
                  <h2>Custom meta fields key (no spaces allowed)</h2>
                  <div id="custom-meta-fields">
                <?php
                  $mapped_fields = $factory_mapping->get_mapped_meta_fields();
                  //debug_msg($mapped_fields, "meta fields...");
                  if(!empty($mapped_fields)){
                    foreach( $mapped_fields as $cf7_field => $post_field ){
                ?>
                    <div class="custom-meta-field cf7-2-post-field">
                        <input <?php $factory_mapping->is_published();?> name="cf7_2_post_map_meta-<?php echo $post_field;?>" class="cf7-2-post-map-labels" type="text" value="<?php echo $post_field;?>">
                        <select <?php $factory_mapping->is_published('select');?> class="field-options" name="cf7_2_post_map_meta_value-<?php echo $post_field;?>">
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
                  ?>
                    <div class="custom-meta-field cf7-2-post-field">
                        <input disabled="disabled" class="cf7-2-post-map-labels" type="text" name="cf7_2_post_map_meta-meta_key_1" value="meta_key_1">
                        <select disabled="disabled" name="cf7_2_post_map_meta_value-meta_key_1" class="field-options">
                            <?php echo $factory_mapping->get_select_options();?>
                        </select>
                        <span class="dashicons dashicons-plus add-more-field"></span>
                    </div>
                    <p class="cf7-post-error-msg"></p>
                    <div class="clear"></div>
                  </div>
                  <p>Custom fields can be used to add extra metadata to a post that you can <a href="https://codex.wordpress.org/Using_Custom_Fields">use in your theme</a>.</p>
                  <h2>Custom Taxonomy  (no spaces allowed)</h2>
                  <div id="post_taxonomy_map">
                    <?php
                      $mapped_taxonomy = $factory_mapping->get_mapped_taxonomy();

                      if(!empty($mapped_taxonomy)){
                        foreach( $mapped_taxonomy as $cf7_field => $post_taxonomy ){
                          $taxonomy = $factory_mapping->get_taxonomy($post_taxonomy);
                          //debug_msg($taxonomy, " taxonomy... ");
                    ?>
                    <div class="custom-taxonomy-field cf7-2-post-field">
                      <label class="taxonomy-label-field cf7-2-post-map-labels">
                        <span class="taxonomy-name"><strong><?php echo $taxonomy['name'];?></strong></span>&nbsp;(<span class="enabled link-button">Edit</span>)
                      </label>
                        <select <?php $factory_mapping->is_published('select');?> class="field-options" name="cf7_2_post_map_taxonomy_value-<?php echo $post_taxonomy;?>">
                            <?php echo $factory_mapping->get_taxonomy_select_options($post_taxonomy);?>
                        </select>
                        <?php if($is_new_mapping):?>
                        <span class="dashicons dashicons-minus remove-field"></span>
                      <?php endif;?>
                    </div>
                    <p class="cf7-post-error-msg"><span class="select-error-msg cf7-2-post-map-labels"></span></p>
                    <div class="clear"></div>
                    <div class="custom-taxonomy-input-fields hide-if-js">
                      <label for="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy;?>"><strong>Plural Name</strong></label>
                      <input class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy;?>" value="<?php echo $taxonomy['name'];?>">
                      <label for="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy;?>"><strong>Singular Name</strong></label>
                      <input class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy;?>" value="<?php echo $taxonomy['singular_name'];?>">
                      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy;?>"><strong>Slug</strong></label>
                      <input class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy;?>" value="<?php echo $post_taxonomy;?>" />
                      <button type="button" class="button-link close-details" aria-expanded="true">
                        <span class="screen-reader-text">Toggle panel: Taxonomy details</span>
                        <span class="wp-core-ui button" aria-hidden="true">Save</span>
                      </button>
                    </div>
                    <?php
                      }
                    }
                    $taxonomy_slug = sanitize_title( $factory_mapping->get('singular_name') ).'_categories';
                    ?>
                    <div class="custom-taxonomy-field cf7-2-post-field">
                      <label class="taxonomy-label-field cf7-2-post-map-labels">
                        <span class="taxonomy-name"><strong>Categories</strong></span>&nbsp;(<span class="disabled link-button">Edit</span>)
                      </label>
                      <select disabled="disabled" name="cf7_2_post_map_taxonomy_value-<?php echo $taxonomy_slug;?>" class="field-options">
                          <?php echo $factory_mapping->get_taxonomy_select_options();?>
                      </select>
                      <span class="dashicons dashicons-plus add-more-field"></span>
                    </div>
                    <p class="cf7-post-error-msg"></p>
                    <div class="clear"></div>
                    <div class="custom-taxonomy-input-fields hide-if-js">
                      <label for="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug;?>"><strong>Plural Name</strong></label>
                      <input disabled="disabled" class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug;?>" value="Categories">
                      <label for="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug;?>"><strong>Singular Name</strong></label>
                      <input disabled="disabled" class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug;?>" value="Category">
                      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug;?>"><strong>Slug</strong></label>
                      <input disabled="disabled" class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug;?>" value="<?php echo $taxonomy_slug;?>" />
                      <button type="button" class="button-link close-details" aria-expanded="true">
                        <span class="wp-core-ui button" aria-hidden="true">Save</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div><!-- .inside end -->
            </div>
          </div>
        </div> <!-- #postbox-container-2 end -->
      </div>
    </div>
  </form>
</div>
