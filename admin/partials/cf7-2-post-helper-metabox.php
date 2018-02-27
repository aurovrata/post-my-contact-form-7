<?php
//helper snippets
?>
<p>Click on a link to copy the helper snippet code and paste it in your <em>functions.php</em> file.</p>
<div id="admin-hooks" class="postbox closed">
  <button type="button" class="handlediv button-link" aria-expanded="false"><span class="screen-reader-text">Toggle panel: Helper</span><span class="toggle-indicator" aria-hidden="true"></span></button>
    <h3 class="hndle">Admin hooks</h3>

    <div class="inside">
      <p>
        these are triggered when your form is being mapped to a post, and can be used to customise the mapping process.
      </p>
      <ol class="helper-list">
        <li class="factory-hook">
          <a class="helper" data-cf72post="add_filter('cf7_2_post_supports_{$post_type}','set_supports');
/**
* Function to set supports asstributes for custom posts '{$post_type}' created by the Post My CF7 Form plugin.
* Hooked on 'cf7_2_post_supports_{$custom_post_type}'
* @param array $supports default set of support attributes
* @return array  an array of supports attributes.
*/
function set_supports($supports){
  $default_supports[]='comments';
  return $default_supports;
}" href="javascript:void(0);">Post Supports Filter</a> custom post <code>supports</code> attributes (<a href="https://codex.wordpress.org/Function_Reference/register_post_type#supports">documentation</a>).
      </li>
      <li class="factory-hook">
        <a class="helper" data-cf72post="add_filter('cf7_2_post_capabilities_{$post_type}', 'set_capabilities');
/**
* Function to set access  capabilities for custom posts '{$post_type}' created by the Post My CF7 Form plugin.
* Hooked on 'cf7_2_post_capabilities_{$custom_post_type}'
* @param array $capabilities default set of capabilities
* @return array  an array of capabilities.
*/
function set_capabilities($capabilities){
$capabilities = array(
    'edit_post' => 'edit_contact',
    'edit_posts' => 'edit_contacts',
    'edit_others_posts' => 'edit_others_contacts',
    'publish_posts' => 'publish_contacts',
    'read_post' => 'read_contacts',
    'read_private_posts' => 'read_private_contacts',
    'delete_post' => 'delete_contact'
);
/*All capabilities must be set, else the plugin will default back to default `post` capabilities.  Also, make sure you assign each of these capabilities to the admin role (or other roles/users) else you won't be able to access your custom post.*/
  return $capabilities;
}" href="javascript:void(0);">Post Access Filter</a> custom post access <code>capabilities</code> (<a href="http://wordpress.stackexchange.com/questions/108338/capabilities-and-custom-post-types">documentation</a>).
      </li>
      <li class="taxonomy-hook">
        <a class="helper" data-cf72post="add_filter('cf7_2_post_filter_taxonomy_registration-{$taxonomy_slug}', 'register_custom_tags');
/**
* Function to modify the registration of the custom taxonomy '{$taxonomy_slug}' created by the Post My CF7 Form plugin.
* Hooked on 'cf7_2_post_filter_taxonomy_registration-{$taxonomy_slug}'
* @param array $taxonomy_arg an array containing arguments used to register the custom taxonomy.
* @return array  an array of arguments.
*/

function register_custom_tags($taxonomy_arg){
  $taxonomy_arg['hierarchical'] = false;
  return $taxonomy_arg;
}" href="javascript:void(0);">Taxonomy Registration Filter</a> to change custom taxonomy like tags (<a href="https://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments">documentation</a>).
      </li>
      <li class="system-hook">
        <a class="helper" data-cf72post="add_filter('cf7_2_post_skip_system_metakey', 'filter_post_metas', 10, 3);
