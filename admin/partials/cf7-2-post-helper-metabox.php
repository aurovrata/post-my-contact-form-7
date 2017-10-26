<?php
//helper snippets
?>
<div id="helperdiv" class="postbox">
  <button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Helper</span><span class="toggle-indicator" aria-hidden="true"></span></button>
  <h2 class="hndle ui-sortable-handle"><span>Actions &amp; Filters</span></h2>
  <div class="inside">
    <p>Click on a link to copy the helper snippet code and paste it in your <em>functions.php</em> file.</p>
    <ul class="helper-list">
      <li>
        <a class="helper" data-cf72post="add_action( 'cf7_2_post_form_mapped_to_{$post_type}','new_{$post_type}_mapped',10,3);
function new_{$post_type}_mapped($post_id, $cf7_form_data, $cf7form_key){
  //$post_id is the ID of the post to which the form values are being mapped to
  // $form_data is the submitted form data as an array of field-name=>value pairs
  //$cf7form_key unique form key to identify your form.

}" href="javascript:void(0);">Action</a> after submission is saved to mapped post.
      </li>
      <li>
        <a class="helper" data-cf72post="add_filter( 'cf7_2_post_filter_cf7_field_value','field_default_value',10,4);
function field_default_value($value, $cf7_id, $field, $cf7form_key){
  //$value to be filtered
  // $field is the current field being loaded.
  //$cf7form_key unique form key to identify your form, $cf7_id is its post_id.
  if('contact-us'!==$cf7form_key ){
    return $value;
  }
  //assuming my target visitors are from Chennai, India, I could pre-fill the fields your-location and your-country as,
  switch($field){
    case 'your-location':
      $value = 'Chennai';
      break;
    case 'your-country':
      $value = 'India';
      break;
  }
  return $value;
}" href="javascript:void(0);">Filter</a> default field value when form is displayed.
      </li>
    </ul>
  </div>
</div>
<script type="text/javascript">
(function($){
	$(document).ready( function(){
    $('#helperdiv .helper-list li a').each(function(){
      new Clipboard($(this)[0], {
        text: function(trigger) {
          var $target = $(trigger);
          var text = $target.data('cf72post');
          //get postType
          var postType = $('#mapped_post_type').val();
          return text.replace(/\{\$post_type\}/gi, postType);
        }
      });
    });
  });
})(jQuery)
</script>
