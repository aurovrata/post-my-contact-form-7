(function($) {
  //obtained from wordpress codex: https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
	// we create a copy of the WP inline edit post function
	var $wp_inline_edit = inlineEditPost.edit;
	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function
		// we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );

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

			// get the data
      var $name = $( ':input[name="post_name"]', $edit_row );
      var $error = $('<span class="cf7-2-post-key-error">Key is not unique or contains spaces</span>').hide()
      $name.after($error);

			var form_key = $name.val();

			// populate the data
      var slugLabel = $( ':input[name="post_name"]', $edit_row ).closest( 'label' ).find('span.title');
      slugLabel.text("Form key");
      $name.on('change', function(){
        //hide error msg
        $error.hide();
        //validate it is unique
        if(cf7_2_post_admin.keys.includes( $(this).val() ) || $(this).val().stringOf(' ') > -1 ){
          var bg = $(this).css('background-color');
          var color = $(this).css('color');
          $error.show();
          $(this).animate({ //fadeout the value
            color: bg },
            1000, //time
            'linear', //easing
            function(){ //on completion
            $(this).val(form_key).css('color',color); //reset value
          });
        }else{ //update the shortcode
          form_key = $(this).val();
          $( ':input.cf7-2-post-shortcode', $edit_row ).val( '[cf7-form cf7key="' + form_key + '"]');
        }
      })
		}
    //remove other fields
    $(':input[name="_status"]').closest('.inline-edit-group').hide();
    $(':input[name="post_password"]').closest('.inline-edit-group').hide();
    $('fieldset.inline-edit-date').hide();

    //$('.inline-edit-row .inline-edit-col-left').not('.inline-edit-cf7').addClass('hide-element');
    //$('.inline-edit-row .inline-edit-col-right').addClass('hide-element');
	};

})(jQuery);
