(function( $ ) {
	'use strict';
  //var addButton = '<span id="add-more-field" class="dashicons dashicons-plus"></span>';
  var removeButton = '<span class="dashicons dashicons-minus remove-field"></span>';
  var errorBox = '<p class="cf7-post-error-msg"></p><div class="clear"></div>';
  var selectedOptions = new Array();
  var selectedCount = 0;


  $(document).ready(function() {
    var parent,keyName, newField,idx;
    var newField = $('#custom-meta-fields div.custom-meta-field').last().clone();
    var newTaxonomy = $('#post_taxonomy_map div.custom-taxonomy-field').last().clone();
    var newTaxonomyDetails = $('#post_taxonomy_map div.custom-taxonomy-input-fields').last().clone();
    //since 1.3
    $('select.taxonomy-list').on('change',function(){
      var option = $("option:selected", this);

      $(this).siblings('input.plural-name').val(option.text()).change();
      $(this).siblings('input.singular-name').val(option.data('name'));
      $(this).siblings('input.taxonomy-slug').val(option.val()).change();
      $(this).siblings('input.taxonomy-source').val('factory');

      if(option.hasClass('system-taxonomy')){
        $(this).siblings('input').prop('readonly',true);
        $(this).siblings('input.taxonomy-source').val('system');
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
      parent.find('select').prop('disabled',false);

      //add new field
      var cloneField = newTaxonomy.clone();
      var cloneDetails = newTaxonomyDetails.clone();
      parent.parent().append(cloneField).append(errorBox).append(cloneDetails);
      //bind event handlers
      cloneField.find('.add-more-field').on('click',createNewTaxonomy);
      //}
      $(this).css('display','none'); //hide the add button
      //add remove button
      parent.append(removeButton);
      //bind event handlers
      parent.activateTaxonomy();
      //find('.remove-field').on('click',removeField);
    }
    function createNewField(){
      parent = $(this).parent();
      var keyName = parent.find('.cf7-2-post-map-labels').val();
      idx = parseInt(keyName.substr(-1),10);
      ++idx;
      var input = newField.find('.cf7-2-post-map-labels');
      var select = newField.find('select');
      input.val('meta_key_'+idx);
      input.attr('name','cf7_2_post_map_meta-meta_key_'+idx);
      select.attr('name','cf7_2_post_map_meta_value-meta_key_'+idx);

      //enable the new field
      var postType = $('input#mapped_post_type').val();
      parent.find('.cf7-2-post-map-labels').prop('disabled',false);
      parent.find('select option.filter-option').val('cf7_2_post_filter-'+postType+'-'+keyName);
      parent.find('select').prop('disabled',false);
      //parent.find('select').attr('name',keyName+'_value');
      //parent.find('select').on('change',optionSelected);
      //if(parent.find('select option').length > selectedCount){
        //add new field
        var cloneField = newField.clone();
        parent.parent().append(cloneField).append(errorBox);
        //bind event handlers
        cloneField.find('.add-more-field').on('click',createNewField);
      //}
      $(this).css('display','none'); //hide the add button
      //add remove button
      parent.append(removeButton);
      //bind event handlers
      parent.activateField();
      //find('.remove-field').on('click',removeField);
    }
    //function to activate fields
    $.fn.activateField = function() {
        this.filter( 'div.custom-meta-field' ).each(function() {
          $(this).find('.remove-field').on('click',removeField);
          $(this).find('select').on('change',optionSelected);
          $(this).find('input.cf7-2-post-map-labels').on('change', metaKeyChange);
        });
        return this;
    };
    //function to activate taxonomy
    $.fn.activateTaxonomy = function() {
        this.filter( 'div.custom-taxonomy-field' ).each(function() {
          $(this).find('.remove-field').on('click',removeField);
          $(this).find('select').on('change',optionSelected);
          $(this).find('span.link-button.enabled').on('click', function(){
            var details = $(this).parents('div.custom-taxonomy-field').nextAll('div.custom-taxonomy-input-fields').eq(0);
            details.removeClass('hide-if-js');
            var parent = $(this).parents('div.custom-taxonomy-field');
            parent.hide();
            parent.next('p.cf7-post-error-msg').hide();
          });
          $(this).nextAll('div.custom-taxonomy-input-fields').eq(0).each(function(){
            $(this).find('button.close-details').on('click', closeDetails);
            $(this).find('input.taxonomy-slug').on('change', taxonomySlug);
            $(this).find('input.plural-name').on('change', pluralName);
          });
        });
        return this;
    };
    function closeDetails(){
      $(this).parent().addClass('hide-if-js');
      $(this).parent().prevAll('p.cf7-post-error-msg').eq(0).show();
      $(this).parent().prevAll('div.custom-taxonomy-field').eq(0).show();

    }

    //function to update selected hook messages
    $.fn.hookMessages = function(highlight) {
        this.filter( 'option.filter-option:selected' ).each(function() {
          var msgBox = $(this).closest('div.cf7-2-post-field').next('p.cf7-post-error-msg');
          msgBox.empty();
          var filter = $('<a class="code" data-clipboard-text="'+$(this).attr('value')+'" href="javascript:void(0);">'+$(this).attr('value')+'</a>').appendTo(msgBox);
          msgBox.prepend('filter:');
          msgBox.append('<span class="popup">Click to Copy!</span>')
          new Clipboard(filter[0]);
          if(highlight) msgBox.addClass('animate-color');
        });
        return this;
    };
    /** Added @since 1.5 automatically populates the meta_field name */
    //delegate the change on meta field mapping.
    $('#custom-meta-fields').change('select.field-options', function(event){
      var $target = $(event.target);
      if( $target.is('select.field-options') ){
        if($target.is('.autofill-field-name')){
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
      }
    });
    //switch-off auto-fill is the name is manually edited.
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
        if($parent.is('.autofill-field-name')){
          var $previous = $parent.prevAll('.custom-meta-field:first').find('select.field-options').find('option:selected');
          var $select = $parent.find('select.field-options');
          if($select.find('option:last').val() != $previous.val() ){
            $select.addClass('autofill-field-name'); //allows change to autofill.
            var $nextOption = $select.find('option[value="'+$previous.val()+'"]').next();
            $nextOption.prop('selected','true'); //select.
            $select.trigger('change');
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
        var otherSelected = $(this).val();
        switch(true){
          case ''== otherSelected:
            break;
          case $(this).hasClass('filter-option'):
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
      var msgBox = $(this).parent().next('p.cf7-post-error-msg').empty();
      if(isDuplicate){
        msgBox.text("Warning: Field already selected!");
      }else if(selected.find('option:selected').hasClass('filter-option')){
        var value = selected.find('option:selected').val();
        var filter = $('<a class="code" data-clipboard-text="'+ value +'" href="javascript:void(0);">'+ value +'</a>').appendTo(msgBox);
        msgBox.prepend('filter:');
        msgBox.append('<span class="popup">Click to Copy!</span>');
        new Clipboard(filter[0]);
      }
    }
    function removeField(){
      parent = $(this).parent();
      var error = parent.next('p.cf7-post-error-msg');
      var details = parent.nextAll('div.custom-taxonomy-input-fields').eq(0); //if taxonomy
      parent.remove();
      error.remove();
      if(details.length) details.remove();
    }
    function metaKeyChange(){
      var name = $(this).val();
      var postType = $('input#mapped_post_type').val();
      //clear message box
      var msgBox = $(this).parent().next('p.cf7-post-error-msg').empty();
      $(this).attr('name','cf7_2_post_map_meta-'+name);
      $(this).next('select').attr('name','cf7_2_post_map_meta_value-'+name);

      var option = $(this).next('select').find('option.filter-option');
      option.attr('value','cf7_2_post_filter-'+postType+'-'+name);
      if( option.is('option:selected') ){
        var filter = $('<a class="code" data-clipboard-text="'+option.attr('value')+'" href="javascript:void(0);">'+option.attr('value')+'</a>').appendTo(msgBox);
        msgBox.prepend('filter:');
        msgBox.append('<span class="popup">Click to Copy!</span>');
        msgBox.addClass('animate-color');
        new Clipboard(filter[0]);
      }
    }
    //change in slug of taxonomy
    function taxonomySlug(){
      var taxonmyField = $(this).parent().prevAll('div.custom-taxonomy-field').eq(0);
      var slug = $(this).val();
      taxonmyField.find('select').attr('name','cf7_2_post_map_taxonomy_value-'+slug);
      //reset the select box
      taxonmyField.find('select').prop('selectedIndex',0);
      var option = taxonmyField.find('select').find('option.filter-option');
      option.attr('value','cf7_2_post_filter-'+slug);
      //reset the msg box
      taxonmyField.next('p.cf7-post-error-msg').empty();
      //change the other input names
      $(this).parent().find('input.singular-name').attr('name','cf7_2_post_map_taxonomy_name-'+slug);
      $(this).parent().find('input.plural-name').attr('name','cf7_2_post_map_taxonomy_names-'+slug);
      $(this).parent().find('input.taxonomy-source').attr('name','cf7_2_post_map_taxonomy_source-'+slug);
      $(this).parent().find('input.taxonomy-slug').attr('name','cf7_2_post_map_taxonomy_slug-'+slug);
    }
    //function called when the taxonomy name changes
    function pluralName(){
      var taxonomyName = $(this).val();
      $(this).parent().prevAll('div.custom-taxonomy-field').eq(0).find('span.taxonomy-name strong').text(taxonomyName);
    }
    //post_type rename event
    $('div#post-type-select input#custom_post_type').on('change',function(){
      //udpate the hidden field
      $('input#mapped_post_type').val($(this).val());
      //rename the filter options
      var postType = $(this).val();
      $('div.post-meta-field').each(function(){
        var name = $(this).find('select.field-options').attr('name');
         name = name.replace('cf7_2_post_map-','');
        $(this).find('option.filter-option').attr('value','cf7_2_post_filter-'+postType+'-'+name);
      })
      $('div.custom-meta-field').each(function(){
        var name = $(this).find('select.field-options').attr('name');
        name = name.replace('cf7_2_post_map_meta_value-','');
        $(this).find('option.filter-option').attr('value','cf7_2_post_filter-'+postType+'-'+name);
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
      var $system = $('#system-poststuff');
      var $factory = $('#postcustomstuff');
      var $selectPost = $('#post-type-exists');
      var $customPost = $('#post-type-select');
      var $post = $('#system-post-type option:selected');
      var $mapped_type = $('input#mapped_post_type');
      var type='';
      switch($source.val()){
        case 'factory':
          $system.hide();
          $selectPost.hide();
          $customPost.show();
          $factory.show();
          type = $('input#custom_post_type').val();
          $mapped_type.val(type);
          break;
        case 'system':
          $('h3', $system).text('This form is mapped to an existing post: '+$post.text() );
          $('p span.action-form-map',$system).text( 'cf7_2_post_save-'+$post.val() );
          $('p span.filter-form-load',$system).text( 'cf7_2_post_load-'+$post.val() );
          $mapped_type.val($post.val());
          $factory.hide();
          $customPost.hide();
          $selectPost.show();
          $system.show();
          break;
      }
    });
    $('#system-post-type').on('change', function(){
      var $system = $('#system-poststuff');
      var $mapped_type = $('input#mapped_post_type');
      $mapped_type.val( $(this).val() );
      $('h3', $system).text('This form is mapped to an existing post: '+ $(this).val() );
      var anime = $('p span.action-form-map', $system).text( 'cf7_2_post_save-'+$(this).val() ).closest('p');
      anime.before(anime.clone());
      anime.remove();
      anime = $('p span.filter-form-load',$system).text( 'cf7_2_post_load-'+$(this).val() ).closest('p');
      anime.before(anime.clone());
      anime.remove();
      $mapped_type.val( $(this).val() );
    });
  });
  /*set elemetn widths once all images are loaded*/
  $(window).load(function() {
    //set up the mesage box width
    var fieldWidth = 0;
    $('form#cf7-post-mapping-form div.cf7-2-post-field').each(function(){
      var exactWidth = $(this).width()+1; /*+1 for rounding errors*/
      if( exactWidth > fieldWidth ) fieldWidth = exactWidth;
    });
    $('div.cf7-2-post-field').width(fieldWidth);
    $('.cf7-post-error-msg').css({'width':'calc(100% - '+(fieldWidth+10)+'px)'});
  });
})( jQuery );
