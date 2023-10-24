<?php
/**
 * Print the front-end js script
 *
 * @since 5.3.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes/partials
 */

// prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die();
}
?>
(function( $ ) {
	'use strict';
		$( document).ready(function() {
		var fname;
		var $cf7Form = $("div#<?php echo esc_attr( $nonce ); ?> form.wpcf7-form");
		var $input;
		if($cf7Form.is('div.cf7-smart-grid.has-grid form.wpcf7-form')){
			//if the smart grid is enabled, execute the loading once the grid is ready
			$cf7Form.on('cf7SmartGridReady', function(){
			preloadForm($(this));
			});
		}else{
			preloadForm($cf7Form);
		}
		// function to load all the data into the form
		function preloadForm($cf7Form){
			var data = '';
			if('function' == typeof $.fn.post2CF7FormData) data = $cf7Form.post2CF7FormData('<?php echo esc_attr( $nonce ); ?>');
			else if( 'undefined' != typeof window['<?php echo esc_attr( $nonce ); ?>'] ) data = window['<?php echo esc_attr( $nonce ); ?>'];
			<?php /*@since 3.1.0 store form nonce for transient storage of post ID*/ ?>
			fname = '<input type="hidden" name="_cf72post_nonce" value="<?php echo esc_attr( $nonce ); ?>" />';
			$cf7Form.find('input[name="_wpcf7"]').parent().append(fname);
			if(0 === data.length){
			$cf7Form.trigger("<?php echo esc_attr( $nonce ); ?>", data);
			return false;
			}
	<?php
	/**
	 * Filter fields mapped to taxonomy (in case mapping is done on a system post)
	 *
	 * @since 1.3.2
	 * this can now be deprecated.
	 */
	$taxonomies = array();
	$taxonomies = apply_filters( 'cf7_2_post_map_extra_taxonomy', $taxonomies, $mapper->cf7_key );
	$taxonomies = array_merge( $mapper->get_mapped_taxonomy(), $taxonomies );
	$fields     = $mapper->get_form_fields();
	$form_id    = $mapper->cf7_post_id; // submisison mapped post id.

	foreach ( $fields as $field => $ftype ) {
		if ( isset( $taxonomies[ $field ] ) ) {
			continue; // handled after.
		}

		$json_var = str_replace( '-', '_', $field );
		// setup sprintf format, %1 = $field (field name), %2 = $json_var (json data variable).
		$js_form        = '$cf7Form';
		$json_value     = 'data.' . $json_var;
		$default_script = true;
		/**
		* Filter to modify the way the field is set.  This is introduced for plugin developers
		* who wish to load values for their custom fields.
		* By default the Post My CF7 Form will load the following js script,
		* `if(<$json_value> !== undefined){ //make sure a value is available for this field.
		*   <$js_form>.find("<input|select|textarea>[name=<$field>]").val(<$json_value>);
		* }`
		* which can be overriden by printing (echo) the custom script using the follwoing attributes,
		*
		* @since 2.0.0
		* @param boolean  $default_script  whether to use the default script or not, default is true.
		* @param string  $form_id  wpcf7_contact_form post  id.
		* @param string  $field  cf7 form field name
		* @param string  $ftype   field type (number, text, select...)
		* @param string  $json_value  the json value loaded for this field in the form.
		* @param string  $$js_form  the javascript variable in which the form is loaded.
		* @param string  $key  unique cf7 form key.
		* @return boolean  false to print a custom script from the called function, true for the default script printed by this plugin.
		*/
		if ( apply_filters_deprecated(
			'cf7_2_post_echo_field_mapping_script',
			array( $default_script, $form_id, $field, $ftype, $json_value, $js_form, $mapper->cf7_key ),
			'5.5.0',
			'cf7_2_post_form_values'
		) ) {
			printf(
				'if(data.%2$s !== undefined){' . PHP_EOL . '  $cf7Form.c2pCF7Field("' . esc_attr( $ftype ) . '", "%1$s", data.%2$s);' . PHP_EOL . '};' . PHP_EOL,
				esc_attr( $field ),
				esc_attr( $json_var )
			);
		}
	}


	/*
	Taxonomy fields
	*/
	$load_chosen_script = false;
	$hdd                = array();
	foreach ( $taxonomies as $form_field => $mapped_taxonomy ) {
		$js_field = str_replace( '-', '_', $form_field );
		if ( 0 === strpos( $form_field, 'cf7_2_post_filter-' ) ) {
			continue; // nothing to do here.
		}
		$field_type = $fields[ $form_field ];

		/** NB @since 5.0.0 skip if hybrid*/
		if ( $mapper->field_has_class( $form_field, 'hybrid-select' ) && 'select' !== $field_type ) {
			$hdd[] = esc_attr( $js_field );
			continue;
		}
		// load the taxonomy required.
		// legacy.

		$load_chosen = apply_filters( 'cf7_2_post_filter_cf7_taxonomy_chosen_select', true, $mapper->cf7_post_id, $form_field, $mapper->cf7_key ) && apply_filters( 'cf7_2_post_filter_cf7_taxonomy_select2', true, $mapper->cf7_post_id, $form_field, $mapper->cf7_key );

		if ( $load_chosen ) {
			$load_chosen_script = true;
		}

		$terms_id = array();

		switch ( $field_type ) {
			case 'select':
				if ( $mapper->field_has_option( $form_field, 'multiple' ) ) {
					$form_field = $form_field . '[]';
				}
				?>
		fname = JSON.parse(data.<?php echo esc_attr( $js_field ); ?>);
		$cf7Form.find('select[name="<?php echo esc_attr( $form_field ); ?>"]').append(fname);
		$('select.hybrid-select').not('.hybrid-no-init').each(function(){
			new HybridDropdown(this,{});
		})
				<?php

				break;
			case 'radio':
				?>
		fname = JSON.parse(data.<?php echo esc_attr( $js_field ); ?>);
		$cf7Form.find('span[data-name="<?php echo esc_attr( $form_field ); ?>"] span.wpcf7-radio').html(fname);
				<?php
				break;
			case 'checkbox':
				?>
		fname = JSON.parse(data.<?php echo esc_attr( $js_field ); ?>);
		$cf7Form.find('span[data-name="<?php echo esc_attr( $form_field ); ?>"] span.wpcf7-checkbox').html(fname);
				<?php
				break;
		}
	}
	if ( $load_chosen_script ) :
		$delay_chosen_script = apply_filters( 'cf7_2_post_filter_cf7_delay_chosen_launch', false, $mapper->cf7_post_id ) || apply_filters( 'cf7_2_post_filter_cf7_delay_select2_launch', false, $mapper->cf7_post_id );
		if ( ! $delay_chosen_script ) :
			?>
		$(".js-select2", $cf7Form).each(function(){
			$(this).select2();
		})
			<?php
		endif;
	endif
	// finally we need to cater for the post_id if there is one.
	?>
	if(data.map_post_id !== undefined){
		fname = '<input type="hidden" name="_map_post_id" id="cf2_2_post_id" value="' + data.map_post_id + '" />';
		$cf7Form.find('input[name="_wpcf7"]').parent().append(fname);
	}
	<?php
	/** NB @since 5.0.0 init hybrid dropdown */
	if ( ! empty( $hdd ) ) :
		echo "['" . esc_html( implode( "','", $hdd ) ) . "']";// escaped at array filling.
		?>
	.forEach(function(f){
		if(data[f]){
		let fn = data[f].fieldName,
			el = $('.'+fn+'> .hybrid-select', $cf7Form).not('.hybrid-no-init').get(0);
		if(el){
			new HybridDropdown(el, Object.assign(data[f],{
			'optionLabel':function (lbl){
				if('string' == typeof lbl) return `<span>${lbl}</span>`
				return `<span class="${lbl[1]}">${lbl[0]}</span>`
			}
			}))
		}
		}
	})
		<?php
endif; // empty hdd.
	if ( is_user_logged_in() ) :
		$user = wp_get_current_user();
		?>
		fname = '<input type="hidden" name="_map_author" id="cf7_2_post_user" value="<?php echo esc_attr( $user->ID ); ?>" />';
		$cf7Form.find('input[name="_wpcf7"]').parent().append(fname);
<?php endif; ?>

		/* trigger the formMapped event to let other scripts that the form is now ready */
		if( $cf7Form.is('.cf7-smart-grid .wpcf7-form') && !$cf7Form.is('.cf7sg-ready') ){
		$cf7Form.on('cf7SmartGridReady', function(){
			$cf7Form.trigger("<?php echo esc_html( $nonce ); ?>", data)
		})
		}else{
		$cf7Form.trigger("<?php echo esc_html( $nonce ); ?>", data);
		}
		//console.log('<?php echo esc_html( $nonce ); ?> form ready');
	}//end preloadForm()
	}); //document ready
	<?php /** NB @since 5.5.0 introcude a field value setter on jquery form object */ ?>
	//field setter for jquery form object.
	if(!$.isFunction( $.fn.c2pCF7Field)){
		$.fn.c2pCF7Field = function(fieldType, fieldName, fieldValue){
			let $form = $(this),
			$field = null;
			if(!$form.is('form.wpcf7-form')) return false;
			if(fieldType === null) fieldType = '';
			//do we have a field
			if(typeof fieldName == 'string'  && fieldName.length > 0 ){
				let pe = new CustomEvent(`c2p-prefill-field`, { name: fieldName,value: fieldValue });
				switch(fieldType){
					case 'checkbox':
					case 'radio':
						fieldName = 'checkbox'===fieldType ? `${fieldName}[]` : fieldName;
						if(!Array.isArray(fieldValue)) fieldValue = new Array(fieldValue);
						$.each(fieldValue , function(index, v){
							$field = $form.find(`input[name="${fieldName}"][value="${v}"]`).prop('checked',true).trigger('change');
						});
						break;
					case 'select':
					case 'dynamic_select':
						$field = $form.find(`select[name=${fieldName}]`).val(fieldValue).trigger("change");
						break;
					case 'textarea':
						$field = $form.find(`textarea[name=${fieldName}]`).val(fieldValue).trigger("change");
						break;
					case 'file':
						break;
					default:
						$field = $form.find(`input[name="${fieldName}"]`).val(fieldValue).trigger("change");
						break;
				}
				if($field) $field.get(0).dispatchEvent(pe);
			}
			return $form;
		}
	}
})( jQuery );
