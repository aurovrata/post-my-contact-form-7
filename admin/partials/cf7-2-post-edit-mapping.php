<?php
/**
* Redesigned mapping page to align with WordPress UI std.
*@since 2.5.0
*/
require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-cf7-2-post-factory.php' ;
//require_once plugin_dir_path (dirname(__FILE__)) . 'class-cf7-2-post-admin.php';
//action
$is_new_mapping = true;
switch($factory_mapping->get('map')){
  case 'draft':
    $is_new_mapping = true;
    break;
  case 'publish':
    $is_new_mapping = false;
    break;
}
$post_type = $factory_mapping->get('type');
$source = $factory_mapping->get('type_source');
$post_name = '';
switch($source){
  case 'factory':
    $post_name = $factory_mapping->get('plural_name');
    break;
  case 'system':
    $post_obj = get_post_type_object( $post_type );
    $post_name = $post_obj->labels->name;
    break;
}

?>
<div class="wrap">
    <h2><?php esc_html_e('Save Submissions as ','cf7-2-post'); ?>&quot;<span id="custom-post-title"><?= $post_name ;?>&nbsp;(<?= $post_type?>)</span>&quot;</h2>
    <form id="cf7-post-mapping-form" name="map_cf7_2_post" method="post">
        <input type="hidden" name="action" value="save_post_mapping"/>
        <?php
        wp_nonce_field('cf7_2_post_mapping', 'cf7_2_post_nonce', false, true);

        /* Used to save closed meta boxes and their order */
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>

        <div id="poststuff">

            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

                <div id="post-body-content">
                    <?php include_once( plugin_dir_path(__FILE__) . 'cf7-2-post-mapping-content.php') ?>
                </div><!-- #post-body-content -->

                <div id="postbox-container-1" class="postbox-container">
                    <?php
                    do_meta_boxes('','side',null); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('','normal',null); ?>
                    <?php do_meta_boxes('','advanced',null); ?>
                </div>

            </div> <!-- #post-body -->

        </div> <!-- #poststuff -->

    </form>

</div><!-- .wrap -->
