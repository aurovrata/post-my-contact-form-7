<?php
$post_type = $factory_mapping->get('type');
$post_obj = get_post_type_object( $post_type );
echo $post_obj->labels->name;

 ?>
<label class="post_type_labels" for="post_type">Post Type:</label>
<input type="hidden" name="mapped_post_type_source" id="post_type_source" value="system"/>
<span id="post-type-display">Existing Post</span>
<label class="post_type_labels">Post:</label>
<input type="hidden" id="system-post-type" name="system_post_type" value="<?php echo $factory_mapping->get('type') ?>"/>
<span><?= $post_type ?></span>
