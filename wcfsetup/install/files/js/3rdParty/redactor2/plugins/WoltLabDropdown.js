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
			this.dropdown.build = (function(name, $dropdown, dropdownObject) {
				dropdownObject = this.dropdown.buildFormatting(name, dropdownObject);
				
				var btnObject, fragment = document.createDocumentFragment();
				for (var btnName in dropdownObject) {
					if (dropdownObject.hasOwnProperty(btnName)) {
						btnObject = dropdownObject[btnName];
						
						var item = this.dropdown.buildItem(btnName, btnObject);
						
						this.observe.addDropdown($(item), btnName, btnObject);
						fragment.appendChild(item);
					}
				}
				
				var hasItems = false;
				for (var i = 0, length = fragment.childNodes.length; i < length; i++) {
					if (fragment.childNodes[i].nodeType === Node.ELEMENT_NODE) {
						hasItems = true;
						break;
					}
				}
				
				if (hasItems) {
					$dropdown[0].rel = name;
					$dropdown[0].appendChild(fragment);
				}
			}).bind(this);
			
			this.dropdown.buildItem = (function(btnName, btnObject) {
				var itemContainer = elCreate('li');
				if (typeof btnObject.classname !== 'undefined') {
					itemContainer.classList.add(btnObject.classname);
				}
				
				if (btnName.toLowerCase().indexOf('divider') === 0) {
					itemContainer.classList.add('redactor-dropdown-divider');
					
					return itemContainer;
				}
				
				itemContainer.innerHTML = '<a href="#" class="redactor-dropdown-' + btnName + '" role="button"><span>' + btnObject.title + '</span></a>';
				itemContainer.children[0].addEventListener('mousedown', (function(event) {
					event.preventDefault();
					
					this.dropdown.buildClick(event, btnName, btnObject);
				}).bind(this));
				
				return itemContainer;
			}).bind(this);
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
