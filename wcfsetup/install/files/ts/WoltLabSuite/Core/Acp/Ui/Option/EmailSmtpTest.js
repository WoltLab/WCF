define(['Ajax', 'Core', 'Language'], function(Ajax, Core, Language) {
	"use strict";
	
	var _buttonRunTest = null;
	var _container = null;
	
	return {
		init: function () {
			var smtpCheckbox = null;
			var methods = elBySelAll('input[name="values[mail_send_method]"]', undefined, (function (radioCheckbox) {
				radioCheckbox.addEventListener('change', this._onChange.bind(this));
				
				if (radioCheckbox.value === 'smtp') smtpCheckbox = radioCheckbox;
			}).bind(this));
			
			// This configuration part is unavailable when running in enterprise mode.
			if (methods.length === 0) {
				return;
			}
			
			Core.triggerEvent(smtpCheckbox, 'change');
		},
		
		_onChange: function (event) {
			var checkbox = event.currentTarget;
			
			if (checkbox.value === 'smtp' && checkbox.checked) {
				if (_container === null) this._initUi(checkbox);
				
				elShow(_container);
			}
			else if (_container !== null) {
				elHide(_container);
			}
		},
		
		_initUi: function (checkbox) {
			var html = '<dt>' + Language.get('wcf.acp.email.smtp.test') + '</dt>';
			html += '<dd>';
			html += '<a href="#" class="button">' + Language.get('wcf.acp.email.smtp.test.run') + '</a>';
			html += '<small>' + Language.get('wcf.acp.email.smtp.test.description') + '</small>';
			html += '</dd>';
			
			_container = elCreate('dl');
			_container.innerHTML = html;
			
			_buttonRunTest = elBySel('a', _container);
			_buttonRunTest.addEventListener(WCF_CLICK_EVENT, this._onClick.bind(this));
			
			var insertAfter = checkbox.closest('dl');
			insertAfter.parentNode.insertBefore(_container, insertAfter.nextSibling);
		},
		
		_onClick: function (event) {
			event.preventDefault();
			
			_buttonRunTest.disabled = true;
			_buttonRunTest.innerHTML = '<span class="icon icon16 fa-spinner"></span> ' + Language.get('wcf.global.loading');
			
			elInnerError(_buttonRunTest, false);
			
			window.setTimeout((function () {
				var startTls = elBySel('input[name="values[mail_smtp_starttls]"]:checked');
				
				Ajax.api(this, {
					parameters: {
						host: elById('mail_smtp_host').value,
						port: elById('mail_smtp_port').value,
						startTls: (startTls) ? startTls.value : '',
						user: elById('mail_smtp_user').value,
						password: elById('mail_smtp_password').value
					}
				});
			}).bind(this), 100);
		},
		
		_ajaxSuccess: function (data) {
			var result = data.returnValues.validationResult;
			if (result === '') {
				this._resetButton(true);
			}
			else {
				this._resetButton(false, result);
			}
		},
		
		_ajaxFailure: function (data) {
			var result = '';
			if (data && data.returnValues && data.returnValues.fieldName) {
				result = Language.get('wcf.acp.email.smtp.test.error.empty.' + data.returnValues.fieldName);
			}
			
			this._resetButton(false, result);
			
			return (result === '');
		},
		
		_resetButton: function (success, errorMessage) {
			_buttonRunTest.disabled = false;
			
			if (success) _buttonRunTest.innerHTML = '<span class="icon icon16 fa-check green"></span> ' + Language.get('wcf.acp.email.smtp.test.run.success');
			else _buttonRunTest.innerHTML = Language.get('wcf.acp.email.smtp.test.run');
			
			if (errorMessage) elInnerError(_buttonRunTest, errorMessage);
		},
		
		_ajaxSetup: function () {
			return {
				data: {
					actionName: 'emailSmtpTest',
					className: 'wcf\\data\\option\\OptionAction'
				},
				silent: true
			};
		}
	};
});
