(function($) {
  $(document).ready(function(){
    $('table.wp-list-table').on('click','.editinline',function(e){
      const $post = $(e.target).closest('tr'),
        post_id = $post.attr('id').replace('post-',''),
        $edit = $('#edit-'+post_id, $post.parent());
      $('fieldset.inline-edit-col-left',$edit).first().children('.inline-edit-col').children(':not(label)').hide();
      $('fieldset.inline-edit-col-right',$edit).first().children().hide();
    })
  })
})(jQuery);
