(function( $ ) {
	'use strict';

	$(document).ready(function(){
    var cf7Form = $('div.cf7_2_post form.wpcf7-form input[type=submit].cf7_2_post_save').closest("form.wpcf7-form");
    cf7Form.each(function(){
			var $form = $(this);
      $('input[type=submit].wpcf7-submit', cf7Form).on('click', function(){
        var isSave = 'false';
        if($(this).is('.cf7_2_post_save')){
          isSave = 'true';
					$(':input',$form).each(function(){
            switch(true){
              case $(this).is(':checked'):
                $(this).prop("defaultChecked", true);
                break;
              case $(this).is('select'):
                var values = $(this).val();
                if(!$.isArray(values)) values = [values];
                $('option', $(this)).each(function(){
                  $(this)[0].defaultSelected= false;
                  if(values.indexOf($(this).val()) >= 0){
                    $(this)[0].defaultSelected=true;
                  }
                });
                break;
              default:
                $(this).prop("defaultValue", $(this).val());
                break;
            }
					});
        }
        $('input[type=hidden].cf7_2_post_draft', cf7Form).val(isSave);

      });
      //verify if a message box is available
      if( ! $('div.wpcf7-response-output', cf7Form).length){
        cf7Form.append('<div class="wpcf7-response-output wpcf7-display-none"></div>')
      }
    });
  });

})( jQuery );
