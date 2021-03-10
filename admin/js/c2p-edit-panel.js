(function( $ ) {
	'use strict';
  const removeButton = '<span class="dashicons dashicons-minus remove-field"></span>',
    errorBox = '<p class="cf7-post-error-msg"></p><div class="clear"></div>',
    selectedOptions = new Array();

  let formFields, $form = $('textarea#wpcf7-form'),
    $tab = $('#cf7-2-post-tab'),selectedCount = 0, c2pChanged=false;;
  $(document).ready(function(){
    let init=true;
    //switch posts if need be.
    switchPostSource();
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
        //for taxnomies, skip fields which are not checkbox|radio|select.
        if($m.is('.taxonomy-options') && ['checkbox','radio','select'].indexOf(f.replace('*',''))<0) return true;
        $option.before('<option value="'+f+'">'+f+' ['+formFields[f]+']'+'</option>');
      })
      $m.val(v);//.change();
    }
    return $menu;
  }
  function switchPostSource(e){
    let $source = $('#post-type-source'),//factory/system source.
      $post = $('#system-post-type option:selected'),
      $mapped_type = $('input#mapped-post-type'),
      type='';
    switch($source.val()){
      case 'factory':
        $('#post-type-exists').hide(); //system posts.
        $('#post-type-select').show(); //factory post options.
        type = $('input#custom-post-type').val();
        $('#custom-post-title').html($('#post-plural-name').val()+' (<code>'+type+'</code>)')
        break;
      case 'system':
        type = $post.val();
        $('#custom-post-title').html($post.html().replace(type, '<code>'+type+'</code>'));
        $('#post-type-select').hide(); //factory post options.
        $('#post-type-exists').show();//system posts.
        break;
    }
    $mapped_type.val(type);
    if(!c2pChanged) c2pUpdateMapping();
    if(e){
      //clear any existing custom meta/taxonomy fields.
      $('#c2p-post-meta-fields li:not(.default-meta-field)').remove();
      $('#c2p-taxonomy-fields li:not(:last-child)').remove();
      //update default post field hooks.
      [].forEach.call(document.querySelector('#c2p-default-post-fields').children, (l,i)=>{
        let f = l.querySelector('.field-options'),
          pf = f.getAttribute('name').replace('cf7_2_post_map-',''),
          fo = f.querySelector('.filter-option');
        fo.value = 'cf7_2_post_filter-'+type+'-'+pf;
        if(fo.selected) c2pFilterHelperCode.call(l,fo.value);
      });
    }

  }
  function c2pUpdateMapping(){
    $('#c2p-mapping-changed').val(1);
    c2pChanged = true;
  }
  /*
   *setup some events.
   */
  //existing post selection
  $('#c2p-factory-post').on('change', '#post-type-source, #system-post-type, #custom-post-source, #post-plural-name', switchPostSource);
  //auto-fill the meta-field name and clone meta-field.
  $('#c2p-post-meta-fields').on('click', '.add-more-field, .remove-field', function(e){
    switch(true){
      case e.target.classList.contains('add-more-field'):
        break;
      case e.target.classList.contains('remove-field'):
        e.target.closest('li').remove();
        return false;
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
    if($('#post-type-exists').is(':visible') && $('#c2p-'+postType).length>0){ //system post.
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
  //bind and delegate add-more/remove taxonomy fields.
  $('#c2p-taxonomy-fields').on('click', '.add-more-field, .remove-field, .edit-taxonomy.enabled, .button.save-taxonomy', function(e){
    switch(true){
      case e.target.classList.contains('add-more-field'):
        break;
      case e.target.classList.contains('edit-taxonomy'):
        c2pEditTaxonomy.call(e.target, true);
        return false;
      case e.target.classList.contains('save-taxonomy'):
        c2pEditTaxonomy.call(e.target, false);
        return false;
      case e.target.classList.contains('remove-field'):
        e.target.closest('li').remove();
        return false;
      default:
        return false;
    }
    //let duplicate the field.
    let fieldList = e.delegateTarget,
      scroll = e.target.getBoundingClientRect(), //position of add button.
      field = e.target.closest('li'), //the field being cloned.
      tSlug = '', //taxonomy slug.
      idx=0, //index.
      $cloneField = $(field).clone(), //clone.
      postType=$('input#mapped-post-type').val(); //post type mapped to.
    //remove the add button on the cloned field.
    $cloneField.find('span.add-more-field').remove();
    let label = field.querySelector('.taxonomy-slug');
    tSlug = label.value; //taxonomy slug..
    let $ffMenu = $cloneField.find('select.field-options');
    $ffMenu.attr('name','cf7_2_post_map_taxonomy_value-'+tSlug);
    //setup the filter.
    $ffMenu.find('option.filter-option').val('cf7_2_post_filter-'+tSlug);
    //populate with latest form fields.
    $ffMenu.fillCF7fields();
    //add cloned field to DOM list of fields.
    $(fieldList).children('li:last').before($cloneField);
    $ffMenu.after('&nbsp;<span class="dashicons dashicons-minus remove-field"></span>');
    //enable the new field.
    $cloneField.find('span.link-button').removeClass('disabled').addClass('enabled');
    $cloneField.find(':input').each(function(){
      this.disabled=false;
      if(this.nodeName==='SELECT') new HybridSelect(this); //nice select.
    });
    //add remove button and error msg.
    let down = e.target.getBoundingClientRect();
    window.scrollBy(0, down.top - scroll.top);
  });
  //bind and delegate event change for meta field selection
  $('#c2p-mapped-fields').on('change', ':input', function(e){
    if(e.target.nodeName != 'SELECT' && e.target.nodeName != 'INPUT') return false;
    let field = e.target, //field.
      fv = field.value, //selected value.
      postType = document.querySelector('#mapped-post-type').value, //mapped psot type.
      fc = field.closest('li'), //field container.
      msgBox = fc.querySelector('.cf7-post-msg'),
      ffMenu = fc.querySelector('select.field-options'), //form field menu.
      update = true;

    switch(true){
      case field.classList.contains('existing-fields'): //post dropdpown.
        if( fv == 'cf72post-custom-meta-field'){ //switch to input text field.
          field.classList.add('display-none'); //hide post field dropdown.
          fc.querySelector('.cf7-2-post-map-label-custom').classList.remove('display-none'); //show text input.
          fv = e.target.parentNode.nextElementSibling.value;
        }
        //setup the form field menu values.
        ffMenu.setAttribute('name','cf7_2_post_map_meta_value-'+fv);
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+postType+'-'+fv;
        if(ffMenu._hselect) ffMenu._hselect.refresh(); //refresh hybrid select.
        break;
      case field.classList.contains('cf7-2-post-map-label-custom'): //update form field name.
        ffMenu.setAttribute('name','cf7_2_post_map_meta_value-'+fv);
        break;
      case field.classList.contains('field-options'): //check if field already used.
        msgBox.innerHTML ='';
        if(isEmpty(fv)) break;
        if(fv.indexOf('cf7_2_post_filter-') < 0){
          let all = [...document.querySelector('#c2p-default-post-fields').children].concat( [...document.querySelector('#c2p-post-meta-fields').children] );
          [].forEach.call(all, (l,i)=>{
            if(l==fc || l.classList.contains('default-meta-field')) return true;
            if(l.querySelector('.field-options').value === fv){ //field already mapped.
              msgBox.innerHTML = c2pLocal.warning;
              return false;
            }
          });
        }else{ //filter option selelected, display helper code.
           c2pFilterHelperCode.call(fc,fv);
        }
        break;
      case field.classList.contains('taxonomy-list'): //taxonomy selected.
        let tax = field.querySelector('option:checked'),
          isSystem = tax.classList.contains('system-taxonomy'),
          input = fc.querySelector('input.singular-name');
        input.value = tax.dataset.name;
        input.disabled = isSystem; //no need to be submitted
        input = fc.querySelector('input.plural-name');
        input.value = tax.innerText;
        input.disabled = isSystem; //no need to be submitted
        input = fc.querySelector('input.taxonomy-slug');
        input.value = tax.value;
        input.disabled = isSystem;//no need to be submitted
        input = fc.querySelector('span.taxonomy-name');
        input.innerHTML = '<strong>'+tax.innerText+'</strong>';
        fc.querySelector('input.taxonomy-source').value = isSystem ? 'system':'factory';
        //update the form-field select name and filter.
        ffMenu.setAttribute('name','cf7_2_post_map_taxonomy_value-'+tax.value);
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+tax.value;
        if(ffMenu._hselect) ffMenu._hselect.refresh(); //refresh hybrid select.
        break;
      case field.classList.contains('taxonomy-slug'): //update in factory custom taxonomy slug.
        //update the form-field select name and filter.
        ffMenu.setAttribute('name','cf7_2_post_map_taxonomy_value-'+field.value);
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+field.value;
        if(ffMenu._hselect) ffMenu._hselect.refresh(); //refresh hybrid select.
        break;
      default:
        update = false;
        break;
    }
    if(update && !c2pChanged) c2pUpdateMapping();
  });
  function c2pEditTaxonomy(show){
    let fc = this.closest('li');
    if(show){
      fc.querySelector('.custom-taxonomy-input-fields').classList.remove('display-none');
      fc.querySelector('.custom-taxonomy-field').classList.add('display-none');
    }else{
      fc.querySelector('.custom-taxonomy-input-fields').classList.add('display-none');
      fc.querySelector('.custom-taxonomy-field').classList.remove('display-none');
    }
    // details.find('select.taxonomy-list').on('change',taxonomySelected);
  }
  function c2pFilterHelperCode(filter){
    if(this) this.querySelector('.cf7-post-msg').remove();
    let field = filter.replace('cf7_2_post_filter-','').replace(/-/g,'_');
    let helper = "add_filter('"+filter+"','filter_"+field+"',10,3);\n";
    helper +="function filter_"+field+"($value, $post_id, $form_data){\n  //$value is the post field value to return, by default it is empty. If you are filtering a taxonomy you can return either slug/id/array.  in case of ids make sure to cast them as integers.(see https://codex.wordpress.org/Function_Reference/wp_set_object_terms for more information.)\n  //$post_id is the ID of the post to which the form values are being mapped to\n  // $form_data is the submitted form data as an array of field-name=>value pairs\n";
    helper +="  return $value;\n}";
    helper = 'filter:<a class="code" data-clipboard-text="'+helper+'" href="javascript:void(0);">'+filter+'</a><span class="popup">'+c2pLocal.copy+'<span>'+c2pLocal.paste+'</span></span>';

    if(this){
      $(this).append('<span class="cf7-post-msg animate-color">'+helper+'</span>');
      new Clipboard(this.querySelector('.cf7-post-msg a.code'));
    }else{
      return helper;
    }
  }
})( jQuery )