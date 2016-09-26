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

    //functions
    function createNewTaxonomy(){
      parent = $(this).parent();

      //enable the new field
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
          msgBox.append('filter: <span class="code">'+$(this).attr('value')+'</span>');
          if(highlight) msgBox.addClass('animate-color');
        });
        return this;
    };

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
            isDuplicate = true;
            break;
          }
      });
      /* clear old msg */
      $(this).parent().next('p.cf7-post-error-msg').empty();
      if(isDuplicate){
        $(this).parent().next('p.cf7-post-error-msg').text("Warning: Field already selected!");
      }else if(selected.find('option:selected').hasClass('filter-option')){
        var value = selected.find('option:selected').val();
        $(this).parent().next('p.cf7-post-error-msg').append('filter: <span class="code">'+value+'</span>');
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
      $(this).parent().next('p.cf7-post-error-msg').empty();
      $(this).attr('name','cf7_2_post_map_meta-'+name);
      $(this).next('select').attr('name','cf7_2_post_map_meta_value-'+name);
      var option = $(this).next('select').find('option.filter-option');
      option.attr('value','cf7_2_post_filter-'+postType+'-'+name);
      if( option.is('option:selected') ){
        $(this).parent().next('p.cf7-post-error-msg').append('filter: <span class="code">'+option.attr("value")+'</span>');
        $(this).parent().next('p.cf7-post-error-msg').addClass('animate-color');
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
    }
    //function called when the taxonomy name changes
    function pluralName(){
      var taxonomyName = $(this).val();
      $(this).parent().prevAll('div.custom-taxonomy-field').eq(0).find('span.taxonomy-name strong').text(taxonomyName);
    }
    //post_type rename event
    $('div#post-type-select input#mapped_post_type').on('change',function(){
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
