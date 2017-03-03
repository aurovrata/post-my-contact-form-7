<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */
 //TODO: add a check box to include or not address fields
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="control-box cf7_2_post-save">
  <fieldset>
    <legend>Save button for Post My CF7 Form extension</legend>
    <table id="cf7_2_post-tag-generator" class="form-table">
      <tbody>
    	<tr>
      	<th scope="row"><label for="tag-generator-panel-submit-values">Label</label></th>
      	<td><input name="values" class="oneline" id="tag-generator-panel-save-values" type="text"></td>
    	</tr>
    	<tr>
      	<th scope="row"><label for="tag-generator-panel-submit-id">Id attribute</label></th>
      	<td><input name="id" class="idvalue oneline option" id="tag-generator-panel-save-id" type="text"></td>
    	</tr>
    	<tr>
      	<th scope="row"><label for="tag-generator-panel-submit-class">Class attribute</label></th>
      	<td><input name="class" class="classvalue oneline option" id="tag-generator-panel-save-class" type="text"></td>
    	</tr>
      </tbody>
    </table>
  </fieldset>
</div>
<div class="insert-box">
  <input type="hidden" name="values" value="" />
  <input type="text" name="save" class="tag code" readonly="readonly" onfocus="this.select()" />

  <div class="submitbox">
      <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
  </div>

  <br class="clear" />
</div>
