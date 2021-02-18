(function( $ ) {
	'use strict';
  const removeButton = '<span class="dashicons dashicons-minus remove-field"></span>',
    errorBox = '<p class="cf7-post-error-msg"></p><div class="clear"></div>',
    selectedOptions = new Array(), selectedCount = 0;

  let formFields, $form = $('textarea#wpcf7-form'),
    $tab = $('#cf7-2-post-tab');
  $(document).ready(function(){
    let init=true;
    //initialise the select fields adn populate them.
    $tab.on('keypress click', function(){
      formFields={};
      if($('#wpcf7-form-hidden').length>0) $form = $('#wpcf7-form-hidden'); //Smart Grid form.
      scanFormTags($form.text());
      //populate fields.
      $('.cf7-2-post-field select').fillCF7fields(init);
      init=false;
      //transform select fields into hybrid-select.
      $('#cf7-2-post select.hybrid-select').each(function(){
        isEmpty(this['_hselect']) ? new HybridSelect(this, {}) : this._hselect.refresh();
      })
    });
    $('#cf7-2-post').change('select.hybrid-select',function(e){
      let $this = $(e.target);
      if(e.target.selectedIndex>0) $this.addClass('cf7-post-mapped');
      else $this.removeClass('cf7-post-mapped');
    });
  });
  function scanFormTags(search){
    let cf7TagRegexp = /\[(.[^\s]*)\s*(.[^\s\]]*)[\s\[]*(.[^\[]*\"source:([^\s]*)\"[\s^\[]*|[.^\[]*(?!\"source:)[^\[]*)\]/img,
      match = cf7TagRegexp.exec(search);
    while(null != match && match.length>2){
      switch(match[1].replace('*','')){
        case 'recaptcha':
        case 'recaptch':
        case 'acceptance':
        case 'submit':
        case 'save':
          break;//tags with no fields of interest.
        default:
          formFields[match[2]] = match[1];
          break;
      }
      match = cf7TagRegexp.exec(search); //search next.
    }
    console.log(formFields);
  }
  function isEmpty(v){
    if('undefined' === typeof v || null===v) return true;
    return typeof v === 'number' ? isNaN(v) : !Boolean(v);
  }
  $.fn.fillCF7fields = function(init){
    let $menu = $(this), //dropdown being initialised.
      $option; //store the filter option.
    if(!$menu.is('select')) return false;
    //pickup all selected values.
    for(let i=0;i<$menu.length;i++ ){
      let $m = $menu.eq(i), v='';
      if(init) v= $m.attr('value');
      else v = $m[0].selectedIndex>0 ? $m[0].selectedOptions[0].value:'';
      $m.children().remove(':not(.filter-option):not(.default-option)');
      $option = $m.find('option.filter-option');
      Object.keys(formFields).forEach(function(f){
        $option.before('<option value="'+f+'">'+f+' ['+formFields[f]+']'+'</option>');
      })
      $m.val(v);//.change();
    }
    // }
  }
  $.fn.addMetaField = function(){

  }
})( jQuery )
