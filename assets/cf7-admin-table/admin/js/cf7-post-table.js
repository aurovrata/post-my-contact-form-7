(function($) {
  $(document).ready(function(){
    $('table.wp-list-table').on('click','.editinline',function(e){
      const $post = $(e.target).closest('tr'),
        post_id = $post.attr('id').replace('post-',''),
				post_name = $post.children('td.cf7_key.column-cf7_key').children('span').text();
        $edit = $('#edit-'+post_id, $post.parent());
      let $t = $('fieldset.inline-edit-col-left',$edit).first().children('.inline-edit-col'),
				key = '<label><span class="title">CF7 Key</span><span class="input-text-wrap"><input type="text" name="post_name" value="'+post_name+'" autocomplete="off" spellcheck="false"></span></label>';
			$t.children('label').after(key);
			$t.children(':not(label)').hide();
      $('fieldset.inline-edit-col-right',$edit).first().children().hide();

    })
  })
})(jQuery);