/**
* Function enable mapping for form fields to internal meta-field (starting with '_').
* Hooked on 'cf7_2_post_supports_{$custom_post_type}'
* @param boolean $skip flag is set to true by default
* @param string $post_type of the post to which the form is being mapped to
* @param string $meta_field_name is the name of the field which is being skipped
* @return boolean false to include this meta field.
*/
function filter_post_metas($skip, $post_type, $meta_field_name){
  /*In v2.0 the plugin allows users to map their forms to existing system posts through a UI interface.  The form fields can be mapped to the post fields, as well as existing post meta-fields, in addition to being able to add new ones too.  The list of existing meta-fields available is built by ignoring meta-fields with names starting with the '_' character which by convention is used to denote an internal meta-field.  This filter permits to include these meta-fields on a field by field mode*/
  if('{$post_type}' == $post_type){
    //show all internal meta-fields.
    $skip = false;
  }
  return $skip;
}" href="javascript:void(0);">Internal Meta Filter</a> to show system post internal meta-fields.
      </li>
      <li class="system-hook">
        <a class="helper" data-cf72post="add_filter('cf7_2_post_display_system_posts', 'filter_posts', 10, 2);
/**
* Function enable mapping for form fields to internal meta-field (starting with '_').
* Hooked on 'cf7_2_post_supports_{$custom_post_type}'
* @param array $displayed_posts array of system $post_type => $post_label key value pairs.
* @param string $form_id form_id is the post id of the current cf7 form being mapped.
* @return array $displayed_posts array of system post_type to view.
*/
function filter_posts($displayed_posts, $form_id){
  /*In v2.0 the plugin allows users to map their forms to existing system posts.  By default, only system posts which are visible in the dashboard are listed.  This list can be modified by this filter,*/
  //add an existing post type and label,
  displayed_posts['some_post'] = 'Some Post';
  return displayed_posts;
}" href="javascript:void(0);">System Post Filter</a> to show hidden system post for mapping.
      </li>
    </ol>
  </div>
</div>
<div id="loading-hooks" class="postbox closed">
  <button type="button" class="handlediv button-link" aria-expanded="false"><span class="screen-reader-text">Toggle panel: Loading Hooks</span><span class="toggle-indicator" aria-hidden="true"></span></button>
    <h3 class="hndle">Form loading hooks</h3>

    <div class="inside">
      <p>
        these are triggered when your form is being loaded on the front-end and are useful to customise the form.
      </p>
      <ol class="helper-list">
        <li>
          <a class="helper" data-cf72post="add_filter( 'cf7_2_post_form_append_output', 'custom_cf7_script',10,5);
/**
* Function to append an inline script at the end of the cf7 form.
* Note: you can also add a script inside your theme js/ folder in a file with the name of your form unique key <unique-key>.js which will be automatically loaded.
* Hooked to 'cf7_2_post_form_append_output'.
* @param string $script script to append.
* @param array $shortcode_attrs the shortcode attributes.
* @param string $nonce  a unique nonce string which is triggered as an event on the form once the plugin has completed loading values into the form fields.  It is best to execute your script once this event has fired.
* @param string $cf7form_key unique key identifying your form.
* @param array $form_values array of <field-name>=>$value pairs loaded in the form if any. If this form is a draft being loaded, you will find the post id of the draft mapped to the key 'map_post_id'.
* @return string an inline script to append at the end of the form.
*/
function custom_cf7_script($script, $shortcode_attrs, $nonce, $cf7form_key, $form_values){
  if(!isset($shortcode_attrs['id'])){
    return $script;
  }
  $cf7_id = $shortcode_attrs['id'];
  if('contact-us' == $cf7form_key){ //check this is your form if need be
  ob_start();
    ?>
    <script type=&quot;text/javascript&quot;>
    (function( $ ) {
      //execute your script once the form nonce event is triggered
      $(document).on('&lt?=$nonce?>', $('div#&lt;?=$nonce?> form.wpcf7-form'), function() {
        var cf7Form = $('div#&lt;?=$nonce?> form.wpcf7-form');
        ... //your custom scripting
      });
    })( jQuery );
    </script>
    &lt;?php
    $script .= ob_get_contents();
    ob_end_clean();
  }
  return $script;
}" href="javascript:void(0);">Custom javascript</a> appended at the end of your form.
        </li>
        <li>
          <a class="helper" data-cf72post="add_filter( 'cf7_2_post_filter_cf7_field_value', 'field_default_value',10,5);
