$.Redactor.prototype.WoltLabModal = function() {
	"use strict";
	
	var _modalWrapper = null;
	var _title = '';
	var _uiDialog = null;
	
	// we're using a separate object here to prevent exposing these methods
	// directly because they would end up in the Redactor object but neither
	// need to go there nor will it work
	var _methods = {
		close: function() {
			_uiDialog.close(this);
		},
		
		load: function(templateName, title) {
			_modalWrapper.innerHTML = this.modal.getTemplate(templateName);
			_title = title;
		},
		
		setTitle: function(title) {
			_uiDialog.setTitle(this, title);
		},
		
		show: function() {
			_uiDialog.open(this);
			_uiDialog.setTitle(this, _title);
		}
	};
	
	return {
		init: function() {
			_modalWrapper = elCreate('div');
			_modalWrapper.className = 'redactorModalWrapper';
			_modalWrapper.id = 'redactorOverlay-' + this.uuid;
			elHide(_modalWrapper);
			document.body.appendChild(_modalWrapper);
			
			this.$modalBody = $(_modalWrapper);
			
			require(['Ui/Dialog'], (function (UiDialog) {
				_uiDialog = UiDialog;
				
				for (var method in _methods) {
					if (_methods.hasOwnProperty(method)) {
						this.modal[method] = _methods[method].bind(this);
					}
				}
			}).bind(this));
			
			this._dialogSetup = function() {
				return {
					id: 'redactorOverlay-' + this.uuid,
					options: {
						onClose: function (id) {
							// The default redactor implementation discards the entire dialog on
							// close, but this is not an option for us because we don't know how
							// long the close animation runs (or if there is one at all).
							//
							// Redactor relies on non-unique ids in templates; We simply discard
							// all ids on close to prevent any conflicts with overlays created by
							// a different Redactor instance.
							
							var data = _uiDialog.getDialog(id);
							elBySelAll('[id]', data.content, function(element) {
								element.removeAttribute('id');
							});
						}
					}
				};
			};
		}
	};
};