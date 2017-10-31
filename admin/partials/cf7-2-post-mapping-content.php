<?php
$default_post_fields = '<div class="post-meta-field cf7-2-post-field"><label class="cf7-2-post-map-labels" for="cf7_2_post_map-%2$s"><strong>%1$s</strong></label>';
$default_post_fields .= '<select '.($is_new_mapping ? '':'disabled').' name="cf7_2_post_map-%2$s" class="field-options post-options nice-select">';
$default_post_fields .= '%3$s';
$default_post_fields .= '</select></div>';
$default_post_fields .= '<p class="cf7-post-error-msg"></p>';
$default_post_fields .= '<div class="clear"></div>';
 ?>
<div id="postcustomstuff" class="postbox">
  <h2 class="handle ui-sortable-handle"><span> Default post fields</span></h2>
  <div class="default-post-fields">    
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
  </div>
</div>
