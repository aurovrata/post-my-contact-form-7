=== Post My CF7 Form ===
Contributors: aurovrata
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DVAJJLS8548QY
Tags: contact form 7, contact form 7 module, post, custom post, form to post
Requires at least: 3.0.1
Tested up to: 4.5.3
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin enables the mapping of your CF7 forms to custom posts.

== Description ==

This plugin enables the mapping of each form field to a post field.   Each forms submitted from your website will then be saved as a new post which you can manage in your dashboard and display on the front end.

=Filters for fields=

In addition to mapping your form fields to post fields you are also given a custom filter for that specific form field.  The filter option allows you to custom fill the post created for the submitted form, for example if a form requests the date of birth, you may want to create an additional post field for the age, so you can filter the date field in your `functions.php` file and calculate the age and save it to a custom post meta field.  The custom filters are created using the following nomenclature, `cf7_2_post_filter-<post_type>-<post-field>`.  For example if you have created a custom post type `quick-contact`, which as a meta field `age`, you could filter it with,
`
add_filter('cf7_2_post_filter-quick-contact-age','filter_date_to_age',10,2);
function filter_date_to_age($value, $post_id, $form_data){
  //$value is the post field value to return, by default it is empty
  //$post_id is the ID of the post to which the form values are being mapped to
  // $form_data is the submitted form data as an array of field-name=>value pairs
  if(isset($form_data['date-of-birth']){
    //calculate the age
    $value = ....
  }
  return $value;
}
`
= Special Fields =

**Author** - unless the user sets the field, the default set in this order: current logged in user else, the recipient of the CF7 form email if such a user exists in the database, else it reverts to the user_id=1 which is the administrator.  A filter is also available to set the author.

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

= 1.2.0 =

* new filter for the author field
* ability for logged in users to save a draft form and edit it later.

