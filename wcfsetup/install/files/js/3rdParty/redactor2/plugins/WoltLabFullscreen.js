$.Redactor.prototype.WoltLabFullscreen = function() {
	"use strict";
	
	var _active = false;
	var _button;
	
	return {
		init: function() {
			var button = this.button.add('woltlabFullscreen', '');
			this.button.addCallback(button, this.WoltLabFullscreen._toggle.bind(this));
			
			_button = button[0];
			elHide(_button.parentNode);
			
			require(['Ui/Screen'], (function (UiScreen) {
				UiScreen.on('screen-sm-up', {
					match: function () {
						elShow(_button.parentNode);
					},
					unmatch: (function () {
						elHide(_button.parentNode);
						
						if (_active) {
							this.WoltLabFullscreen._toggle();
						}
					}).bind(this),
					setup: function () {
						elShow(_button.parentNode);
					}
				});
			}).bind(this));
		},
		
		_toggle: function () {
			_button.children[0].classList.toggle('fa-compress');
			_button.children[0].classList.toggle('fa-expand');
			
			if (this.core.box()[0].classList.toggle('redactorBoxFullscreen')) {
				WCF.System.DisableScrolling.disable();
				_active = true;
			}
			else {
				WCF.System.DisableScrolling.enable();
				_active = false;
			}
		}
	};
};
