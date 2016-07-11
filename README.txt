=== Post My CF7 Form ===
Contributors: aurovrata
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DVAJJLS8548QY
Tags: contact form 7, contact form 7 module, post, custom post, form to post
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables the mapping of your CF7 forms to custom posts.

== Description ==

This plugin enables the mapping of each form field to a post field.   Each forms submitted from your website will then be saved as a new post which you can manage in your dashboard and display on the front end.

=Filters for fields=

In addition to mapping your form fields to post fields you are also given a custom filter for that specific form field.  The filter option allows you to custom fill the post created for the submitted form, for example if a form requests the date of birth, you may want to create an additional post field for the age, so you can filter the date field in your `functions.php` file and calculate the age and save it to a custom post meta field.  The custom filters are created using the following nomenclature, `cf7_2_post_filter-<post_type>-<post-field>`.  For example if you have created a custom post type `quick-contact`, which as a meta field `age`, you could filter it with,
`
add_filter('cf7_2_post_filter-quick-contact-age','filter_date_to_age',10,2);
function filter_date_to_age($value, $form_data){
  //$value is the post field value to return, by default it is empty
  // $form_data is the submitted form data as an array of field-name=>value pairs
  if(isset($form_data['date-of-birth']){
    //calculate the age
    $value = ....
  }
  return $value;
}
`
= Special Fields =

**Author** - unless the user sets the field, the default is the recipient of the CF7 form email if such a user exists in the database, else it reverts to the user_id=1 which is the administrator.

**Featured image/Thumbnail** - these will only accept form fields of type *file*.  However, non image files uploaded will not show up as thumbnails in the post edit page.

**Title/Content/Excerpt** - there are enabled by default, and can be used to map any form fields to them.  However, if you wish disable these fields (using the post registration *supports* array of values), then please use the filter that allows these to be set for your custom post type.  (see the [filters section](https://wordpress.org/plugins/post-my-contact-form-7/other_notes/) for more info)


= Contact Form 7 list table =

This plugin re-organises the CF7 dashboard list table, using the cf7 custom post list table to permit other developpers to easily add custom columns to the list table.  You can therefore use [WP functionality](http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types) to customise your table.

= Other hooks =

The plugin has been coded with additional actions and filters to allow you to hook your functionality such as when a form to post mapping is completed.  For a list of such hooks, please refer to the Filters section.

== Installation ==

1. Install the *Contact Form 7* plugin
2. Install the *Post My CF7 Form* plugin
3. Create a contact form.  A new column appears in the contact table list which shows you which Post Type the form is mapped to
4. Click on the link 'Create New' that appears on the column to start mapping your form to a custom post.
5. Create the post and it will appear in your Dashboard.
6. Each time a visitor submits the form on your website, a new post will be created.


== Frequently Asked Questions ==

= Questions ? =

If you have a query, please drop me a message in the support forum.



== Screenshots ==

1. You can map your form fields to post fields and meta-fields.  You can save the mapping as a draft.  You can also change the custom post attributes that will be used to create the post. The default ones are `public, show_ui, show_in_menu, can_export, has_archive, exclude_from_search`.  For more information, please consult the custom post [documentation](https://codex.wordpress.org/Function_Reference/register_post_type).
2. Once created, you have can only view the mapping.  All the fields are disabled to maintain post integrity. You can however add new meta-fields.  You will also see your new custom post in your dashboard menu is you have enabled post attributes `show_ui` & `show_in_menu`.
3. The CF7 table list shows an exra column with the status of the form mapping.

== Changelog ==

= 1.0 =
* Allows for mapping of any CF7 form to a custom post

== Filters ==

The following filters are provided in this plugin,

= `cf7_2_post_supports_{$post_type}` =

Set custom post type support attributes,
`
add_filter('cf7_2_post_supports_quick-contact','set_supports');
function set_supports($default_supports){
  $default_supports[]='comments';
  return $default_supports;
}
`
= `cf7_2_post_capabilities_{$post_type}` =

Set [custom post capabilities](http://wordpress.stackexchange.com/questions/108338/capabilities-and-custom-post-types) for user access,
`
add_filter('cf7_2_post_supports_quick-contact','set_capabilities');
function set_supports($capabilities){
$capabilities = array(
    'edit_post' => 'edit_contact',
    'edit_posts' => 'edit_contacts',
    'edit_others_posts' => 'edit_others_contacts',
    'publish_posts' => 'publish_contacts',
    'read_post' => 'read_contacts',
    'read_private_posts' => 'read_private_contacts',
    'delete_post' => 'delete_contact'
);
  return $capabilities;
}
`
All capabilities must be set, else the plugin will default back to `post` capabilities.  Also, make sure you assign each of these capabilities to the admin role (or other roles/users) else you won't be able to access your custom post.

= `cf7_2_post_form_mapped_to_{$post_type}` =

Action fired when a submitted form is saved to its custom post, for example if your custom post type is `my-cpt`,

`
add_action('f7_2_post_form_mapped_to_my-cpt','modify_form_posts');
function modify_form_posts(,$cf7_form_data, $post_id){
  //cf7_form_data is the submitted data from your form
  //post_id is the id of the post to which it has been saved.
  //... do something here
}
`
NOTE: all posts are saved in `draft` mode, so if you wanted this to be changed and published immediately, you could do it with the above example.
