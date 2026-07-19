(function($) {
  //obtained from wordpress codex: https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
	// we create a copy of the WP inline edit post function
	var $wp_quick_edit = inlineEditPost.edit;
	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_quick_edit.apply( this, arguments );

		// now we take care of our business

		// get the post ID
		var $post_id = 0;
		if ( typeof( id ) == 'object' ) {
			$post_id = parseInt( this.getId( id ) );
		}

		if ( $post_id > 0 ) {
			// define the edit row
			var $edit_row = $( '#edit-' + $post_id );
			var $post_row = $( '#post-' + $post_id );
			// get the status
      var $submit = $('.cf7-2-post-submit', $post_row);
      if($submit.length>0 && 'yes'==$submit.text()){
        $('input[name="cf7_2_post_submit"]', $edit_row).prop('checked', true);
      }
    }
	}

})(jQuery);
