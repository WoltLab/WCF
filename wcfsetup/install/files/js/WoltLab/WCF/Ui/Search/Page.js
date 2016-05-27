define(['Core', 'Dom/Util', 'Ui/SimpleDropdown', './Input'], function(Core, DomUtil, UiSimpleDropdown, UiSearchInput) {
	"use strict";
	
	return {
		init: function (objectType) {
			var searchInput = elById('pageHeaderSearchInput');
			
			new UiSearchInput(searchInput, {
				ajax: {
					className: 'wcf\\data\\search\\keyword\\SearchKeywordAction'
				},
				callbackDropdownInit: function(dropdownMenu) {
					dropdownMenu.classList.add('dropdownMenuPageSearch');
					
					elData(dropdownMenu, 'dropdown-alignment-horizontal', 'right');
					
					var minWidth = searchInput.clientWidth;
					dropdownMenu.style.setProperty('min-width', minWidth + 'px', '');
					
					// calculate offset to ignore the width caused by the submit button
					var parent = searchInput.parentNode;
					var offsetRight = (DomUtil.offset(parent).left + parent.clientWidth) - (DomUtil.offset(searchInput).left + minWidth);
					var offsetTop = DomUtil.styleAsInt(window.getComputedStyle(parent), 'padding-bottom');
					dropdownMenu.style.setProperty('transform', 'translateX(-' + Math.ceil(offsetRight) + 'px) translateY(-' + offsetTop + 'px)', '');
				}
			});
			
			var dropdownMenu = UiSimpleDropdown.getDropdownMenu(DomUtil.identify(elBySel('.pageHeaderSearchType')));
			var callback = this._click.bind(this);
			elBySelAll('a[data-object-type]', dropdownMenu, function(link) {
				link.addEventListener(WCF_CLICK_EVENT, callback);
			});
			
			// trigger click on init
			var link = elBySel('a[data-object-type="' + objectType + '"]', dropdownMenu);
			Core.triggerEvent(link, WCF_CLICK_EVENT);
		},
		
		_click: function(event) {
			event.preventDefault();
			
			var objectType = elData(event.currentTarget, 'object-type');
			
			var container = elById('pageHeaderSearchParameters');
			container.innerHTML = '';
			
			var parameters = elData(event.currentTarget, 'parameters');
			if (parameters) {
				parameters = JSON.parse(parameters);
			}
			else {
				parameters = {};
			}
			
			if (objectType) parameters['types[]'] = objectType;
			
			for (var key in parameters) {
				if (parameters.hasOwnProperty(key)) {
					var input = elCreate('input');
					input.type = 'hidden';
					input.name = key;
					input.value = parameters[key];
					container.appendChild(input);
				}
			}
			
			// update label
			var button = elBySel('.pageHeaderSearchType > .button', elById('pageHeaderSearchInputContainer'));
			button.textContent = event.currentTarget.textContent;
		}
	};
});
