$.Redactor.prototype.WoltLabCode = function() {
	"use strict";
	
	var _button;
	
	return {
		init: function() {
			_button = this.button.add('woltlabFullscreen', '');
			this.button.addCallback(_button, this.WoltLabCode._toggle.bind(this));
		},
		
		_toggle: function () {
			_button[0].children[0].classList.toggle('fa-compress');
			_button[0].children[0].classList.toggle('fa-expand');
			
			if (this.core.box()[0].classList.toggle('redactorBoxFullscreen')) {
				WCF.System.DisableScrolling.disable();
				this.core.editor()[0].style.setProperty('height', 'calc(100% - ' + ~~this.core.toolbar()[0].clientHeight + 'px)', '');
			}
			else {
				WCF.System.DisableScrolling.enable();
				this.core.editor()[0].style.removeProperty('height');
			}
		}
	};
};
