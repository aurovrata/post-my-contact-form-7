<?php
$post_type = $factory_mapping->get('type');
$post_obj = get_post_type_object( $post_type );
?>
<label class="post_type_labels" for="post_type"><?=__('Post Type:', 'post-my-contact-form-7')?></label>
<input type="hidden" name="mapped_post_type_source" id="post_type_source" value="system"/>
<span id="post-type-display"><?=__('Existing Post', 'post-my-contact-form-7')?></span>
<label class="post_type_labels"><?=__('Post:', 'post-my-contact-form-7')?></label>
<input type="hidden" id="system-post-type" name="system_post_type" value="<?= $factory_mapping->get('type') ?>"/>
<span><?= $post_type ?></span>
