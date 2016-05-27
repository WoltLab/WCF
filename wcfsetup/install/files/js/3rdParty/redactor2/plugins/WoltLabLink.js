$.Redactor.prototype.WoltLabLink = function() {
	"use strict";
	
	var _dialogApi = null;
	
	return {
		init: function() {
			this.link.show = this.WoltLabLink.show.bind(this);
			
			require(['WoltLab/WCF/Ui/Redactor/Link'], function(UiRedactorLink) {
				_dialogApi = UiRedactorLink;
			});
		},
		
		show: function(e) {
			// if call from clickable element
			if (typeof e !== 'undefined' && e.preventDefault)
			{
				e.preventDefault();
			}
			
			// close tooltip
			this.observe.closeAllTooltip();
			
			// is link
			var $el = this.link.is();
			
			// WoltLab START
			// this.link.buildModal($el);
			_dialogApi.showDialog({
				insert: ($el === false),
				submitCallback: (function() {
					// build link
					var link = this.link.buildLinkFromModal();
					if (link === false) {
						return false;
					}
					
					this.selection.restore();
					
					// insert or update
					this.link.insert(link, true);
					
					return true;
				}).bind(this)
			});
			// WoltLab END
			
			// build link
			var link = this.link.buildLinkFromElement($el);
			
			// if link cut & paste inside editor browser added self host to a link
			link.url = this.link.removeSelfHostFromUrl(link.url);
			
			// set modal values
			this.link.setModalValues(link);
			
			// WoltLab START
			// this.modal.show();
			// WoltLab END
			
			// focus
			if (this.detect.isDesktop())
			{
				$('#redactor-link-url').focus();
			}
		}
	};
};
