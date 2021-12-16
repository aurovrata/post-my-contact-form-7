(function($) {
  //obtained from wordpress codex: https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
  // we create a copy of the WP inline edit post function
  let $wp_quick_edit = inlineEditPost.edit;
  // and then we overwrite the function with our own code
  inlineEditPost.edit = function( id ) {

	// "call" the original WP edit function
	// we don't want to leave WordPress hanging
	$wp_quick_edit.apply( this, arguments );

	// now we take care of our business

	// get the post ID
    let postId = 0;
    if ( typeof( id ) == 'object' ) {
      postId = parseInt( this.getId( id ) );
	}

	if ( postId > 0 ) {
	  // define the edit row
	  let $editRow = $( '#edit-' + postId ),
        $postRow = $( '#post-' + postId ),
        $type = $('.cf7-2-post-type', $postRow);
      if($type.length>0){
        let $warn = $('.c2p-delete-warning',$editRow),
          $delete = $('.c2p-delete-mapping',$editRow);

        $('.c2p-post-type', $delete).text($type.val());
        $('.title>input', $delete).val(postId).on('change',function(){
          if(this.checked){
            $warn.show();
            if($type.is('.c2p-factory')){
              let $data = $('.c2p-delete-data',$delete),
                $filter = $('span', $data).not('.popup'), $clip, text;
              if($filter.length>0){
                $clip = $('a',$data).remove(),
                text = $clip.data('clipboard-text').replace('${post_type}',$type.val());
                $clip.attr('data-clipboard-text', text).text($filter.text());
                $filter.after($clip);
                $filter.remove();
                $data.show();
                new Clipboard($clip[0]);
              }
            }
          }else{
            $warn.hide();
          }
        });
        $delete.show();
      }
    }
	}

})(jQuery);
