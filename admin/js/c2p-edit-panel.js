(function( $ ) {
	'use strict';
  let formFields, $form = $('textarea#wpcf7-form'),
    $tab = $('#cf7-2-post-tab');
  $(document).ready(function(){
    let init=true;
    $tab.on('keypress click', function(){
      formFields={};
      scanFormTags($form.val());
      //populate fields.
      $('.cf7-2-post-field select').fillCF7fields(init);
      init=false;
    })
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
  $.fn.fillCF7fields = function(init){
    let $menu = $(this), $option, s=[];
    if(!$menu.is('select')) return false;
    // for(let i=0;i<$menu.length;i++){
    for(let v of $menu){
      if(init) s[s.length]= $(v).attr('value');
      else s[s.length]=v.value;
    }
    $menu.children().remove(':not(.filter-option):not(.default-option)');
    $option = $menu.find('option.filter-option');
    Object.keys(formFields).forEach(function(f){
      $option.before($('<option value="'+f+'">'+f+' ['+formFields[f]+']'+'</option>'));
    })
    for(let i in $menu) $menu[i].value= s[i];
    $menu.change();
    // }

  }
})( jQuery )
