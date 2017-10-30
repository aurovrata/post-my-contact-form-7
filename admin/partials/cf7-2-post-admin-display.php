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
$source = $factory_mapping->get('type_source');
?>
<div class="wrap">
  <h1>Save Form as Custom Post <span id="custom-post-title"><?php echo $factory_mapping->get('plural_name');?></span></h1>
  <form id="cf7-post-mapping-form" method="post">
    <input type="hidden" name="action" value="save_post_mapping"/>
<?php  //wp_nonce_field( $action, $field_name, $show_referer_field, $echo_field )
    wp_nonce_field('cf7_2_post_mapping', 'cf7_2_post_nonce', false, true);
    /* Used to save closed meta boxes and their order */
    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
    wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
        <div id="postbox-container-1" class="postbox-container">
            <!-- Post the post type and udpate button here -->
          <div style="" id="side-sortables" class="meta-box-sortables ui-sortable">
            <div id="submitdiv" class="postbox ">
              <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button>
              <h2 class="hndle ui-sortable-handle"><span>Create</span></h2>
              <div class="inside">
                <div class="createbox" id="createpost">
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section misc-pub-post-type">
                      <input name="cf7_post_id" id="cf7_post_id" value="<?php echo $cf7_post_id;?>" type="hidden">
                      <input name="mapped_post_type" <?php $factory_mapping->is_published();?> id="mapped_post_type" value="<?php echo $factory_mapping->get('type');?>" type="hidden">
                    <?php if('system' == $source):?>
                      <label class="post_type_labels" for="post_type">Post Type:</label>
                      <input type="hidden" name="mapped_post_type_source" id="post_type_source" value="system"/>
                      <span id="post-type-display">Existing Post</span>
                      <label class="post_type_labels">Post:</label>
                      <input type="hidden" id="system-post-type" name="system_post_type" value="<?php echo $factory_mapping->get('type') ?>"/>
                      <span><?php echo $factory_mapping->get('type') ?></span>
                    <?php else:?>
                      <label class="post_type_labels" for="post_type">Post Type:</label>
                      <span id="post-type-display">
                        <?php if('filter'!==$source):?>
                        <select name="mapped_post_type_source" id="post_type_source" <?php $factory_mapping->is_published();?>>
                          <option value="factory" <?php echo ('factory'==$source) ? ' selected="true"' : ''; ?>>New Post</option>
                          <option value="system"<?php echo ('system'==$source) ? ' selected="true"' : ''; ?>>Existing Post</option>
                          <option style="color:red;" value="filter"<?php echo ('filter'==$source) ? ' selected="true"' : ''; ?>>Action hook</option>
                        </select>
                        <?php else:?>
                        Action Hook<input type="hidden" name="mapped_post_type_source" value="filter">
                        <?php endif;?>
                      </span>
                      <?php if('filter'!==$source):?><!-- post-type-select -->
                      <div id="post-type-select" <?php echo ('system'==$source)?' class="display-none"':'';?>> <!--class="hide-if-js"-->
                        <label for="custom_post_type" class="post_type_labels">Post type</label>
                        <input name="custom_post_type" <?php $factory_mapping->is_published();?> id="custom_post_type" value="<?php echo $factory_mapping->get('type');?>" type="text">

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
                        <input id="menu-position-checkbox" type="checkbox" <?php $factory_mapping->is('show_in_menu','checked="checked"');?> name="mapped_post_show_in_menu"/>
                        <label class="post_type_cb_labels">show_in_menu</label><br />
                        <div id="menu-position"><label>menu_position</label>
                          <input style="width:45px;" type="number" value="<?= $factory_mapping->get('menu_position');?>" size="3" name="mapped_post_menu_position"/>
                        </div>
                        <input type="checkbox" <?php $factory_mapping->is('show_in_admin_bar','checked="checked"');?> name="mapped_post_show_in_admin_bar"/>
                        <label class="post_type_cb_labels">show_in_admin_bar</label><br />
                        <input type="checkbox" <?php $factory_mapping->is('show_in_nav_menus','checked="checked"');?> name="mapped_post_show_in_nav_menus"/>
                        <label class="post_type_cb_labels">show_in_nav_menus</label><br />
                        <input type="checkbox" <?php $factory_mapping->is('can_export','checked="checked"');?> name="mapped_post_can_export"/>
                        <label class="post_type_cb_labels">can_export</label><br />
                        <input type="checkbox" <?php $factory_mapping->is('has_archive','checked="checked"');?> name="mapped_post_has_archive"/>
                        <label class="post_type_cb_labels">has_archive</label><br />
                        <input type="checkbox" <?php $factory_mapping->is('exclude_from_search','checked="checked"');?> name="mapped_post_exclude_from_search"/>
                        <label class="post_type_cb_labels">exclude_from_search</label><br />
                        <input type="checkbox" <?php $factory_mapping->is('publicly_queryable','checked="checked"');?> name="mapped_post_publicly_queryable"/>
                        <label class="post_type_cb_labels">publicly_queryable</label><br />
                        <div class="clear"></div>
                      </div><!-- end post-type-select -->
                      <div id="post-type-exists"<?php echo ('system'==$source)? '':' class="display-none"';?>>
                        <label class="post_type_labels" for="system_post_type">Select a Post</label>
                        <select id="system-post-type" name="system_post_type" <?php $factory_mapping->is_published();?>>
                          <option value="">Select a Post</option>
                          <?php echo $factory_mapping->get_system_posts_options();?>
                        </select>
                      </div>
                      <?php endif;?>
                    <?php endif;?>
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
              </div>
            </div>
  <?php
