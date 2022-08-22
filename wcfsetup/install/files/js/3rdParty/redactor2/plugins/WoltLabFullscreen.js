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
			const icon = _button.querySelector("fa-icon");
			if (icon.name === "expand") {
				icon.setIcon("compress");
			} else {
				icon.setIcon("expand");
			}
			
			var anchorFixedHeader = elClosest(this.core.box()[0], '.anchorFixedHeader');
			if (anchorFixedHeader) anchorFixedHeader.classList.toggle('disableAnchorFixedHeader');
			
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
