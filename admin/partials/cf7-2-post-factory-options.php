<label class="post_type_labels" for="post_type">Post Type:</label>
<span id="post-type-display">
  <select name="mapped_post_type_source" id="post_type_source" class="nice-select" <?php $factory_mapping->is_published();?>>
    <option value="factory" <?php echo ('factory'==$source) ? ' selected="true"' : ''; ?>>New Post</option>
    <option value="system"<?php echo ('system'==$source) ? ' selected="true"' : ''; ?>>Existing Post</option>
  </select>
</span>
<div id="post-type-exists"<?php echo ('system'==$source)? '':' class="display-none"';?>>
  <label class="post_type_labels" for="system_post_type">Select a Post</label>
  <select id="system-post-type" class="nice-select right" name="system_post_type" <?php $factory_mapping->is_published();?>>
    <option value="">Select a Post</option>
    <?php echo $factory_mapping->get_system_posts_options();?>
  </select>
</div>
<div id="post-type-select" <?php echo ('system'==$source)?' class="display-none"':'';?>> <!--class="hide-if-js"-->
  <label for="custom_post_type" class="post_type_labels">Post type</label>
  <input name="custom_post_type" <?php $factory_mapping->is_published();?> id="custom_post_type" value="<?php echo $factory_mapping->get('type');?>" type="text">

  <label for="mapped_post_singular_name" class="post_type_labels">Singular name</label>
  <input name="mapped_post_singular_name"  <?php $factory_mapping->is_published();?> id="post_singular_name" value="<?php echo $factory_mapping->get('singular_name');?>" type="text">
  <label for="mapped_post_plural_name" class="post_type_labels">Plural name</label>
  <input name="mapped_post_plural_name" <?php $factory_mapping->is_published();?>  id="post_plural_name" value="<?php echo $factory_mapping->get('plural_name');?>" type="text">
  <p class="post-type-display">
    Attributes
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
