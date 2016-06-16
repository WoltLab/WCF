define(['Language', 'Ui/Dialog'], function(Language, UiDialog) {
	"use strict";
	
	var _boundListener = false;
	var _callback = null;
	
	return {
		showDialog: function(options) {
			UiDialog.open(this);
			
			UiDialog.setTitle(this, Language.get('wcf.editor.link.' + (options.insert ? 'add' : 'edit')));
			
			var submitButton = elById('redactor-modal-button-action');
			submitButton.textContent = Language.get('wcf.global.button.' + (options.insert ? 'insert' : 'save'));
			
			_callback = options.submitCallback;
			
			if (!_boundListener) {
				_boundListener = true;
				
				submitButton.addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
			}
		},
		
		_submit: function() {
			if (_callback()) {
				UiDialog.close(this);
			}
			else {
				var url = elById('redactor-link-url');
				var small = (url.nextElementSibling && url.nextElementSibling.nodeName === 'SMALL') ? url.nextElementSibling : null;
				
				if (small === null) {
					small = elCreate('small');
					small.className = 'innerError';
					small.textContent = Language.get('wcf.global.form.error.empty');
					url.parentNode.appendChild(small);
				}
			}
		},
		
		_dialogSetup: function() {
			return {
				id: 'redactorDialogLink',
				options: {
					onClose: function() {
						var url = elById('redactor-link-url');
						var small = (url.nextElementSibling && url.nextElementSibling.nodeName === 'SMALL') ? url.nextElementSibling : null;
						if (small !== null) {
							elRemove(small);
						}
					}
				},
				source: '<dl>'
						+ '<dt><label for="redactor-link-url">' + Language.get('wcf.editor.link.url') + '</label></dt>'
						+ '<dd><input type="url" id="redactor-link-url" class="long"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-link-url-text">' + Language.get('wcf.editor.link.text') + '</label></dt>'
						+ '<dd><input type="text" id="redactor-link-url-text" class="long"></dd>'
					+ '</dl>'
					+ '<div class="formSubmit">'
						+ '<button id="redactor-modal-button-action" class="buttonPrimary"></button>'
					+ '</div>'
			};
		}
	};
});
