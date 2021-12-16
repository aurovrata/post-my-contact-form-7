<?php

/**
 * PDisplay OTP tab settings in CF& editor page.
 *
 * @link       https://profiles.wordpress.org/aurovrata/
 * @since      5.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

 // require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-cf7-2-posst-factory.php' ;

 //action
 $is_new_mapping = true;
 switch($post_mapper->get('map')){
   case 'draft':
     $is_new_mapping = true;
     break;
   case 'publish':
     $is_new_mapping = false;
     break;
 }
 $post_type = $post_mapper->get('type');
 // debug_msg($post_mapper, 'post mapper ');
 $source = $post_mapper->get('type_source');
 $post_name = '';
 $class_factory = '';
 $system_factory=' display-none';
 switch($source){
   case 'factory':
     $post_name = $post_mapper->get('plural_name');
     break;
   case 'system':
     $post_obj = get_post_type_object( $post_type );
     $post_name = $post_obj->labels->name;
     $class_factory = ' display-none';
     $system_factory='';
     break;
 }
 $post_fields = '
   <li id="c2p-%2$s">
     <div class="cf7-2-post-field">
       <label class="cf7-2-post-map-labels" for="cf7-2-%2$s"><strong>%1$s</strong></label>
       <select id="cf7-2-%2$s" value="%3$s" name="cf7_2_post_map-%2$s" class="field-options post-options select-hybrid">
         <option class="default-option" value="">'. __('Select a form field', 'post-my-contact-form-7' ). '</option>
         <option class="filter-option" value="cf7_2_post_filter-'.$post_type.'-%2$s">'.__('Hook with a filter', 'post-my-contact-form-7' ). '</option>
       </select>
     </div><span class="cf7-post-msg"></span>
   </li>';
   //%1 - Label.
   //%2 - field id/name.
   //%3 - mapped form field.
 ?>
<h1><?= esc_html('Save submissions as ','post-my-contact-form-7' ); ?><span id="custom-post-title"><?= $post_name;?>&colon;&nbsp;<code><?= $post_type?></code></span></h1>
   <!-- $form = get_post($cf7_post_id); ?> -->
<input type="hidden" id="c2p-cf7-key" value="<?=$post_mapper->cf7_key?>"/>
<input type="hidden" id="c2p-mapping-changed" name="c2p_mapping_changes" value="0"/>
<input type="hidden" id="c2p-active-tab" name="c2p_active_tab" value="0"/>
<input type="hidden" id="c2p-mapping-status" name="mapped_post_map" value="<?=$post_mapper->get('map')?>"/>
<input type="hidden" name="mapped_post_default" value="<?=$post_mapper->get('default')?>"/>
<input name="mapped_post_type"  id="mapped-post-type" value="<?= $post_mapper->get('type');?>" type="hidden">

 <?php wp_nonce_field('cf7_2_post_mapping', 'cf7_2_post_nonce', false, true);?>

<div id="c2p-factory-post">
  <div class="c2p-title-header">
    <h2><?=__('Map form to...','post-my-contact-form-7' )?></h2>
    <div class="toggle toggle-light"></div>
  </div>
   <p>
     <?= __('You can either map your form to a custom post or an existing post type.','post-my-contact-form-7');?>
   </p>
   <div>
      <label class="post_type_labels" for="post-type-source"><?=__('Post Type:','post-my-contact-form-7')?></label>
      <span id="post-type-display">
        <select name="mapped_post_type_source" id="post-type-source" class="select-hybrid" >
          <option value="factory" <?= ('factory'==$source) ? ' selected="true"' : ''; ?>><?= __('New Post','post-my-contact-form-7')?></option>
          <option value="system"<?= ('system'==$source) ? ' selected="true"' : ''; ?>><?=__('Existing Post','post-my-contact-form-7')?></option>
        </select>
      </span>
   </div>
   <div id="post-type-exists"<?= ('system'==$source)? '':' class="display-none"';?>>
     <label class="post_type_labels" for="system-post-type"><?=__('Select a Post','post-my-contact-form-7')?></label>
     <select id="system-post-type" class="select-hybrid" name="system_post_type" >
       <?= $factory->get_system_posts_options($post_mapper->get('type'));?>
     </select>
   </div>
   <div id="post-type-select" <?= ('system'==$source)?' class="display-none"':'';?>> <!--class="hide-if-js"-->
     <label for="custom-post-type" class="post-type-labels"><?=__('Post type', 'post-my-contact-form-7')?><input name="custom_post_type"  id="custom-post-type" value="<?= $post_mapper->get('type');?>" type="text"/></label>

     <label for="mapped_post_singular_name" class="post_type_labels"><?=__('Singular name', 'post-my-contact-form-7');?><input name="mapped_post_singular_name"   id="post_singular_name" value="<?= $post_mapper->get('singular_name');?>" type="text"/></label>

     <label for="post-plural-name" class="post_type_labels"><?=__('Plural name','post-my-contact-form-7')?><input name="mapped_post_plural_name"  id="post-plural-name" value="<?= $post_mapper->get('plural_name');?>" type="text"/></label>

     <p class="post-type-display">
       <?=__('Attributes','post-my-contact-form-7')?>
     </p>
     <label class="post_type_cb_labels">
        <input type="checkbox" <?= $post_mapper->is('hierarchical','checked="checked"');?> name="mapped_post_hierarchical" value="true" class="c2cpt-attribute"/>hierarchical</label>
     <label class="post_type_cb_labels">
        <input type="checkbox" <?= $post_mapper->is('public','checked="checked"');?> name="mapped_post_public" value="true" class="c2cpt-attribute"/>public</label>
     <label class="post_type_cb_labels">
        <input type="checkbox" <?= $post_mapper->is('show_ui','checked="checked"');?> name="mapped_post_show_ui" value="true" class="c2cpt-attribute"/>show_ui</label>
     <label class="post_type_cb_labels">
        <input id="menu-position-checkbox" type="checkbox" <?= $post_mapper->is('show_in_menu','checked="checked"');?> name="mapped_post_show_in_menu" value="true" class="c2cpt-attribute"/>show_in_menu</label>
     <div id="menu-position">
        <label class="post_type_cb_labels">menu_position<input style="width:45px;" type="number" value="<?= $post_mapper->get('menu_position');?>" size="3" name="mapped_post_menu_position" class="c2cpt-attribute"/></label>
     </div>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is('show_in_admin_bar','checked="checked"');?> name="mapped_post_show_in_admin_bar"  value="true" class="c2cpt-attribute"/>show_in_admin_bar</label>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is( 'show_in_nav_menus', 'checked="checked"' );?> name="mapped_post_show_in_nav_menus" value="true" class="c2cpt-attribute"/>show_in_nav_menus</label>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is('can_export','checked="checked"');?> name="mapped_post_can_export" value="true" class="c2cpt-attribute"/>can_export</label>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is('has_archive','checked="checked"');?> name="mapped_post_has_archive" value="true" class="c2cpt-attribute"/>has_archive</label>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is('exclude_from_search','checked="checked"');?> name="mapped_post_exclude_from_search" value="true" class="c2cpt-attribute"/>exclude_from_search</label>
     <label class="post_type_cb_labels"><input type="checkbox" <?= $post_mapper->is('publicly_queryable','checked="checked"');?> name="mapped_post_publicly_queryable" value="true" class="c2cpt-attribute"/>publicly_queryable</label>
     <p>
        <?= sprintf(__('To understand how to parametrise your custom post, please read the WordPress post registration <a href="%s">documentation</a>.','post-my-contact-form-7'), 'https://developer.wordpress.org/reference/functions/register_post_type/#parameter-detail-information');?>
     </p>
   </div><!-- end post-type-select -->
</div>
<h2><?=__('Map form fields to default post fields', 'post-my-contact-form-7')?></h2>
<div id="c2p-mapped-fields">
   <ul id="c2p-default-post-fields">
      <?php
      echo sprintf( $post_fields, __('Post title', 'post-my-contact-form-7' ),'title',$post_mapper->get_mapped_form_field('title'));
      echo sprintf($post_fields, __('Post Content', 'post-my-contact-form-7' ),'editor',$post_mapper->get_mapped_form_field('editor'));
      echo sprintf($post_fields, __('Post Excerpt', 'post-my-contact-form-7' ),'excerpt',$post_mapper->get_mapped_form_field('excerpt'));
      echo sprintf($post_fields, __('Featured image', 'post-my-contact-form-7' ),'thumbnail',$post_mapper->get_mapped_form_field('thumbnail'));
      echo sprintf($post_fields, __('Post slug', 'post-my-contact-form-7' ),'slug',$post_mapper->get_mapped_form_field('slug'));
      echo sprintf($post_fields, __('Post author', 'post-my-contact-form-7' ),'author',$post_mapper->get_mapped_form_field('author'));
      ?>
   </ul>
   <h2><?=__('Map form fields to post meta-fields','post-my-contact-form-7' )?></h2>
   <ul id="c2p-post-meta-fields">
      <?php include_once 'cf7-2-post-field-metabox.php'; ?>
   </ul>
   <?= $factory->get_all_metafield_menus();?>
   <p><?=__('Custom fields can be used to add extra metadata to a post that you can <a href="https://codex.wordpress.org/Using_Custom_Fields">use in your theme</a>','post-my-contact-form-7')?>.</p>
   <h2><?=__('Map form fields to post taxonomy','post-my-contact-form-7' )?></h2>
   <p>
      <?= sprintf(
         __('Only %1$s form fields can be mapped to a taxonomy, create the field with empty options and the plugin will populate the field with the taxonomy terms it is mapped to.','post-my-contact-form-7' ), '<strong>checkbox|radio|select</strong>')?>
   </p>
   <ul id="c2p-tax-notes">
     <li>
       <?= sprintf(
          __('You can now use the %1$s in your %2$s field tag, to convert your field into a %3$s field','post-my-contact-form-7' ), '<strong>class:hybrid-select</strong>', '<em>[checkbox|radio|select]</em>', '<a href="https://aurovrata.github.io/hybrid-html-dropdown/">Hybrid Dropdown</a>')?>
     </li>
     <li>
       <?= sprintf(
          __('You can now use the %1$s in your %2$s field tag, to convert your field into a %3$s field','post-my-contact-form-7' ), '<strong>class:js-select2</strong>', '<em>[select]</em>', '<a href="https://select2.org/">Select2</a>')?>
     </li>
     <li>
       <?=
       sprintf(
         __('For more details see this <a href="%1$s">YouTube tutorial</a>.','post-my-contact-form-7' ),
       'https://www.youtube.com/watch?v=9lK9eHFhGPk&list=PLblJwjs_dFBtQzwPMoMFf-vsXkhuKHKhV');
       ?>
     </li>
   </ul>
   <ul id="c2p-taxonomy-fields">
      <?php include_once 'cf7-2-post-taxonomy-metabox.php'; ?>
   </ul>
</div>

<div>
  <?php if(!defined('CF7_GRID_VERSION')){
    $closed ='';
    echo '<h2>'.__('Hooks & Filters to customise the mapping', 'post-my-contact-form-7').'</h2>';
    include_once 'cf7-2-post-helper-metabox.php';
  }?>
</div>
