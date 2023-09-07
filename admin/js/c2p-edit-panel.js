(function( $ ) {
	'use strict';
  const removeButton = '<span class="dashicons dashicons-remove remove-field"></span>',
    errorBox = '<p class="cf7-post-error-msg"></p><div class="clear"></div>',
    selectedOptions = new Array(),
    $status = $('#c2p-mapping-status');

  let fieldTags, formFields, $form = $('textarea#wpcf7-form'),
    $tab = $('#cf7-2-post-tab'),selectedCount = 0, init=true;
  $(document).ready(function(){
    fieldTags = ['hidden']; /** @since 5.4.2 fix tag scanning */
    if(typeof c2pLocal['wpcf7_tags'] != 'undefined'){
      $.each(c2pLocal.wpcf7_tags,(i,tag)=>{
        fieldTags.push(tag);
      });
    }
    fieldTags = fieldTags.join('|');

    $('#c2p-active-tab').val($tab.index());
    //status toggle.
    let tstatus = $status.val()=='draft'?false:true,
      $tggl = $('#c2p-factory-post .toggle');
    $tggl.toggles( {
      drag:false,
      text:{ on:c2pLocal.live, off:c2pLocal.draft },
      on: tstatus});
    $tggl.on('toggle', function(e, active) {
      if (active) $status.val('publish');
      else $status.val('draft');
      $('#c2p-mapping-changed').val(1);
    });
    //switch posts if need be.
    switchPostSource();
    //initialise mapping editor.
    function initC2PEditor(){
      formFields={};
      if($('#wpcf7-form-hidden').length>0) $form = $('#wpcf7-form-hidden'); //Smart Grid form.
      scanFormTags($form.text());
      //populate fields.
      $('.cf7-2-post-field select.field-options').fillCF7fields(init);
      init=false;
      //transform select fields into select-hybrid.
      $('#cf7-2-post select.select-hybrid:not(:disabled)').each(function(){
        isEmpty(this['_hybriddd']) ? new HybridDropdown(this, {}) : this._hybriddd.refresh();
      })
    }
    //initialise the select fields adn populate them.
    $tab.on('keypress click', initC2PEditor);
    $('#cf7-2-post').change('select.select-hybrid',function(e){
      let $this = $(e.target);
      if(e.target.selectedIndex>0) $this.addClass('cf7-post-mapped');
      else $this.removeClass('cf7-post-mapped');
    });
    initC2PEditor(); //on document ready.
  }); //document ready.
  function scanFormTags(search){
    /** @since v5.4.6 improved cf7 tag regex pattern. */
    let rgx = new RegExp('\\[(('+fieldTags+')\\*?)(?:[\\s](.*?))?(?:[\\s](\\/))?\\]','igm'),
      match = null;
    while(null !== (match = rgx.exec(search)) ){
      if(match.length>3){
        let fMatch = /^([^\s="':]+)([\s]+.*)?$/i.exec(match[3]);
        if(fMatch && fMatch[1]){
          switch(match[2]){
            case 'group'://special case which is not a field.
              break;
            default:
              formFields[fMatch[1]] = match[1];
              break;
          }
        }
      }
    }
    // console.log(formFields);
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
        switch(true){
          case $m.is('#cf7-2-thumbnail'): /*IS*/
            if(['file'].indexOf(formFields[f].replace('*',''))<0) return true;
            break;
          case $m.is('.taxonomy-options'):/*IS*/
            if(['checkbox','radio','select'].indexOf(formFields[f].replace('*',''))<0) return true;
            break;
          case $m.is('#cf7-2-slug'):
          case $m.is('#cf7-2-author'):
          case $m.is('#cf7-2-title'): /*NOT*/
            if(['textarea','file'].indexOf(formFields[f].replace('*',''))>=0) return true;

          }
        $option.before('<option value="'+f+'">'+f+' ['+formFields[f]+']'+'</option>');
      })
      if(isEmpty(v)) continue;
      if($('option[value="'+v+'"]',$m).length>0) $m.val(v);//.change();
      else c2pUpdateMapping(); //broken mapping.

      if(0==v.indexOf('cf7_2_post_filter-')){
        c2pFilterHelperCode.call($m.closest('li').get(0),v);
      }
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
        $('#post-type-exists').hide().find(':input').prop('disabled',true); //system posts.
        $('#post-type-select').show().find(':input').prop('disabled',false); //factory post options.
        type = $('input#custom-post-type').val();
        $('#custom-post-title').html($('#post-plural-name').val()+' (<code>'+type+'</code>)')
        break;
      case 'system':
        type = $post.val();
        $('#custom-post-title').html($post.html().replace(type, '<code>'+type+'</code>'));
        $('#post-type-select').hide().find(':input').prop('disabled',true); //factory post options.
        $('#post-type-exists').show().find(':input').prop('disabled',false);//system posts.
        break;
    }
    $mapped_type.val(type);
    if(!init) c2pUpdateMapping();
    if(e){
      //clear any existing custom meta/taxonomy fields.
      $('#c2p-post-meta-fields li:not(.default-meta-field)').remove();
      $('#c2p-taxonomy-fields li:not(:last-child)').remove();
      //update default post field hooks.
      [].forEach.call(document.querySelector('#c2p-default-post-fields').children, (l,i)=>{
        let f = l.querySelector('.field-options'),
          pf = f.getAttribute('name'),
          fo = f.querySelector('.filter-option');
          if(!pf && f._hybriddd) pf = f._hybriddd.opt.fieldName;
          pf = pf.replace('cf7_2_post_map-','');
        fo.value = 'cf7_2_post_filter-'+type+'-'+pf;
        if(fo.selected) c2pFilterHelperCode.call(l,fo.value);
      });
    }

  }
  function c2pUpdateMapping(){
    $('#c2p-mapping-changed').val(1);
    if($status.val()!='draft'){
      alert(c2pLocal.warn);
      $('#c2p-factory-post .toggle').toggles(false);
      $status.val('draft');
    }
  }
  /*
   *setup some events.
   */
  //existing post selection
  $('#c2p-factory-post').on('change', '#post-type-source, #system-post-type, #custom-post-type, #custom-post-source, #post-plural-name, #post_singular_name', switchPostSource);
  $('#c2p-factory-post').on('change','input.c2cpt-attribute', c2pUpdateMapping);
  //auto-fill the meta-field name and clone meta-field.
  $('#c2p-post-meta-fields').on('click', '.add-more-field, .remove-field', function(e){
    switch(true){
      case e.target.classList.contains('add-more-field'):
        break;
      case e.target.classList.contains('remove-field'):
        e.target.closest('li').remove();
        c2pUpdateMapping();
        return false;
      default:
        return false;
    }
    //let duplicate the field.
    let fieldList = e.delegateTarget,
      scroll = e.target.getBoundingClientRect(), //position of add button.
      field = e.target.closest('li'), //the field being cloned.
      keyName = '', //name of meta-field.
      idx=0, //index.
      $cloneField = $(field).clone(), //clone.
      postType=$('input#mapped-post-type').val(), //post type mapped to.
      $prev = $('select.autofill-field-name',$(field).prev());

    //remove the add button on the cloned field.
    $cloneField.find('span.add-more-field').remove();
    $cloneField.removeClass('default-meta-field');
    //add cloned field to DOM list of fields.
    $(fieldList).children('li:last').before($cloneField);
    //setup the clone field.
    if($('#post-type-exists').is(':visible') && $('#c2p-'+postType).length>0){ //system post.
      $cloneField.find('.post-field-name').html($('#c2p-'+postType).html());
      if($prev.length>0){
        $('select.existing-fields',$cloneField).addClass('display-none');
        $('input.cf7-2-post-map-label-custom',$cloneField).removeClass('display-none');
      }
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
    //enable the new field.
    $cloneField.find(':input').each(function(){
      if(this.classList.contains('display-none')) return true;
      this.disabled=false;
      if(this.nodeName==='SELECT') new HybridDropdown(this); //nice select.
    });
    //add remove button and error msg.
    $cloneField.append('<span class="dashicons dashicons-remove remove-field"></span>');
    $cloneField.append('<span class="cf7-post-msg"></span>');
    //scroll down window.
    let down = e.target.getBoundingClientRect();
    window.scrollBy(0, down.top - scroll.top);
    if($cloneField.children('.cf7-2-post-map-labels:first').is('select')){
      return;//this is a select dropdown of existing post meta-fields, nothing to autofill.
    }
    if($prev.length>0){
      let ff = Object.keys(formFields), mf=[];;
      $('#c2p-mapped-fields select.field-options').each(function(){
        if(this.value.indexOf('cf7_2_post_filter-')==0) return true; //skip.
        let rem = this.value;
        ff = ff.filter(function(e){
          return rem != e;
        });
      });
      //fill up the new field with the first available unused form field.
      if(ff.length>0){
        $('select.field-options', $cloneField).val(ff[0]).change().get(0).dispatchEvent(new Event('change'));
        $('.cf7-2-post-map-label-custom', $cloneField).val(ff[0].replace(/-/g,'_')).change();
      }
      $('select.field-options', $cloneField).addClass('autofill-field-name');
    }else{ //enable autofill on the select
      if($('.cf7-2-post-map-label-custom', $cloneField).is(':visible')){
        $('select.field-options', $cloneField).addClass('autofill-field-name');
      }
    }
  });
  //bind and delegate add-more/remove taxonomy fields.
  $('#c2p-taxonomy-fields').on('click', '.add-more-field, .remove-field, .edit-taxonomy.enabled, .button.save-taxonomy, .php-filter-button, .php-close.dashicons', function(e){
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
        c2pUpdateMapping();
        return false;
      case e.target.classList.contains('php-filter-button'):
        let l = e.delegateTarget.querySelector('ul.helper-list');
        if(!l){
          let f = document.createDocumentFragment(),list;
          l = document.createElement('ul');
          l.classList.add('helper-list');
          l.innerHTML = '<span class="dashicons dashicons-plus-alt php-close"></span>';
          e.target.closest('ul').append(l);
          list = document.querySelectorAll('#c2p-helper-lists li.c2p-taxonomy');
          [].forEach.call(list, (e)=>{l.appendChild(e)});
          l.appendChild(f.cloneNode(true));
        }
        let p = $(e.target).position();
        l.style['top'] = p.top+'px';
        l.style['left'] = p.left+'px';
        l.classList.remove('display-none');
        p = e.target.closest('li').querySelector('.field-options');
        if(p) l.setAttribute('data-field',p.value);
        return false;
      case e.target.classList.contains('php-close'):
        e.target.closest('ul').classList.add('display-none');
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
    $ffMenu.attr('data-field-name','cf7_2_post_map_taxonomy_value-'+tSlug);
    //setup the filter.
    $ffMenu.find('option.filter-option').val('cf7_2_post_filter-'+tSlug);
    //populate with latest form fields.
    $ffMenu.fillCF7fields();
    //add cloned field to DOM list of fields.
    $(fieldList).children('li:last').before($cloneField);
    $ffMenu.after('&nbsp;<span class="dashicons dashicons-remove remove-field"></span>');
    //enable the new field.
    $cloneField.find('span.link-button').removeClass('disabled').addClass('enabled');
    $cloneField.find(':input').each(function(){
      this.disabled=false;
      if(this.nodeName==='SELECT') new HybridDropdown(this); //nice select.
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
          field.classList.add('display-none');
          field.disabled=true; //hide & disable post field dropdown.
          field = fc.querySelector('.cf7-2-post-map-label-custom');
          field.disabled=false;
          field.classList.remove('display-none'); //show text input.
          fv = e.target.parentNode.nextElementSibling.value;
        }
        //setup the form field menu values.
        // ffMenu.setAttribute('name','cf7_2_post_map_meta_value-'+fv);
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+postType+'-'+fv;
        ffMenu.classList.add('autofill-field-name');
        if(ffMenu._hybriddd) ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_meta_value-'+fv}); //refresh hybrid select.
        break;
      case field.classList.contains('cf7-2-post-map-labels'): //update form field name.
      case field.classList.contains('cf7-2-post-map-label-custom'): //update form field name.
        // ffMenu.setAttribute('name','cf7_2_post_map_meta_value-'+fv);
        if(ffMenu._hybriddd){ //refresh hybrid select.
          ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_meta_value-'+fv});
        }
        break;
      case field.classList.contains('field-options'): //check if field already used.
        msgBox.innerHTML ='';
        if(isEmpty(fv)) break;
        if(fv.indexOf('cf7_2_post_filter-') < 0){
          let all = [...document.querySelector('#c2p-default-post-fields').children,
            ...document.querySelector('#c2p-post-meta-fields').children,
            ...document.querySelector('#c2p-taxonomy-fields').children];
          [].forEach.call(all, (l,i)=>{
            if(l==fc || l.classList.contains('default-meta-field')) return true;
            if(l.querySelector('.field-options').value === fv){ //field already mapped.
              msgBox.innerHTML = c2pLocal.warning;
              return false;
            }
          })
          if(ffMenu.classList.contains('autofill-field-name')){
            let pf = fc.querySelector('.cf7-2-post-map-label-custom');
            if('custom_meta_key'==pf.value) pf.value = fv.replace(/-/g,'_');
            if(ffMenu._hybriddd){ //refresh hybrid select.
              ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_meta_value-'+pf.value});
            }
          }
          if(field.classList.contains('taxonomy-options')){
            let tax = fc.querySelector('input.taxonomy-slug');
            // field.setAttribute('name','cf7_2_post_map_taxonomy_value-'+tax.value+'/'+ffMenu.value)
            if(ffMenu._hybriddd){ //refresh hybrid select.
              ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_taxonomy_value-'+tax.value+'/'+ffMenu.value});
            }
          }
          ffMenu.parentNode.classList.remove('hooked');
        }else{ //filter option selelected, display helper code.
           c2pFilterHelperCode.call(fc,fv);
           ffMenu.parentNode.classList.add('hooked');
        }

        break;
      case field.classList.contains('taxonomy-list'): //taxonomy selected.
        let tax = field.querySelector('option:checked'),
          isSystem = tax.classList.contains('system-taxonomy'),
          input = fc.querySelector('input.singular-name');
        input.value = tax.dataset.name;
        input.disabled = isSystem; //no need to be submitted
        input = fc.querySelector('input.plural-name');
        input.value = tax.innerHTML;
        input.disabled = isSystem; //no need to be submitted
        input = fc.querySelector('input.taxonomy-slug');
        input.value = tax.value;
        input.disabled = isSystem;//no need to be submitted
        input = fc.querySelector('span.taxonomy-name');
        input.innerHTML = '<strong>'+tax.innerHTML+'</strong>';
        input = fc.querySelector('input.taxonomy-source');
        input.value = isSystem ? 'system':'factory';
        input.setAttribute('name','cf7_2_post_map_taxonomy_source-'+tax.value);
        //update the form-field select name and filter.
        let v = ffMenu.value;
        if( v.indexOf('cf7_2_post_filter-') == 0){ //filter option.
          v='';
          c2pFilterHelperCode.call(fc,'cf7_2_post_filter-'+tax.value); //update filter.
        }
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+tax.value;
        // ffMenu.setAttribute('name','cf7_2_post_map_taxonomy_value-'+tax.value+'/'+v);
        if(ffMenu._hybriddd){ //refresh hybrid select.
          ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_taxonomy_value-'+tax.value+'/'+v});
        }
        break;
      case field.classList.contains('taxonomy-slug'): //update in factory custom taxonomy slug.
        //update the form-field select name and filter.
        // ffMenu.setAttribute('name','cf7_2_post_map_taxonomy_value-'+field.value);
        ffMenu.querySelector('.filter-option').value = 'cf7_2_post_filter-'+field.value;
        if(ffMenu._hybriddd){ //refresh hybrid select.
          ffMenu._hybriddd.refresh({'fieldName':'cf7_2_post_map_taxonomy_value-'+field.value});
        }
        fc.querySelector('.plural-name').setAttribute('name','cf7_2_post_map_taxonomy_names-'+fv);
        fc.querySelector('.singular-name').setAttribute('name','cf7_2_post_map_taxonomy_name-'+fv);
        field.setAttribute('name','cf7_2_post_map_taxonomy_slug-'+fv);
        break;
      case field.classList.contains('plural-name'): //update taxonomy label.
        fc.querySelector('.taxonomy-label-field > .taxonomy-name').innerHTML('<strong>'+fv+'</strong>');
      default:
        update = false;
        break;
    }
    if(update) c2pUpdateMapping();
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
    let field = filter.replace('cf7_2_post_filter-','').replace(/-/g,'_'),
			formKey = $('input#c2p-cf7-key').val(),
    	helper = "add_filter( '"+filter+"', 'filter_"+field+"', 10, 4 );\n";
		helper += "/**\n * Filter the value stored in the "+field+".\n *\n";
		helper += " * @param string $value is the post field value to filter/return, by default it is empty. You can return a combination of multiple form fields for example to store in the post content.\n";
		helper += " * @param string $post_id the post ID to which the form submission is saved to.\n";
		helper += " * @param Array $form_data is the submitted form data as an array of field-name=>value pairs.\n";
		helper += " * @param string $cf7_key the unique key identifying your form (the form post slug).\n";
		helper += " * @return mixed A string for most fields, or an array for meta-fields. If you are filtering a taxonomy you can return either slug/id/array.  in case of ids make sure to cast them as integers.(see https://codex.wordpress.org/Function_Reference/wp_set_object_terms for more information).\n */\n";
    helper +="function filter_"+field+"( $value, $post_id, $form_data, $cf7_key ) {\n";
		helper +="  if ( '"+formKey+"' === $cf7_key ) {// verify this is the correct form.\n";
		helper +="  	// do something and populate the $value field.\n";
		helper +="  }\n";
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
