(function( $ ) {
	'use strict';

	$(document).ready(function(){
    var cf7Form = $('div.cf7_2_post form.wpcf7-form input[type=submit].cf7_2_post_save').closest("form.wpcf7-form");;
    cf7Form.each(function(){
      $('input[type=submit].cf7_2_post_save', cf7Form).on('click', function(){
        $('input[type=hidden].cf7_2_post_draft', cf7Form).val('true');
      });
      //verify if a message box is available
      if( ! $('div.wpcf7-response-output', cf7Form).length){
        cf7Form.append('<div class="wpcf7-response-output wpcf7-display-none"></div>')
      }
    });
  });

})( jQuery );