= 1.1.0 =
* Auto deactivation of extension if CF7 plugin is deactivated

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
add_action('cf7_2_post_form_mapped_to_my-cpt','modify_form_posts',10,2);
function modify_form_posts($cf7_form_data, $post_id){
  //cf7_form_data is the submitted data from your form
  //post_id is the id of the post to which it has been saved.
  //... do something here
}
`
NOTE: all posts are saved in `draft` mode, so if you wanted this to be changed and published immediately, you could do it with the above example.

= `cf7_2_post_author_{$post_type}` =
Allows you to set a custom author ID for a new post based on the form being submitted.
`
add_filter('cf7_2_post_author_my-cpt','set_my_post_author',10,3);
function set_my_post_author($author_id, $cf7_form_id, $cf7_form_data){
  //$cf7_form_data is the submitted data from your form
  //$cf7_form_id is the id of the form being saved to a custom post my-cpt.
  //... do something here and set a new author ID
  return $author_id;
}
`
This filter expects the author ID to be returned.

= 'cf7_2_post_filter_taxonomy_registration-{$taxonomy_slug}' =
This filter allows you to customise taxonomies before they are registered.
`
add_filter('cf7_2_post_filter_taxonomy_registration-my_categories','modify_my_categories');
function modify_my_categories($taxonomy_arg){
  //$taxonomy_arg is an array containing arguments used to register the custom taxonomy
  //modify the values in the array and pass it back
  //for example, by default all taxonomies are registered as hierarchical, use this filter to change this.
  return $taxonomy_arg;
}
`
It is possible to pass a optional arguments for Metabox callback functions, taxonomy count update, and the taxonomy capabilities.  See the Wordpress [register_taxonomy](https://codex.wordpress.org/Function_Reference/register_taxonomy) documentation for more information.

`
add_filter('cf7_2_post_filter_taxonomy_registration-my_categories','modify_my_categories');
function modify_my_categories($taxonomy_arg){
  $args = array(
          'meta_box_cb' => 'my_custom_taxonomy_metabox',
          'update_count_callback' => 'my_taxonomy_selected',
          'capabilities' => array(
                              'manage_terms' => 'manage_categories'
                              'edit_terms' => 'manage_categories'
                              'delete_terms' => 'manage_categories'
                              'assign_terms' => 'edit_posts'
                            )
        );
  return args;
}
`

= 'cf7_2_post_filter_cf7_field_value' =

This filter allows you to pre-fill form fields with custom values for new submissions.
`
add_filter('cf7_2_post_filter_cf7_field_value','modify_my_field',10,3);
function modify_my_field($value, $cf7_post_id, $field){
  //assuming you have defined a text field called city-location for cf7 form ID=20
  if(20 == $cf7_post_id && 'city-location' == $field){
    $value = 'London';
  }
  return $value;
}
`
= 'cf7_2_post_filter_cf7_taxonomy_terms' =

This filter allows you to pre-fill/select taxonomy terms fields for new submissions.
`
add_filter('cf7_2_post_filter_cf7_taxonomy_terms','modify_my_terms',10,3);
function modify_my_terms($terms_id, $cf7_post_id, $field){
  //assuming you have defined a checkbox field called city-locations for cf7 form ID=20
  if(20 == $cf7_post_id && 'city-locations' == $field){
    $term = get_term_by('name','London','location_categories');
    $terms_id = array();
    $terms_id[] = $term->term_id;
  }
  return $terms_id;
}
`
The filter expects an array of terms id.

= 'cf7_2_post_filter_taxonomy_query' =

This filter allows you to modify the taxonomy terms query arguments for a form's dropdown/checkbox/radio list.

`
add_filter('cf7_2_post_filter_taxonomy_query','custom_dropdown_order',10,3);
function custom_dropdown_order($args, $cf7_post_id, $taxonomy){
  if(20 == $cf7_post_id && 'location_categories' == $taxonomy){
    //modify the order in which the terms are listed,
    $args['order_by'] = 'count';
  }
  return $args;
}
`
This function changes the list order, putting the most commonly used terms at the top of the list.
For more information on taxonomy query arguments, please refer to the [WP codex documentation](https://developer.wordpress.org/reference/functions/get_terms/#parameters).

= 'cf7_2_post_filter_cf7_taxonomy_chosen_select' =

This filter expects a boolean, by default it is `true` and enables [jquery chosen plugin](https://harvesthq.github.io/chosen/) on select dropdown fields.
To disable it, do the following

`
add_filter('cf7_2_post_filter_cf7_taxonomy_chosen_select','disable_chosen_plugin',10,3);
function disable_chosen_plugin($enable, $cf7_post_id, $form_field){
  if(20 == $cf7_post_id && 'your-option' == $form_field){
    //we assume here that cf7 form 20 has a dropdown field called 'your-option' which was mapped to a taxonomy
    $enable=false;
  }
  //you could just return false if you want to disable for all dropdown
  return $enable;
}
`
= 'cf7_2_post_filter_cf7_taxonomy_select_optgroup' =

This filter expects a boolean, by default it is `false` and disables [`optgroup`](http://www.w3schools.com/tags/tag_optgroup.asp) on select dropdown options.
To enable grouped options for hierarchical taxonomy top level term, you can use this filter.  Note however, that child term options will be grouped by their top-level parent only as nested `optgroup` are not allowed. Furthermore the parent term will not be selectable. Therefore this option only makes sense if you have a hierarchical taxonomy with only single level child terms which can be selected.  To enable grouped options,

`
add_filter('cf7_2_post_filter_cf7_taxonomy_select_optgroup','enable_grouped_options',10,4);
function enable_grouped_options($enable, $cf7_post_id, $form_field, $parent_term){
  if(20 == $cf7_post_id && 'your-option' == $form_field){
    //we assume here that cf7 form 20 has a dropdown field called 'your-option' which was mapped to a hierarchical taxonomy
    //you can even filter it based on the parent term, so as to group some and not others.
    //the attribute $parent_term is a WP_Term object
    switch($parent_term->name){
      case 'Others':
        $enable=false;
        break;
      default:
        $enable=true;
        break;
    }
  }
  return $enable;
}
`
