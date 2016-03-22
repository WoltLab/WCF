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
		},
		
		_hideAll: function() {
			var hideAll = this.dropdown.hideAll;
			this.dropdown.hideAll = (function(e, key) {
				hideAll.call(this, e, key);
				
				$('.redactor-dropdown-' + this.uuid).stop(true, true).hide();
			}).bind(this);
		},
		
		_show: function() {
			var show = this.dropdown.show;
			this.dropdown.show = (function(e, key) {
				var $button = this.button.get(key);
				var $dropdown = $button.data('dropdown');
				
				if (!elDataBool($dropdown[0], 'woltlab')) {
					var list = elCreate('ul');
					list.className = 'dropdownMenu';
					
					while ($dropdown[0].childElementCount) {
						list.appendChild($dropdown[0].children[0]);
					}
					
					$dropdown[0].appendChild(list);
					
					elData($dropdown[0], 'woltlab', true);
				}
				
				var isActive = $button.hasClass('dropact');
				
				show.call(this, e, key);
				
				if (!isActive) {
					$dropdown.stop(true).show();
				}
			}).bind(this);
			
		}
	};
};
