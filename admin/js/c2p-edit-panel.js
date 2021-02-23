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
      //transform select fields into select-hybrid.
      $('#cf7-2-post select.select-hybrid:not(:disabled)').each(function(){
        isEmpty(this['_hselect']) ? new HybridSelect(this, {}) : this._hselect.refresh();
      })
    });
    $('#cf7-2-post').change('select.select-hybrid',function(e){
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
    return $menu;
  }

  /*
   *setup some events.
   */
  //existing post selection
  $('#post_type_source').on('change', function(){
    let $source = $(this).find('option:selected'),//factory/system source.
      $post = $('#system-post-type option:selected'),
      $mapped_type = $('input#mapped_post_type'),
      type='';
    switch($source.val()){
      case 'factory':
        $('#post-type-exists').hide(); //system posts.
        $('#post-type-select').show(); //factory post options.
        $mapped_type.val($('input#custom_post_type').val());
        break;
      case 'system':
        $mapped_type.val($post.val());
        $('#post-type-select').hide(); //factory post options.
        $('#post-type-exists').show();//system posts.
        break;
    }
  });
  //auto-fill the meta-field name and clone meta-field.
  $('#post-meta-fields').on('click', '.add-more-field, .remove-field', function(e){
    switch(true){
      case e.target.classList.contains('add-more-field'):
        break;
      case e.target.classList.contains('remove-field'):
        e.target.closest('li').remove();
      default:
        return false;
    }
    //let duplicate the field.
    let fieldList = e.delegateTarget,
      scroll = e.target.getBoundingClientRect(), //position of add button.
      field = e.target.closest('.post-meta-field'), //the field being cloned.
      keyName = '', //name of meta-field.
      idx=0, //index.
      $cloneField = $(field).clone(), //clone.
      postType=$('input#mapped-post-type').val(); //post type mapped to.
    //remove the add button on the cloned field.
    $cloneField.find('span.add-more-field').remove();
    //setup the clone field.
    if($('#post-type-exists').is(':visible')){ //system post.
      postType = $('#system-post-type option:selected').val();
      $cloneField.find('.post-field-name').html($('#c2p-'+postType).html());
    }else{
      let label = field.querySelector('.cf7-2-post-map-labels');
      keyName = label.value; //name of meta-field.
      idx = parseInt(keyName.replace('meta_key_','')) +1; //meta_key_<idx>, get next index.
      //setup next field.
      label.value= 'meta_key_'+idx;
      label.name='cf7_2_post_map_meta-meta_key_'+idx;
      field.querySelector('select').name='cf7_2_post_map_meta_value-meta_key_'+idx;
    }
    //enable the new field
    let $ffMenu = $cloneField.find('select.field-options');
    //setup the filter.
    $ffMenu.find('option.filter-option').val('cf7_2_post_filter-'+postType+'-'+keyName);
    //populate with latest form fields.
    $ffMenu.fillCF7fields();
    //add cloned field to DOM list of fields.
    $(fieldList).children('li:last').before($cloneField);
    $cloneField.wrap('<li></li>');
    //enable the new field.
    $cloneField.find(':input').each(function(){
      this.disabled=false;
      if(this.nodeName==='SELECT') new HybridSelect(this); //nice select.
    });
    //add remove button and error msg.
    $cloneField.append('<span class="dashicons dashicons-minus remove-field"></span>');
    $cloneField.after('<span class="cf7-post-msg"></span>');
    //scroll down window.
    let down = e.target.getBoundingClientRect();
    window.scrollBy(0, down.top - scroll.top);
    if($cloneField.children('.cf7-2-post-map-labels:first').is('select')){
      return;//this is a select dropdown of existing post meta-fields, nothing to autofill.
    }
    if($cloneField.is('.autofill-field-name')){
      let $previous = $cloneField.prevAll('.custom-meta-field:first').find('select.field-options').find('option:selected');
      let $select = $cloneField.find('select.field-options');
      if(Object.keys(formFields).pop() != $previous.val() ){ //last field not used.
        $select.addClass('autofill-field-name'); //allows change to autofill.
        let $nextOption = $select.find('option[value="'+$previous.val()+'"]').next();
        $nextOption.prop('selected','true'); //select.
        $select.val($nextOption.val()).trigger('change');
        let name = $nextOption.val().replace(/-/g,'_');
        $cloneField.find('input.cf7-2-post-map-labels').val(name).trigger('change');
        if( $nextOption.next().val() != $select.find('option:last').val()){
          $cloneField.nextAll('.custom-meta-field:first').addClass('autofill-field-name'); //prep for autofill.
        }
      }
      $cloneField.removeClass('autofill-field-name');
    }else{ //enable autofill on the select
      $cloneField.find('select.field-options').addClass('autofill-field-name');
    }
  });
  //bind and delegate event change for meta field selection
  $('#post-meta-fields').on('change', ':input', function(e){
    if(e.target.type != 'select' && e.target.type != 'input') return false;
    let field = e.target;
    //when selecting custom field in existing post meta-fields, change to input field.
    //when selecting a post-field, update the name form field select name + the hook filter value, need to refresh the hybrid-select.
    //when inut custom field, update the name form field select name + the hook filter value, need to refresh the hybrid-select.
    //when a form field is selected, check if it has been selected for another field, display warning.
    //when a hook if selected, display hook anchor link.
  });

})( jQuery )
