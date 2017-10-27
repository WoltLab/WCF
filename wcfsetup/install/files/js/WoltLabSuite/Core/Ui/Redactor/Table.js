define(['Language', 'Ui/Dialog'], function(Language, UiDialog) {
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
			
			_callback = options.submitCallback;
			
			if (!_boundListener) {
				_boundListener = true;
				
				elById('redactor-modal-button-action').addEventListener(WCF_CLICK_EVENT, this._submit.bind(this));
			}
		},
		
		_submit: function() {
			// check if rows and cols are within the boundaries
			var isValid = true;
			['rows', 'cols'].forEach(function(type) {
				var input = elById('redactor-table-' + type);
				if (input.value < 1 || input.value > 100) {
					isValid = false;
				}
			});
			
			if (!isValid) return;
			
			_callback();
			
			UiDialog.close(this);
		},
		
		_dialogSetup: function() {
			return {
				id: 'redactorDialogTable',
				options: {
					onShow: function () {
						elById('redactor-table-rows').value = 2;
						elById('redactor-table-cols').value = 3;
					},
					title: Language.get('wcf.editor.table.insertTable')
				},
				source: '<dl>'
						+ '<dt><label for="redactor-table-rows">' + Language.get('wcf.editor.table.rows') + '</label></dt>'
						+ '<dd><input type="number" id="redactor-table-rows" class="small" min="1" max="100" value="2"></dd>'
					+ '</dl>'
					+ '<dl>'
						+ '<dt><label for="redactor-table-cols">' + Language.get('wcf.editor.table.cols') + '</label></dt>'
						+ '<dd><input type="number" id="redactor-table-cols" class="small" min="1" max="100" value="3"></dd>'
					+ '</dl>'
					+ '<div class="formSubmit">'
						+ '<button id="redactor-modal-button-action" class="buttonPrimary">' + Language.get('wcf.global.button.insert') + '</button>'
					+ '</div>'
			};
		}
	};
});
