;(function($) {

	// Listen to the field_type dropdown for changes
	$('#acf_fields tr.field_type select').live('change', function() {
		var $this = $(this),
			field = $this.closest('.field');
		
		if($this.val() == 'required-field') {
			// we use setTimeout here to make sure the other listeners bound to change can complete first.
			// http://www.advancedcustomfields.com/support/discussion/1307/feature-request-javascript-event-after-a-field-type-has-changed.
			setTimeout(function() {
				$('select.required-field-type', field).change();
			}, 10);
		}
	});
	
	// Show and hide necessary field_option when required type changes
	$('#acf_fields tr.field_option select.required-field-type').live('change', function() {
		var $this = $(this),
			field = $this.closest('.field'),
			type = $this.val();
		
		$('tr.field_option', field).not('tr.field_option_required-field')
			.hide()
			.find(':input[name]').attr('disabled', 'disabled').end()
			.filter('tr.field_option_'+type)
				.find(':input[name]').removeAttr('disabled').end()
				.insertAfter($('tr.field_option_required-field:last', field))
				.show();
	});
	
	// document.ready
	$(function() {
		
		//Add an asterisk before required field labels
		$('div.acf_postbox .required-field')
			.siblings('label.field_label')
			.each(function() {
				$(this).text(' ' + $(this).text());
			})
			.prepend( $('<span class="required">'+acf_required_field_l10n.asterisk+'</span>') );
		
		//Add required-field-error class to a fields parent div.field
		$('div.acf_postbox .required-field-error')
			.closest('.field')
			.addClass('required-field-error');
			
		//Add required text to metaboxes containing a required field
		$('div.acf_postbox')
			.has('.required-field')
			.find('div.inside')
				.append( $('<div class="field required">'+acf_required_field_l10n.required+'</div>') );
		
	});
	
})(jQuery)