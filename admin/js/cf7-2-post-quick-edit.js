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
      var $warn = $('<div class="cf7-2-post-delete-warning"><em>This will reset the mapping</em></div>');
			// get the status
      var $status = $('.map_cf7_2_post input.cf7-2-post-status', $post_row);
      var $submit = $('.cf7-2-post-submit', $post_row);
      var $select = $('select.cf7-2-post-map', $edit_row);
      var $link = $('.map_cf7_2_post a.cf7-2-post-map-link', $post_row);
      $('.cf7-2-post-delete-warning', $edit_row).hide();
      if($status.length > 0){
        $select.find('option[value="'+$status.val()+'"]').prop('selected', true);
        //$select.val($status.val());

        $select.change(function(){
          var $this = $(this);
          $('.cf7-2-post-delete-warning', $edit_row).hide();
          if('delete' == $this.val()){
            $this.after($warn);
          }
        });
      }else{
        $select.after($link.clone());
        $select.hide();
      }
      if($submit.length>0 && 'yes'==$submit.val()){
        $('input[name="cf7_2_post_submit"]', $edit_row).prop('checked', true);
      }
    }
	}

})(jQuery);