/**
* Function to pre-fill form fields when form is loading.
* Note: for saved draft-forms values found in the database this filter will not be fired.
* Hooked to 'cf7_2_post_filter_cf7_field_value'. As of v3.2 this plugin integrates with the Smart Grid-Layout designs for CF7 plugin extension which introduces the wpf7_type taxonomy for forms.
* @param mixed $value value to to be filtered/pre-filled.
* @param $cf7_id  the form id being loaded.
* @param $field  the field name for which the value is being filtered.
* @param string $cf7form_key unique key identifying your form.
* @param array $form_terms an array of form taxonomy term slugs if any, else empty arrya.
* @return mixed value of field.  For fields than can accept array values, an array can be returned.
*/
function field_default_value($value, $cf7_id, $field, $cf7form_key, $form_terms){
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
}" href="javascript:void(0);">Default Values Filter</a> default field value when form is displayed.
        </li>
        <li>
          <a class="helper" data-cf72post="add_filter('cf7_2_post_filter_cf7_taxonomy_terms', 'modify_my_terms',10,4);
/**
* Function to pre-fill form dropdown/radio/checkbox fields mapped to a taxonomy.
* Hooked to 'cf7_2_post_filter_cf7_taxonomy_terms'.
* @param array $terms_id initially an empty array.
* @param $cf7_id  the form id being loaded.
* @param $field  the field name for which the value is being filtered.
* @param string $cf7_key unique key identifying your form.
* @return array an array of term IDs to select.
*/
function modify_my_terms($terms_id, $cf7_id, $field, $cf7_key){
  /*This filter allows you to pre-fill/select taxonomy terms fields for new submissions.*/
  //assuming you have defined a checkbox field called city-locations for cf7 form 'contact-us'.
  if('contact-us' == $cf7_key &amp; 'city-locations' == $field){
    $term = get_term_by('name','London','location_categories');
    $terms_id = array();
    $terms_id[] = $term->term_id;
  }
  return $terms_id;
}" href="javascript:void(0);">Default Term Values Filter</a> taxonomy terms in dropdown/radio/checkbox.
        </li>
        <li>
          <a class="helper" data-cf72post="add_filter( 'cf7_2_post_filter_taxonomy_query', 'filter_taxonomy_terms',10, 5);
  /**
  * Function to filter the list of terms shown in a mapped taxonomy field.  For example if you have a select field you can restrict the options listed to a select set of terms.
  * @param array $query an array of query attributes for the taxonomy.
  * @param string $cf7_id  the form id being loaded.
  * @param string $taxonomy the taxonomy slug being queried.
  * @param string $field the field name in the form being loaded.
  * @param string $cf7_key unique key identifying your form.
  * @return array an array taxonomy query attributes (see codex page:https://developer.wordpress.org/reference/functions/get_terms/).
  */
  function filter_taxonomy_terms($query, $cf7_id, $taxonomy, $field, $cf7_key){
  // NOTE: the plugin iterates through each terms it finds to get its children.
  //so for hierarchical taxonomy this filter will be recursively called on each term and its children.
  //in the example below I am assuming that I have a dropdown field which map to my (hierarchical) locations taxonomy.  I want restrict the listed options to my top level terms 'UK', 'France' and 'Germany'.Furthermore, I don't want to list the children of these capital cities, namely 'London', 'Paris', 'Berlin'.
  if($cf7_key == 'travel-form' && $field=='locations'){//verify this is the correct form.
    switch($query['parent']){
      case 0: //this is the first (top-level) iteration of this filter.
        $query['slug'] = array('uk', 'france', 'germany');//restrict our top level terms.
        break;
      default: //this is an iteration through a child-term.
        //let's find which term,
        $term = get_term_by('id', $query['parent'], $taxonomy);
        switch($term->slug){
          case 'london':
          case 'paris':
          case 'berlin':
            $query = array(); //an emtpy array will stop loading their children.
            break;
        }
        break;
    }
  }
  return $query;
}" href="javascript:void(0);">Filter term list</a> in mapped taxonomy field.
      </li>
        <li>
          <a class="helper" data-cf72post="add_filter( 'cf7_2_post_print_page_nocache_metas','disable_page_cache_metas',10);
