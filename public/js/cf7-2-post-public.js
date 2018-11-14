(function( $ ) {
	'use strict';
   /**
   * js global function called by dynamic inline js to prefill a cf7 form.
   * this function loads the data using wp_localize_script() function, this is done
   * in order to overcome page caching of the json data which was previously embeded inline.
   * @since 3.0.0
   */
   $.fn.post2CF7FormData = function(nonce){
      if( 0=== $(this).closest('#'+nonce).length ){
        return '';
      }
      if( 'undefined' != typeof window[nonce] ){
        return window[nonce];
      }
      return '';
   }
})( jQuery );
