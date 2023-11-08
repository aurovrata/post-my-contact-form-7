(function( $ ) {
	$(document).ready(function(){
		$('input.cf72post-submitted').on('change', function(){
			if( $(this).is(':checked') ){
			$('#cf72post-submitted-hidden').val('yes');
			}else{
			$('#cf72post-submitted-hidden').val('no');
			}
		});
	});
})( jQuery );