/**
* Helper metabox with click-to-copy filter snippets.
*@since 2.4.0
*/
include plugin_dir_path(__FILE__).'/cf7-2-post-helper-metabox.php';
  ?>
          </div>
        </div><!-- #postbox-container-1 end -->
        <?php if('filter'!==$source):?> <!-- postbox-container-2 -->
        <div id="postbox-container-2" class="postbox-container">
          <?php do_meta_boxes('','normal',null); ?>
          <?php do_meta_boxes('','advanced',null); ?>
          <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div style="display: block;" id="postcustom" class="postbox  hide-if-js">
              <button type="button" class="handlediv button-link" aria-expanded="true">
                <span class="screen-reader-text">Toggle panel: Custom Fields</span>
                <span class="toggle-indicator" aria-hidden="true"></span>
              </button>
              <h2 class="hndle ui-sortable-handle"><span>Map Contact Form 7 Fields to Post Meta Fields</span></h2>
              <div class="inside">
                <div id="postcustomstuff">
                  <h2 class="handle ui-sortable-handle"><span> Default post fields</span></h2>
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
                      <select <?php $factory_mapping->is_published('select');?> class="cf7-2-post-map-labels options-<?php echo $factory_mapping->get('type')?>">
                        <option value="">Select a field</option>
                        <?php echo $factory_mapping->get_system_post_metas($factory_mapping->get('type'), $post_field)?>
                        <option value="cf72post-custom-meta-field">Custom field</option>
                      </select>
                      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>
                    <?php else: ?>
                      <input <?php $factory_mapping->is_published();?> name="cf7_2_post_map_meta-<?php echo $post_field;?>" class="cf7-2-post-map-labels" type="text" value="<?php echo $post_field;?>">
                    <?php endif; ?>
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
                  if($is_new_mapping):
                  ?>
                    <div class="custom-meta-field cf7-2-post-field">
                      <span class="spinner meta-label"></span>
                    <?php if('system' == $source): //post meta field names are saved in the form field option select name?>
                      <select disabled="disabled" class="cf7-2-post-map-labels options-<?php echo $factory_mapping->get('type')?>">
                        <option value="">Select a field</option>
                        <?php echo $factory_mapping->get_system_post_metas($factory_mapping->get('type'))?>
                        <option value="cf72post-custom-meta-field">Custom field</option>
                      </select>
                      <input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>
                    <?php else:?>
                      <input disabled="disabled" class="cf7-2-post-map-labels " type="text" name="cf7_2_post_map_meta-meta_key_1" value="meta_key_1">
                    <?php endif;?>
                      <select disabled="disabled" name="cf7_2_post_map_meta_value-meta_key_1" class="field-options">
                          <?php echo $factory_mapping->get_select_options();?>
                      </select>
                      <span class="dashicons dashicons-plus add-more-field"></span>
                    </div>
                    <p class="cf7-post-error-msg"></p>
                  <?php endif;?>
                    <div class="clear"></div>
                  </div>
                  <p>Custom fields can be used to add extra metadata to a post that you can <a href="https://codex.wordpress.org/Using_Custom_Fields">use in your theme</a>.</p>
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
                          <strong><?php echo $taxonomy['name']; ?></strong>
                        </span>&nbsp;(<span class="enabled link-button">Edit</span>)
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
                  <?php if( !$factory_mapping->is_published('boolean',false) ): ?>
                    <div class="custom-taxonomy-input-fields hide-if-js">
                      <h4>
                        Choose a taxonomy, in blue are existing public taxonomies
                      </h4>
                      <?php echo $factory_mapping->get_taxonomy_listing($post_taxonomy)?>
                      <label for="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy;?>">
                        <strong>Plural Name</strong>
                      </label>

                      <input class="cf7-2-post-map-labels plural-name" type="text" <?php $factory_mapping->is_published();?> readonly="<?echo ('system'==$taxonomy['source'])?>" name="cf7_2_post_map_taxonomy_names-<?php echo $post_taxonomy;?>" value="<?php echo $taxonomy['name'];?>">
                      <label for="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy;?>">
                        <strong>Singular Name</strong>
                      </label>
                      <input class="cf7-2-post-map-labels singular-name" type="text" <?php $factory_mapping->is_published();?> name="cf7_2_post_map_taxonomy_name-<?php echo $post_taxonomy;?>" readonly="<?echo ('system'==$taxonomy['source'])?>" value="<?php echo $taxonomy['singular_name'];?>">
                      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy;?>">
                        <strong>Slug</strong>
                      </label>
                      <input class="cf7-2-post-map-labels taxonomy-slug" type="text" <?php $factory_mapping->is_published();?> name="cf7_2_post_map_taxonomy_slug-<?php echo $post_taxonomy;?>" readonly="<?echo ('system'==$taxonomy['source'])?>" value="<?php echo $post_taxonomy;?>" />
                      <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo $post_taxonomy;?>" <?php $factory_mapping->is_published();?> value="<?php echo $taxonomy['source'];?>"/>
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
                      <select disabled="disabled" name="cf7_2_post_map_taxonomy_value-<?php echo $taxonomy_slug;?>" class="field-options">
                          <?php echo $factory_mapping->get_taxonomy_select_options();?>
                      </select>
                      <span class="dashicons dashicons-plus add-more-field"></span>
                    </div>
                    <p class="cf7-post-error-msg"></p>
                    <div class="clear"></div>
                    <div class="custom-taxonomy-input-fields hide-if-js">
                      <h3>
                        Choose a taxonomy, in blue are existing public taxonomies
                      </h3>
                      <?php echo $factory_mapping->get_taxonomy_listing()?>
                      <label for="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug;?>">
                        <strong>Plural Name</strong>
                      </label>
                      <input type="hidden" class="taxonomy-source"  name="cf7_2_post_map_taxonomy_source-<?php echo $taxonomy_slug;?>" disabled="disabled" value="factory"/>
                      <input disabled="disabled" class="cf7-2-post-map-labels plural-name" type="text" name="cf7_2_post_map_taxonomy_names-<?php echo $taxonomy_slug;?>" value="New Categories">
                      <label for="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug;?>"><strong>Singular Name</strong></label>
                      <input disabled="disabled" class="cf7-2-post-map-labels singular-name" type="text" name="cf7_2_post_map_taxonomy_name-<?php echo $taxonomy_slug;?>" value="New Category">
                      <label for="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug;?>"><strong>Slug</strong></label>
                      <input disabled="disabled" class="cf7-2-post-map-labels taxonomy-slug" type="text" name="cf7_2_post_map_taxonomy_slug-<?php echo $taxonomy_slug;?>" value="<?php echo $taxonomy_slug;?>" />
                      <button type="button" class="button-link close-details" aria-expanded="true">
                        <span class="wp-core-ui button" aria-hidden="true">Save</span>
                      </button>
                    </div>
                  <?php endif;?>
                  </div>
                </div>
              </div><!-- .inside end -->
            </div>
          </div>
        </div> <!-- #postbox-container-2 end -->
      <?php endif;?>
        <div id="postbox-container-3" class="postbox-container<?= ('filter' == $source ) ? '':' display-none' ?>">
          <div id="normal-sortables" class="meta-box-sortables ui-sortable">
            <div style="display: block;" id="postcustom" class="postbox  hide-if-js">
              <button type="button" class="handlediv button-link" aria-expanded="true">
                <span class="screen-reader-text">Toggle panel: Custom Fields</span>
                <span class="toggle-indicator" aria-hidden="true"></span>
              </button>
              <h2 class="hndle ui-sortable-handle"><span>Map Contact Form 7 Fields with an Action hook</span></h2>
              <div class="inside">
                <div id="postcustomstuff">
                  <p class="info">
                    Hook the following acton hooks to programmatically map your form submission.
                  </p>
                  <p>
                    <strong>Mapping Form submissions</strong>: hook the following action,<br />
                    <span class="code">add_action( '<span class="code action-form-map animate-change">cf7_2_post_save_submission</span>', 'your_function_name',10,3);<br />
                    function your_function_name($cf7_key, $submitted_data, $submitted_files){}</span>
                  </p>
                  <p>
                    <strong>Pre-loading the form</strong>: hook the following filter,<br />
                    <span class="code">add_filter( '<span class="code filter-form-load animate-change">cf7_2_post_load_form</span>', 'your_function_name',10,5);<br />
                    function your_function_name( $field_value_pairs, $cf7_key, $form_fields, $form_field_options, $cf7_post_id){<br />
                      &nbsp;&nbsp;//$form_field_options options set in the form field tags<br />
                      &nbsp;&nbsp;//$cf7_post_id the cf7 form id in case you need to load the form object<br />
                      &nbsp;&nbsp;foreach($form_fields as $field=>$type){<br />
                      &nbsp;&nbsp;&nbsp;&nbsp;$field_value_pairs[$field] = '';//load your value<br />
                      &nbsp;&nbsp;}<br />
                      &nbsp;&nbsp;//if this is a saved draft form, you can set your mapped post id<br />
                      &nbsp;&nbsp;//it will be set as hidden field so you can map the (re)submission to the same post<br />
                      &nbsp;&nbsp;$field_value_pairs['map_post_id'] = $post_id;<br />
                      &nbsp;&nbsp;return $field_value_pairs;<br />
                    }</span>
                  </p>

                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </form>
</div>
