(function( $ ) {
  'use strict';
   $(document).ready(function() {
 	  var fname;
    var cf7Form = $("form.wpcf7-form");

    if(cf7_2_post_148.product_name !== undefined){
      cf7Form.find("input[name=product-name]").val(cf7_2_post_148.product_name);
    }
      if(cf7_2_post_148.components !== undefined){
      cf7Form.find("select[name=components]").val(cf7_2_post_148.components);
    }

      if(cf7_2_post_148.quantities !== undefined){
      cf7Form.find("input[name=quantities]").val(cf7_2_post_148.quantities);
    }
      if(cf7_2_post_148.units !== undefined){
      cf7Form.find("select[name=units]").val(cf7_2_post_148.units);
    }

      fname = JSON.parse(cf7_2_post_148.components);
    cf7Form.find('select[name=components]').addClass('chosen-select').append(fname);
        if(cf7_2_post_148.map_post_id !== undefined){
      fname = '<input type="hidden" name="_map_post_id" id="cf2_2_post_id" value="' + cf7_2_post_148.map_post_id + '" />';
      cf7Form.find('input[type=hidden][name=_wpnonce]').parent().append(fname);
    }
  });
})( jQuery );
