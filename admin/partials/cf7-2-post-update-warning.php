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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<span id="c2p-update-warning">
<span><?php echo esc_html( __( 'WARNING: This is a major update, please ensure you properly test your mapped forms on a local/staging server before updating your live site.', 'post-my-contact-form-7' ) ); ?></span>
<a href="" class="update-link c2p-link"><?php echo esc_html( __( 'Update now', 'post-my-contact-form-7' ) ); ?></a>
</span>
<script type="text/javascript">
	(function($){
	'use strict';
		$(document).ready(function(){
			let $update = $('#post-my-contact-form-7-update').find('a.update-link').not('.c2p-link'),
				$major = $('#c2p-update-warning');
			$update.attr('onclick','document.getElementById("c2p-update-warning").style.display="inline";');
			$major.find('a').attr('href', $update.attr('href'));
			$update.attr('href','javascript:void(0);');
			$update.removeClass('update-link');
		});
	})( jQuery );
</script>
<style>
	#post-my-contact-form-7-update {
		position: relative;
	}
	#c2p-update-warning {
	position: absolute;
	display: none;
	background: #fff;
	padding: 5px;
	border: 1px solid #c60000;
	bottom: 0;
	right: 0;
	width: 260px;
	font-weight: bold;
}
</style>
