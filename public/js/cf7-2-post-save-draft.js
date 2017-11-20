(function( $ ) {
	'use strict';

	$(document).ready(function(){
    var $save = $('form.wpcf7-form input[type=submit].cf7_2_post_save');
    $save.each(function(){
			var $form = $(this).closest("form.wpcf7-form");
      //localized cf72post_save.disabled = true if form is not mapped.
      $('input[type=submit].wpcf7-submit', $form).on('click', function(event){
        var isSave = 'false';
        if($(this).is('.cf7_2_post_save')){
          if(cf72post_save.disabled) {
            $(this).parent().append('<span class="wpcf7-warning">'+cf72post_save.error+'</span>')
            event.stopPropagation();
            return false;
          }
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
        $('input[type=hidden].cf7_2_post_draft', $form).val(isSave);

      });
      //verify if a message box is available
      if( ! $('div.wpcf7-response-output', $form).length){
        cf7Form.append('<div class="wpcf7-response-output wpcf7-display-none"></div>')
      }
    });
  });

})( jQuery );
