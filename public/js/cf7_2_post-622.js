(function( $ ) {
  'use strict';
   $(document).ready(function() {
 	  var fname;
    var cf7Form = $("form.wpcf7-form");

    if(cf7_2_post_622.your_name !== undefined){
      cf7Form.find("input[name=your-name]").val(cf7_2_post_622.your_name);
    }
      if(cf7_2_post_622.your_email !== undefined){
      cf7Form.find("input[name=your-email]").val(cf7_2_post_622.your_email);
    }
      if(cf7_2_post_622.project_type !== undefined){
      cf7Form.find("select[name=project-type]").val(cf7_2_post_622.project_type);
    }

      if(cf7_2_post_622.your_message !== undefined){
      cf7Form.find("textarea[name=your-message]").val(cf7_2_post_622.your_message);
    }

      fname = JSON.parse(cf7_2_post_622.project_type);
    cf7Form.find('select[name=project-type]').addClass('chosen-select').append(fname);
        if(cf7_2_post_622.map_post_id !== undefined){
      fname = '<input type="hidden" name="_map_post_id" id="cf2_2_post_id" value="' + cf7_2_post_622.map_post_id + '" />';
      cf7Form.find('input[type=hidden][name=_wpnonce]').parent().append(fname);
    }
  });
})( jQuery );
