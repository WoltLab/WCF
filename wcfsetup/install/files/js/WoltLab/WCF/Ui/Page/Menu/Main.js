/**
 * Provides the touch-friendly fullscreen main menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Menu/Main
 */
define(['Core', 'Dom/Traverse', './Abstract'], function(Core, DomTraverse, UiPageMenuAbstract) {
	"use strict";
	
	var _container = elById('pageMainMenuMobilePageOptionsContainer');
	var _hasItems = null;
	var _list = DomTraverse.childByClass(_container, 'menuOverlayItemList');
	var _navigationList = elBySel('.jsPageNavigationIcons');
	var _spacer = _container.nextElementSibling;
	
	/**
	 * @constructor
	 */
	function UiPageMenuMain() { this.init(); }
	Core.inherit(UiPageMenuMain, UiPageMenuAbstract, {
		/**
		 * Initializes the touch-friendly fullscreen main menu.
		 */
		init: function() {
			UiPageMenuMain._super.prototype.init.call(
				this,
				'com.woltlab.wcf.MainMenuMobile',
				'pageMainMenuMobile',
				'#pageHeader .mainMenu'
			);
			
			// remove placeholder item
			elRemove(DomTraverse.childByClass(_list, 'jsMenuOverlayItemPlaceholder'));
		},
		
		open: function (event) {
			if (!UiPageMenuMain._super.prototype.open.call(this, event)) {
				return false;
			}
			
			_hasItems = _navigationList.childElementCount > 0;
			
			if (_hasItems) {
				var item, link;
				while (_navigationList.childElementCount) {
					item = _navigationList.children[0];
					
					item.classList.add('menuOverlayItem');
					
					link = item.children[0];
					link.classList.add('menuOverlayItemLink');
					link.classList.add('box24');
					
					link.children[1].classList.remove('invisible');
					link.children[1].classList.add('menuOverlayItemTitle');
					
					_list.appendChild(item);
				}
				
				elShow(_container);
				elShow(_spacer);
			}
			else {
				elHide(_container);
				elHide(_spacer);
			}
			
			return true;
		},
		
		close: function(event) {
			if (!UiPageMenuMain._super.prototype.close.call(this, event)) {
				return false;
			}
			
			if (_hasItems) {
				elHide(_container);
				elHide(_spacer);
				
				var item, link, title = DomTraverse.childByClass(_list, 'menuOverlayTitle');
				while (item = title.nextElementSibling) {
					item.classList.remove('menuOverlayItem');
					
					link = item.children[0];
					link.classList.remove('menuOverlayItemLink');
					link.classList.remove('box24');
					
					link.children[1].classList.add('invisible');
					link.children[1].classList.remove('menuOverlayItemTitle');
					
					_navigationList.appendChild(item);
				}
			}
			
			return true;
		}
	});
	
	return UiPageMenuMain;
});