/**
*Function to disable the no-cache meta tags in the form page head section.
*When you map a form which users can save a draft of, browser caching can result in old draft values persisting on the client side.  The plugin adds no-cache meta tags in the head section to precent browsers cahcing this page.  You can use this filter to disable it.
* @param boolea $print_on_page default is true.
* @return boolean false to disable meta tags.
*/
function disable_page_cache_metas($print_on_page){
  // you can get the global $post to see which page is being loaded.
  global $post;
  //or you can check if perticular page template is used.
  if(is_page_template( 'page-contact.php')) $print_on_page = false;
  return $print_on_page;
}" href="javascript:void(0);">Page Cache Filter</a> to remove nocache meta tags in &lt;head&gt;.
      </li>
    </ol>
  </div>
</div>
<div id="submit-hooks" class="postbox closed">
  <button type="button" class="handlediv button-link" aria-expanded="false"><span class="screen-reader-text">Toggle panel: Submitted hooks</span><span class="toggle-indicator" aria-hidden="true"></span></button>
    <h3 class="hndle ui-sortable-handle">Form submitted hooks</h3>
    <div class="inside">
      <p>
        these are triggered when a user has submitted/saved your form and can be used to manipulate the submitted data and take further action.
      </p>
      <ol class="helper-list">
        <li>
          <a class="helper" data-cf72post="add_filter('cf7_2_post_author_{$post_type}', 'set_{$post_type}_author',10,4);
/**
* Function to change the author of saved/submitted posts.
* Note: For logged in users submitting a draft form, the post is saved with the user as author.  Therefore changing this will result in the user not being able to reload their draft form.
* @param string $author_id the post author, default is current user if logged in, else site admin.
* @param $cf7_id  the form id being loaded.
* @param array $submitted_data complete set of data submitted in the form as an array of field-name=>value pairs.
* @param string $ckf7_key unique key to identify your form.
* @return string a valid user id.
*/

function set_{$post_type}_author($author_id, $cf7_id, $submitted_data, $cf7_key){
  //... do something here and set a new author ID with a valid user id which exists in the user table.
  return $author_id;
}" href="javascript:void(0);">Author Filter</a> the author of the submitted post .
        </li>
        <li>
          <a class="helper" data-cf72post="add_action( 'cf7_2_post_status_{$post_type}', 'publish_new_{$post_type}',10,3);
/**
* Function to change the post status of saved/submitted posts.
* @param string $status the post status, default is 'draft'.
* @param string $ckf7_key unique key to identify your form.
* @param array $submitted_data complete set of data submitted in the form as an array of field-name=>value pairs.
* @return string a valid post status ('publish'|'draft'|'pending'|'trash')
*/
function publish_new_{$post_type}($status, $ckf7_key, $submitted_data){
  /*The default behaviour is to save post to 'draft' status.  If you wish to change this, you can use this filter and return a valid post status: 'publish'|'draft'|'pending'|'trash'*/
  return 'publish';
}" href="javascript:void(0);">Post Status Filter</a> to automatically publish submitted post (<a href="https://codex.wordpress.org/Function_Reference/get_post_status#Return_Values">documentation</a>).
        </li>
        <li>
          <a class="helper" data-cf72post="add_fitler('cf7_2_post_draft_skips_validation', 'force_validation', 10, 2);
/**
* Function to force field validation when draft form is saved.
* @param boolean $skip_validation true by default.
* @param string $cf7_key unique key to identify your form.
* @return boolean false validate fields when draft form is saved.
*/
function force_validation($skip_validation, $cf7_key){
  /*For forms which have a save button, the validation of draft forms are skipped by default. This filter allows you to force validation of draft forms.*/
  if('my-form' == $cf7_key){
    $skip_validation = false;
  }
  return skip_validation;
}" href="javascript:void(0);">Draft Validation Filter</a> to enable field validation on draft form saved.
        </li>
        <li>
          <a class="helper" data-cf72post="add_fitler('cf7_2_post_transient_submission_expiration', 'keep_transient',10,2);
