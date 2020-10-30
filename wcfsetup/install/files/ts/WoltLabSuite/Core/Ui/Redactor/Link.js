define(['Core', 'EventKey', 'Language', 'Ui/Dialog'], function(Core, EventKey, Language, UiDialog) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			showDialog: function() {},
			_submit: function() {},
			_dialogSetup: function() {}
		};
		return Fake;
	}
	
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
				
				submitButton.addEventListener('click', this._submit.bind(this));
			}
		},
		
		_submit: function() {
			if (_callback()) {
				UiDialog.close(this);
			}
			else {
				var url = elById('redactor-link-url');
				elInnerError(url, Language.get((url.value.trim() === '' ? 'wcf.global.form.error.empty' : 'wcf.editor.link.error.invalid')));
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
					},
					onSetup: function (content) {
						var submitButton = elBySel('.formSubmit > .buttonPrimary', content);
						
						if (submitButton !== null) {
							elBySelAll('input[type="url"], input[type="text"]', content, function (input) {
								input.addEventListener('keyup', function (event) {
									if (EventKey.Enter(event)) {
										Core.triggerEvent(submitButton, 'click');
									}
								});
							});
						}
					},
					onShow: function () {
						elById('redactor-link-url').focus();
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
