$.Redactor.prototype.WoltLabDropdown = function() {
	"use strict";
	
	return {
		init: function() {
			// prevent overflow: hidden on body while hovering dropdowns
			this.utils.disableBodyScroll = function() {};
			this.utils.enableBodyScroll = function() {};
			
			// disable slideUp effect for dropdowns on close
			this.WoltLabDropdown._hideAll();
			
			// disable slideDown effect for dropdowns on open
			// enforce dropdownMenu-like DOM
			this.WoltLabDropdown._show();
			
			// the original implementation didn't perform that well (especially with multiple
			// instance being launched at start) and suffered from too many live DOM manipulations
			// Integrated into Redactor itself in WoltLab Suite 5.2:
			//  * this.dropdown.build
			//  * this.dropdown.buildItem
		},
		
		_hideAll: function() {
			var hideAll = this.dropdown.hideAll;
			this.dropdown.hideAll = (function(e, key) {
				hideAll.call(this, e, key);
				
				$('.redactor-dropdown-' + this.uuid).stop(true, true).hide();
			}).bind(this);
		},
		
		_show: function() {
			return;
			var show = this.dropdown.show;
			this.dropdown.show = (function(e, key) {
				var $button = this.button.get(key);
				var $dropdown = $button.data('dropdown');
				
				var isActive = $button.hasClass('dropact');
				
				show.call(this, e, key);
				
				if (!isActive) {
					$dropdown.stop(true).show();
				}
			}).bind(this);
			
		}
	};
};