/**
* Function change the expiration of transient saved post ID after a submission.
* @param int $time value in seconds, default is 300 = 5 mins.
* @param string $cf7_key unique key to identify your form.
* @return int time in seconds to expiration of the transient post ID.
*/
function force_notification($time, $cf7_key){
  /*The post ID to which a submission is saved to is stored as a transient value in the WordPress database cache.  This is helpful is you want to redirect your form submission to another page and display the results.  You can access the saved post ID on the redirected page. This transient value is cached for 5 minutes, but you may need to keep this value in the cache for a longer period if you expect your users to visit the redirected page at a later state. */
  if('my-form' == $cf7_key){
    $time = 60*60*1; //this is 1 hour.
  }
  return $time;
}" href="javascript:void(0);">Cache Time Filter</a> period to track anonymous submissions.
      </li>
        <li>
          <a class="helper" data-cf72post="add_fitler('cf7_2_post_draft_skips_mail', 'force_notification');
/**
* Function to force mail sending for draft form saving.
* @param boolean $skip_mail true by default.
* @param string $cf7_key unique key to identify your form.
* @return boolean false to send mails on draft form saving.
*/
function force_notification($skip_mail, $cf7_key){
  /*For forms which have a save button, the mail sending of draft forms is skipped by default. This filter allows you to force mail notification of draft forms. */
  if('my-form' == $cf7_key){
    skip_mail = false;
  }
  return skip_mail;
}" href="javascript:void(0);">Draft Mail Filter</a> to send cf7 mail for draft form saved.
      </li>
      <li>
        <a class="helper" data-cf72post="add_action('cf7_2_post_form_submitted_to_{$post_type}', 'new_{$post_type}_mapped',10,3);
/**
* Function to take further action once form has been submitted and saved as a post.  Note this action is only fired for submission which has been submitted as opposed to saved as drafts.
* @param string $post_id new post ID to which submission was saved.
* @param array $cf7_form_data complete set of data submitted in the form as an array of field-name=>value pairs.
* @param string $cf7form_key unique key to identify your form.
*/
function new_{$post_type}_mapped($post_id, $cf7_form_data, $cf7form_key){
  //do something.
}" href="javascript:void(0);">Action</a> after <em>submitted</em> form is saved to post.
      </li>
      <li>
        <a class="helper" data-cf72post="add_filter('cf72post_default_post_title', 'set_default_title',10,3);
        /**
        * Filter to set the default title for a mapped post.  Use this filter if you are mapping a form to an existing post and are only saving meta-fields.
        * @param  string  $post_title  default title to set.
        * @param  string  $post_type  the post type being mapped to.
        * @param  string  $cf7_key  the unique key to indetify the form.
        * @return string the default title.
        */
function set_default_title($post_title, $post_type, $cf7_key){
  //set the default title for the post to which the form sibmission is saved to.
return $post_title;
}" href="javascript:void(0);">Default title Filter</a> the default title of the submitted post.
      </li>
    </ol>
  </div>
</div>
<script type="text/javascript">
(function($){
	$(document).ready( function(){
    var $source = $('#post_type_source');
    $.fn.updateHelper = function(){
      switch($(this).val()){
        case 'factory':
          $('li.system-hook', 'ul.helper-list').hide();
          $('li.factory-hook', 'ul.helper-list').show();
          break;
        case 'system':
          $('li.system-hook', 'ul.helper-list').show();
          $('li.factory-hook', 'ul.helper-list').hide();
          break;
      }
      //setup clipboard
      $('#helper .helper-list li a').each(function(){
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
    }
    $source.change(function(){
      $(this).updateHelper();
    });
    //initialise
    $source.updateHelper();
    //button accordion
    //$('#helperdiv .helper-list li .button-link')
  });
})(jQuery)
</script>
<style>
.helper-list li{
  position: relative;
}
.helper-list li .helper::before {
    content: 'Click to copy!';
    display: none;
    position: absolute;
    top: -22px;
    left: 10px;
    background: #323232;
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-weight: bold;
}
.helper-list li .helper:hover::before {
    display: inline-block;
}
.helper-list li.no-post-my-form{
  display: none;
}
#helper .postbox {
    margin-bottom: 2px;
    border: none;
}
#helper .postbox .toggle-indicator {
    float: right;
}
#helper .postbox h3.hndle {
    padding-left: 0;
    padding-right: 0;
}
#helper .postbox .inside,
.helper-list {
    padding: 0;
    margin: 0;
}
.helper {
    color: #006800;
}
#helperdiv p, #helperdiv ul {
    margin: 0;
}
.helper-list p {
    margin: 0;
    text-align: justify;
}
</style>
