<?php
$default_post_fields = '<div class="post-meta-field cf7-2-post-field"><label class="cf7-2-post-map-labels" for="cf7_2_post_map-%2$s"><strong>%1$s</strong></label>';
$default_post_fields .= '<select '.($is_new_mapping ? '':'disabled').' name="cf7_2_post_map-%2$s" class="field-options post-options nice-select">';
$default_post_fields .= '%3$s';
$default_post_fields .= '</select></div>';
$default_post_fields .= '<p class="cf7-post-error-msg"></p>';
$default_post_fields .= '<div class="clear"></div>';
 ?>
<div id="postcustomstuff" class="postbox">
  <h2 class="handle ui-sortable-handle"><span> <?=__('Default post fields', 'post-my-contact-form-7')?></span></h2>
  <div class="default-post-fields">
<?php
  if($factory_mapping->supports('title')){
    echo sprintf($default_post_fields, __('Post title', 'post-my-contact-form-7' ), 'title', $factory_mapping->get_select_options('title'));
  }
  echo sprintf($default_post_fields, __('Post slug', 'post-my-contact-form-7' ), 'slug', $factory_mapping->get_select_options('slug'));
  if($factory_mapping->supports('author')){
    echo sprintf($default_post_fields, __('Post author', 'post-my-contact-form-7' ), 'author', $factory_mapping->get_select_options('author'));
  }
  if($factory_mapping->supports('thumbnail') && $factory_mapping->has_file_field()){
    echo sprintf($default_post_fields, __('Featured image', 'post-my-contact-form-7' ), 'thumbnail', $factory_mapping->get_select_options('thumbnail'));
  }
  if($factory_mapping->supports('editor')){
    echo sprintf($default_post_fields, __('Post Content', 'post-my-contact-form-7' ), 'editor', $factory_mapping->get_select_options('editor'));
  }
  if($factory_mapping->supports('excerpt')){
    echo sprintf($default_post_fields, __('Post Excerpt', 'post-my-contact-form-7' ), 'excerpt', $factory_mapping->get_select_options('excerpt'));
  }
  $published_class = '';
  if(!$is_new_mapping) $published_class = ' class="mapping-published"';
?>
  </div>
</div>
