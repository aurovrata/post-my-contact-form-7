(function( $ ) {
	'use strict';
  //var addButton = '<span id="add-more-field" class="dashicons dashicons-plus"></span>';
  var removeButton = '<span class="dashicons dashicons-minus remove-field"></span>';
  var errorBox = '<p class="cf7-post-error-msg"></p><div class="clear"></div>';
  var selectedOptions = new Array();
  var selectedCount = 0;

  /**TODO implement simpler form, with no name attributes on input fields, and instead build ajax data to be sent back to server depending on inputs*/
  $(document).ready(function() {
    //since 3.0.0
    var $mapForm = $('#cf7-post-mapping-form');
    $('.nice-select', $mapForm).each(function(){
      $(this).select2();
    });
    var parent,keyName, newField,idx;
    var newField = $('#custom-meta-fields div.custom-meta-field').last().clone();
    var newTaxonomy = $('#post_taxonomy_map div.custom-taxonomy-field').last().clone();
    var newTaxonomyDetails = $('#post_taxonomy_map div.custom-taxonomy-input-fields').last().clone();

    postboxes.add_postbox_toggles(pagenow);
    //since 1.3
    function taxonomySelected(){
      var option = $("option:selected", this);
      var $this = $(this);
      $this.siblings('input.plural-name').val(option.text()).change();
      $this.siblings('input.singular-name').val(option.data('name'));
      $this.siblings('input.taxonomy-slug').val(option.val()).change();
      $this.siblings('input.taxonomy-source').val('factory');

      if(option.hasClass('system-taxonomy')){
        $this.siblings('input').prop('readonly',true);
        $this.siblings('input.taxonomy-source').val('system');
      }

    }
		/**toggle menu position field
		*@since 2.1
		**/
		$('input#menu-position-checkbox').click(function(){
			if($(this).is(':checked')){
				$('#menu-position').show();
			}else{
				$('#menu-position').hide();
			}
		});
    //functions
    function createNewTaxonomy(){
      //enable the new field
      parent = $(this).parent();
      var postType = $('input#mapped_post_type').val();
      var button = parent.find('.taxonomy-label-field span.link-button');
      //enable input fiedls in the details section
      button.removeClass('disabled');
      button.addClass('enabled');
      //taxonomy details
      var details = parent.nextAll('div.custom-taxonomy-input-fields').eq(0);
      details.find('input').prop('disabled',false);;
      var fieldName = details.find('input.taxonomy-slug').attr('name');
      //alert(fieldName);
      var slug = fieldName.replace('cf7_2_post_map_taxonomy_slug-','')
      //details.find('input[name='+fieldName+']').prop('disabled',false);
      //fieldName = fieldName.replace('cf7_2_post_map_taxonomy_names','cf7_2_post_map_taxonomy_name')
      //details.find('input[name='+fieldName+']').prop('disabled',false);
      parent.find('select option.filter-option').val('cf7_2_post_filter-'+slug);
      parent.find('select').each(function(){
        $(this).prop('disabled',false);
      });

      //add new field
      var cloneField = newTaxonomy.clone();
      var cloneDetails = newTaxonomyDetails.clone();
      parent.parent().append(cloneField).append(errorBox).append(cloneDetails);
      //bind event handlers
      cloneField.find('.add-more-field').on('click',createNewTaxonomy);
      cloneField.find('.select2').remove();
      cloneDetails.find('.select2').remove();
      $('select', cloneField).each(function(){
        $(this).select2();
      });
      $('select', cloneDetails).each(function(){
        $(this).select2();
      });
      //}
      $(this).css('display','none'); //hide the add button
      //add remove button
      parent.append(removeButton);
      //bind event handlers
      parent.activateTaxonomy();
      //find('.remove-field').on('click',removeField);
    }
    function createNewField(){
      var $this = $(this);
      parent = $this.parent();
      var keyName;
      idx=0;

      var cloneField;

      if($('#post-type-exists').is(':visible') && $newSystemField.length>0){
        $('select.cf7-2-post-map-labels' ,$newSystemField).attr('name','');
        $('select.field-options' ,$newSystemField).attr('name','');
        keyName = '';
        cloneField = $newSystemField.clone();
      }else{
        var input = newField.find('.cf7-2-post-map-labels');
        var select = newField.find('select');
        keyName = parent.find('.cf7-2-post-map-labels').val();
        idx = parseInt(keyName.substr(-1),10);
        ++idx;
        input.val('meta_key_'+idx);
        input.attr('name','cf7_2_post_map_meta-meta_key_'+idx);
        select.attr('name','cf7_2_post_map_meta_value-meta_key_'+idx);
        cloneField = newField.clone();
      }
      cloneField.find('.select2').remove();
      //enable the new field
      var postType = $('input#mapped_post_type').val();
      parent.find('select option.filter-option').val('cf7_2_post_filter-'+postType+'-'+keyName);
      parent.find('.cf7-2-post-map-labels:first').prop('disabled',false);
      parent.find('select').each(function(){
        $(this).prop('disabled',false).trigger('change');
      });
      //add new field
      parent.parent().append(cloneField).append(errorBox);
      cloneField.find('select').each(function(){
        $(this).select2();
      });
      //bind event handlers
      cloneField.find('.add-more-field').on('click',createNewField);
      $this.css('display','none'); //hide the add button
      //add remove button
      parent.append(removeButton);
      //bind event handlers
      parent.activateField();
    }
    //function to activate fields
    $.fn.activateField = function() {
        this.filter( 'div.custom-meta-field' ).each(function() {
          var $this = $(this);
          $this.find('.remove-field').on('click',removeField);
          $this.find('select.field-options').on('change',optionSelected);
          $this.find('.cf7-2-post-map-labels:visible').on('change', metaKeyChange);
        });
        return this;
    };
    //function to activate taxonomy
    $.fn.activateTaxonomy = function() {
        this.filter( 'div.custom-taxonomy-field' ).each(function() {
          var $tax = $(this);
          $tax.find('.remove-field').on('click',removeField);
          $tax.find('select').on('change',optionSelected);
          $tax.find('span.link-button.enabled').on('click', function(){
            var $this = $(this);
            var details = $this.parents('div.custom-taxonomy-field').nextAll('div.custom-taxonomy-input-fields').eq(0);
            details.removeClass('hide-if-js');
            details.find('select.taxonomy-list').on('change',taxonomySelected);
            var parent = $this.parents('div.custom-taxonomy-field');
            parent.hide();
            parent.next('p.cf7-post-error-msg').hide();
          });
          $tax.nextAll('div.custom-taxonomy-input-fields').eq(0).each(function(){
            var $this = $(this);
            $this.find('button.close-details').on('click', closeDetails);
            $this.find('input.taxonomy-slug').on('change', taxonomySlug);
            $this.find('input.plural-name').on('change', pluralName);
          });
        });
        return this;
    };
    function closeDetails(){
      var $this = $(this);
      $this.parent().addClass('hide-if-js');
      $this.parent().prevAll('p.cf7-post-error-msg').eq(0).show();
      $this.parent().prevAll('div.custom-taxonomy-field').eq(0).show();
    }

    //function to update selected hook messages
    $.fn.hookMessages = function(highlight) {
        this.filter( 'option.filter-option:selected' ).each(function() {
          var $this = $(this);
          var msgBox = $this.closest('div.cf7-2-post-field').next('p.cf7-post-error-msg');
          msgBox.empty();
          var value = $this.attr('value');
          var helper = filterHelper(value);

          var filter = $('<a class="code" data-clipboard-text="'+helper+'" href="javascript:void(0);">'+$this.attr('value')+'</a>').appendTo(msgBox);
          msgBox.prepend('filter:');
          msgBox.append('<span class="popup">Click to Copy!</span>')
          new Clipboard(filter[0]);
          if(highlight) msgBox.addClass('animate-color');
        });
        return this;
    };
    /** Added @since 1.5 automatically populates the meta_field name */
    //delegate the change on meta field mapping.
    $('#custom-meta-fields').change('select', function(event){
      var $target = $(event.target);
      if( $target.is('select.field-options') ){
        var $option = $('option[value="'+$target.val()+'"]', $target);
        if($target.is('.autofill-field-name') && !$option.is('.filter-option')){
          var name = $target.val().replace(/-/g,'_');
          var $parent = $target.closest('.custom-meta-field');
          $parent.find('input.cf7-2-post-map-labels').val(name).trigger('change');
          //$target.removeClass('autofill-field-name');
          //if this funcitonality has been used, let's replicate it on the next meta field input.
          var $next = $parent.nextAll('.custom-meta-field:first');
          if($next.is($parent.siblings('.custom-meta-field:last') ) ){ //next is last.
            $next.addClass('autofill-field-name'); //prep for autofill.
          }
        }
      }else if($target.is('select.cf7-2-post-map-labels')){
				if('cf72post-custom-meta-field'===$target.val()){
					$target.hide();
          $target.next('.select2').hide();
					var $input = $target.siblings('input.cf7-2-post-map-label-custom').show();
					$input.prop('disabled', false).addClass('cf7-2-post-map-labels');
          $input.on('change', function(){
            var $this = $(this);
            $target.append('<option value="'+$this.val()+'">').val($this.val());
            $this.each(metaKeyChange);
          });
          $input.each(metaKeyChange); //udpate with the default meta field.
				}else{
          $target.each(metaKeyChange);
        }
			}
    });
    //switch-off auto-fill if the name is manually edited.
    $('#custom-meta-fields').keyup('input.cf7-2-post-map-labels', function(event){
      var $target = $(event.target);
      if( $target.is('.cf7-2-post-map-labels') ){
        //remove the autofill class on the dropdopwn.
        var $parent = $target.closest('.custom-meta-field');
        $parent.find('select.field-options').removeClass('autofill-field-name');
        $parent.nextAll('.custom-meta-field:first').removeClass('autofill-field-name');
      }
    });
    //auto-fill the meta-field name.
    $('#custom-meta-fields').on('click', 'span.add-more-field', function(event){
      var $target = $(event.target);
      if( $target.is('.add-more-field') ){
        var $parent = $target.closest('.custom-meta-field');
        if($parent.children('.cf7-2-post-map-labels:first').is('select')){
          return;//this is not a text field label.
        }
        if($parent.is('.autofill-field-name')){
          var $previous = $parent.prevAll('.custom-meta-field:first').find('select.field-options').find('option:selected');
          var $select = $parent.find('select.field-options');
          if($select.find('option:last').val() != $previous.val() ){
            $select.addClass('autofill-field-name'); //allows change to autofill.
            var $nextOption = $select.find('option[value="'+$previous.val()+'"]').next();
            $nextOption.prop('selected','true'); //select.
            $select.val($nextOption.val()).trigger('change');
            var name = $nextOption.val().replace(/-/g,'_');
            $parent.find('input.cf7-2-post-map-labels').val(name).trigger('change');
            if( $nextOption.next().val() != $select.find('option:last').val()){
              $parent.nextAll('.custom-meta-field:first').addClass('autofill-field-name'); //prep for autofill.
            }
          }
          $parent.removeClass('autofill-field-name');
        }else{ //enable autofill on the select
          $parent.find('select.field-options').addClass('autofill-field-name');
        }
      }
    });

    //when the select dropdown changes
    function optionSelected(){
      var selected = $(this);
      var isDuplicate = false;
      selectedCount = 0;
      selectedOptions = new Array();
      $('select.field-options option:selected').each(function(){
        var $this = $(this);
        var otherSelected = $this.val();
        switch(true){
          case ''== otherSelected:
            break;
          case $this.hasClass('filter-option'):
            break;
          case $.inArray(otherSelected,selectedOptions)<0:
            selectedOptions[selectedOptions.length]=otherSelected;
            ++selectedCount;
            break;
          default:
            if(otherSelected==selected.val()){
              isDuplicate = true;
            }
            break;
          }
      });
      /* clear old msg */
      var msgBox = selected.parent().next('p.cf7-post-error-msg').empty();
      if(isDuplicate){
        msgBox.text("Warning: Field already selected!");
      }else if(selected.find('option:selected').hasClass('filter-option')){
        var value = selected.find('option:selected').val();
        var helper = filterHelper(value);
        var filter = $('<a class="code" data-clipboard-text="'+ helper +'" href="javascript:void(0);">'+ value +'</a>').appendTo(msgBox);
        msgBox.prepend('filter:');
        msgBox.append('<span class="popup">Click to Copy!</span>');
        new Clipboard(filter[0]);
      }
    }
    function filterHelper(filter){
      var field = filter.replace('cf7_2_post_filter-','');
      field = field.replace(/-/g,'_');
      var helper = "add_filter('"+filter+"','filter_"+field+"',10,3);\n";
      helper +="function filter_"+field+"($value, $post_id, $form_data){\n  //$value is the post field value to return, by default it is empty. If you are filtering a taxonomy you can return either slug/id/array.  in case of ids make sure to cast them integers.(see https://codex.wordpress.org/Function_Reference/wp_set_object_terms for more information.)\n  //$post_id is the ID of the post to which the form values are being mapped to\n  // $form_data is the submitted form data as an array of field-name=>value pairs\n";
      helper +="  return $value;\n}";
      return helper;
    }
    // called by remove button
    function removeField(){
      parent = $(this).parent();
      var error = parent.next('p.cf7-post-error-msg');
      var details = parent.nextAll('div.custom-taxonomy-input-fields').eq(0); //if taxonomy
      parent.remove();
      error.remove();
      if(details.length) details.remove();
    }
    //called when meta field value changes
    function metaKeyChange(){
      var $this = $(this);
      var name = $this.val();
      if('cf72post-custom-meta-field'===name) return true;
      var postType = $('input#mapped_post_type').val();
      //clear message box
      var msgBox = $this.parent().next('p.cf7-post-error-msg');
      $this.attr('name','cf7_2_post_map_meta-'+name);
      var $cf7Fields = $this.siblings('select.field-options');
      $cf7Fields.attr('name','cf7_2_post_map_meta_value-'+name);

      var option = $cf7Fields.find('option.filter-option');
      option.attr('value','cf7_2_post_filter-'+postType+'-'+name);
      if( option.is('option:selected') ){
        msgBox.empty();
        var filter = $('<a class="code" data-clipboard-text="'+option.attr('value')+'" href="javascript:void(0);">'+option.attr('value')+'</a>').appendTo(msgBox);
        msgBox.prepend('filter:');
        msgBox.append('<span class="popup">Click to Copy!</span>');
        msgBox.addClass('animate-color');
        new Clipboard(filter[0]);
      }
    }
    //change in slug of taxonomy
    function taxonomySlug(){
      var $this = $(this);
      var taxonmyField = $this.parent().prevAll('div.custom-taxonomy-field').eq(0);
      var $fieldSelect = taxonmyField.find('select');
      var slug = $this.val();
      //var mapped = $fieldSelect.val();
      $fieldSelect.attr('name','cf7_2_post_map_taxonomy_value-'+slug);
      //reset the select box
      //$fieldSelect.prop('selectedIndex',0);
      var option = $fieldSelect.find('option.filter-option');
      option.attr('value','cf7_2_post_filter-'+slug);
      //reset the msg box
      taxonmyField.next('p.cf7-post-error-msg').empty();
      //change the other input names
      $this.parent().find('input.singular-name').attr('name','cf7_2_post_map_taxonomy_name-'+slug);
      $this.parent().find('input.plural-name').attr('name','cf7_2_post_map_taxonomy_names-'+slug);
      $this.parent().find('input.taxonomy-source').attr('name','cf7_2_post_map_taxonomy_source-'+slug);
      $this.parent().find('input.taxonomy-slug').attr('name','cf7_2_post_map_taxonomy_slug-'+slug);
    }
    //function called when the taxonomy name changes
    function pluralName(){
      var $this = $(this);
      var taxonomyName = $this.val();
      $this.parent().prevAll('div.custom-taxonomy-field').eq(0).find('span.taxonomy-name strong').text(taxonomyName);
    }
    //post_type rename event
    $('div#post-type-select input#custom_post_type').on('change',function(){
      var $this = $(this);
      //udpate the hidden field
      $('input#mapped_post_type').val($this.val());
      //rename the filter options
      var postType = $this.val();
      $('div.post-meta-field').each(function(){
        var $select = $('select.field-options', $(this));
        var name = $select.attr('name');
        name = name.replace('cf7_2_post_map-','');
        $('option.filter-option', $select).attr('value','cf7_2_post_filter-'+postType+'-'+name);
      })
      $('div.custom-meta-field').each(function(){
        var $this = $(this);
        var name = $this.find('select.field-options').attr('name');
        name = name.replace('cf7_2_post_map_meta_value-','');
        $this.find('option.filter-option').attr('value','cf7_2_post_filter-'+postType+'-'+name);
      })
      //update hook messages
      $('option.filter-option').hookMessages(true);
    });
    //bind event handlers for div.post-meta-field
    $('div.post-meta-field select.post-options').on('change',optionSelected);
    $('div.custom-meta-field .add-more-field').on('click',createNewField);
    $('div.custom-taxonomy-field .add-more-field').on('click',createNewTaxonomy);

    //side metabox edit link
    $('a.edit-post-type').on('click',function(){
      $(this).addClass('hide-if-js');
      $('#post-type-select').removeClass('hide-if-js');
    });
    //taxonomy details
    // $('span.link-button.enabled').on('click',function(){
    //   $(this).parents('.custom-taxonomy-field').siblings('.custom-taxonomy-input-fields').removeClass('hide-if-js');
    // });
    // $('.custom-taxonomy-input-fields button').on('click',function(){
    //   $(this).parent('.custom-taxonomy-input-fields').addClass('hide-if-js');
    // });
    //bind event handlers for div.custom-meta-field
    $('div.custom-meta-field').activateField();
    $('div.custom-taxonomy-field').activateTaxonomy();
    //update hook messages
    $('option.filter-option').hookMessages(false);

    //AJAX Submission
    //submit button

    $('form#cf7-post-mapping-form').submit(function(event){
      event.preventDefault();
      var buttonID = $(this).prop('submited');
      var source = $('#post_type_source').val();
      $('.spinner.'+buttonID).css('visibility','visible');
      switch(buttonID){
        case 'save_draft':
          $('div#ajax-response').text('Saving...');
          break;
        case 'save_post':
          var msg = $('input#'+buttonID).val().slice(0,-1) + 'ing...';
          $('div#ajax-response').text(msg);
          break;
      }
      $.ajax({
        type:'POST',
        action:'save_post_mapping',
        dataType: 'json',
        url: cf7_2_post_ajaxData.url,
        data: $(this).serialize()+'&'+buttonID+'=selected',
        success:function(data){
          $('.spinner.'+buttonID).css('visibility','hidden');
          $('div#ajax-response').text(data.data.msg);
          if('created'==data.data.post) location.reload();
          else if('system' == source) location.reload();
          $('div#ajax-response').removeClass('error-msg');
        },
        error:function(data){
          $('.spinner.'+buttonID).css('visibility','hidden');
          $('div#ajax-response').text(data.data.msg);
          $('div#ajax-response').addClass('error-msg');
        }
      });
    });
    //existing post selection
    $('#post_type_source').on('change', function(){
      var $source = $(this).find('option:selected');
      var $factory = $('#postcustomstuff');
      var $selectPost = $('#post-type-exists');
      var $customPost = $('#post-type-select');
      var $post = $('#system-post-type option:selected');
      var $mapped_type = $('input#mapped_post_type');
      var $postbox = $('#postbox-container-2');
      var $filterbox = $('#postbox-container-3');
      var $draftButton = $('#save-draft-action');
      var type='';
      switch($source.val()){
        case 'factory':
          $postbox.show();
          $filterbox.hide();
          $selectPost.hide();
          $customPost.show();
          $draftButton.show();
          type = $('input#custom_post_type').val();
          $mapped_type.val(type);
          break;
        case 'system':
          $postbox.show();
          $filterbox.hide();
          $mapped_type.val($post.val());
          $customPost.hide();
          $selectPost.show();
          $draftButton.show();
          break;
        case 'filter':
          $postbox.hide();
          $filterbox.show();
          $selectPost.hide();
          $customPost.hide();
          $draftButton.fadeOut();
      }
    });
    /**@since 2.0.0 get system post meta fields as select option */
    var $newSystemField=''; //store the clnable meta field for system posts.
    $('#system-post-type').on('change', function(){
      var $post = $(this);
      //get the options
      $('#custom-meta-fields .custom-meta-field .cf7-2-post-map-labels').hide();
      $('#custom-meta-fields .custom-meta-field .spinner.meta-label').show().css('display','inline-block');
      var postType = $post.val();
      $.ajax({
        type:'POST',
        dataType: 'json',
        url: ajaxurl,
        data: {
          action:'get_meta_options',
          post_type: postType,
          cf7_2_post_nonce: $('#cf7_2_post_nonce').val()
        },
        success:function(data){
          $('#custom-meta-fields .custom-meta-field').each(function(index){
            var $this = $(this);
            $this.removeClass('autofill-field-name');
            $this.removeClass('autofill-field-name');
            var disable = '';
            if($this.is('.custom-meta-field:last')){
							disable = ' disabled="disabled"';
						}
            var $label = $('<select class="cf7-2-post-map-labels metas-'+postType+'" '+ disable +'>');
            $label.append('<option value="">Select a field</option>')
            $label.append(data.data.options);
						$label.append('<option value="cf72post-custom-meta-field">Custom field</option>')
            $('.spinner' , $this).hide().after($label);
            $label.siblings('.cf7-2-post-map-labels').removeClass('autofill-field-name').prop('disabled', true);
						$label.after('<input class="cf7-2-post-map-label-custom display-none" type="text" value="custom_meta_key" disabled>');
            $label.select2();
            if($this.is('.custom-meta-field:last')){
              $newSystemField = $this.clone();//reset for field cloning
            }
          });
          //$newSystemField = $newSystemField.clone();
        },
        error:function(data){
          var $label = $('<em>error in getting fields</em>');
          $('.spinner' , $(this)).hide().after($label);
        }
      });

      var $system = $('#system-poststuff');
      var $mapped_type = $('input#mapped_post_type');
      var postName = $('option[value="'+postType+'"]', $post).text();
      $('#custom-post-title').text(postName);
      $mapped_type.val( postType );
      $('h3', $system).text('This form is mapped to an existing post: '+ $post.val() );
      var anime = $('p span.action-form-map', $system).text( 'cf7_2_post_save-'+postType ).closest('p');
      anime.before(anime.clone());
      anime.remove();
      anime = $('p span.filter-form-load',$system).text( 'cf7_2_post_load-'+postType ).closest('p');
      anime.before(anime.clone());
      anime.remove();
      $mapped_type.val( postType );
      //rename the filter options
      $('div.post-meta-field').each(function(){
        var $select = $('select.field-options', $(this));
        var name = $select.attr('name');
        name = name.replace('cf7_2_post_map-','');
        $('option.filter-option', $select).attr('value','cf7_2_post_filter-'+postType+'-'+name);
      })
      $('div.custom-meta-field').each(function(){
        var $this = $(this);
        var name = $this.find('select.field-options').attr('name');
        name = name.replace('cf7_2_post_map_meta_value-','');
        $this.find('option.filter-option').attr('value','cf7_2_post_filter-'+postType+'-'+name);
      })
      //update hook messages
      $('option.filter-option').hookMessages(true);
    });
  });

})( jQuery );
