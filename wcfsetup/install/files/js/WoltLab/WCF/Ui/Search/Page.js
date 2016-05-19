define(['Dom/Util', './Input'], function(DomUtil, UiSearchInput) {
	"use strict";
	
	var _dropdownMenu = null;
	
	return {
		init: function () {
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
		}
	};
});
