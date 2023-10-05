<?php
/**
 * Display quickedit options in table of wpcf7 form posts.
 *
 * @since 5.3.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_nonce_field( 'c2p_quickedit_nonce', 'c2p_nonce' );
?>
<fieldset class="inline-edit-col-right">
	<div class="inline-edit-col">
		<div class="inline-edit-group wp-clearfix">
			<label class="alignright display-none c2p-delete-mapping">
				<span class="title"><input type="checkbox" name="delete_c2p_map" value="0"><?php echo esc_html( __( 'Delete mapping:', 'post-my-cf7-form' ) ); ?><span class="c2p-post-type"></span></span>
				<div class="c2p-delete-warning display-none">
					<em><?php echo esc_html( __( 'This will reset the mapping', 'post-my-cf7-form' ) ); ?></em>
					<div class="c2p-delete-data display-none"><a class="code" data-clipboard-text="
add_filter('c2p_delete_all_submitted_posts','delete_all_submissions',10,3);
/**
* this filter controls wether or not to delete all submissions saved to the custom post type in the dashboard.
* @param Boolean $delete_all default is false
* @param String $post_type the custom post type that is mapped.
* @param String $cf7_key the current form key of which the mapping is being deleted.
* @return Boolean .
*/
function delete_all_submissions($delete_all, $post_type, $cf7_key){
	if('${post_type}'!=$post_type) return $delete_all;
	return true;
}
add_filter('c2p_delete_all_submitted_posts_query','delete_post_submissions',10,3);
/**
* In case the above filter return true, this filter controls the query to retrive the posts which get deleted.
* @param Array $post_query query to retrieve posts to be deleted (all by default), set codex get_posts() documention.
* @param String $post_type the custom post type that is mapped.
* @param String $cf7_key the current form key of which the mapping is being deleted.
* @return Boolean .
*/
function delete_all_submissions($post_query, $post_type, $cf7_key){
	if('${post_type}'!=$post_type) return $post_query;
	//modify the query such as to delete posts by a certain author or a perticular date...
	return $post_query;
}" href="javascript:void(0);"></a>
<?php
echo wp_kses(
	__( 'WARNING: this will also delete all saved submissions!  Use this <span>filter</span> in your <code>functions.php</code> file to control the deletion of saved submissions.', 'post-my-cf7-form' ),
	array(
		'span' => array(),
		'code' => array(),
	),
);
?>
<span class="popup display-none">Click to Copy!</span></div>
				</div>
			</label>
		</div>
	</div>
</fieldset>
