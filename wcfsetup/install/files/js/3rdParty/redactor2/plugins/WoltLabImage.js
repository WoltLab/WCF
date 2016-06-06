$.Redactor.prototype.WoltLabImage = function() {
	"use strict";
	
	return {
		init: function() {
			// overwrite modal templates
			this.opts.modal['image-edit'] = '<div class="section">'
					+ '<dl>'
						+ '<dt><label>' + this.lang.get('title') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-title" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label>' + this.lang.get('link') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-image-link" class="long" aria-label="' + this.lang.get('link') + '"></dd>'
					+ '</dl>'
					+ '<div class="formSubmit">'
						+ '<button id="redactor-modal-button-action" class="buttonPrimary">Insert</button>'
						+ '<button id="redactor-modal-button-delete" class="redactor-modal-button-offset">Delete</button>'
					+ '</div>'
				+ '</div>';
			
		}
	};
};