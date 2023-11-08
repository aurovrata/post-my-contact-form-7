<?php
/**
 * Print the admin footer js script on all admin pages.
 *
 * @since 5.3.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<script type="text/javascript">
(function( $ ) {
	'use strict';
	$(document).ready(function() {
		$('#adminmenu ul.wp-submenu li a[href="admin.php?page=cf7_post"]').parent().hide();
	});
})( jQuery );
</script>
