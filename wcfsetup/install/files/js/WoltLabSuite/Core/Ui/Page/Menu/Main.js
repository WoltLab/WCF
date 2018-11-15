/**
 * Provides the touch-friendly fullscreen main menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Menu/Main
 */
define(['Core', 'Language', 'Dom/Traverse', './Abstract'], function(Core, Language, DomTraverse, UiPageMenuAbstract) {
	"use strict";
	
	var _container = null, _hasItems = null, _list = null, _navigationList = null, _spacer = null;
	
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
			
			_container = elById('pageMainMenuMobilePageOptionsContainer');
			if (_container !== null) {
				_list = DomTraverse.childByClass(_container, 'menuOverlayItemList');
				_navigationList = elBySel('.jsPageNavigationIcons');
				//_spacer = _container.nextElementSibling;
				
				// remove placeholder item
				elRemove(DomTraverse.childByClass(_list, 'jsMenuOverlayItemPlaceholder'));
				
				_list.addEventListener('click', (function (event) {
					if (event.target !== _list && DomTraverse.parentByClass(event.target, 'menuOverlayItem', _list) !== null) {
						this.close();
						event.stopPropagation();
					}
				}).bind(this));
			}
			
			elAttr(this._button, 'aria-label', Language.get('wcf.menu.page'));
			elAttr(this._button, 'role', 'button');
		},
		
		open: function (event) {
			if (!UiPageMenuMain._super.prototype.open.call(this, event)) {
				return false;
			}
			
			if (_container === null) {
				return true;
			}
			
			_hasItems = _navigationList && _navigationList.childElementCount > 0;
			
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
				//elShow(_spacer);
			}
			else {
				elHide(_container);
				//elHide(_spacer);
			}
			
			return true;
		},
		
		close: function(event) {
			if (!UiPageMenuMain._super.prototype.close.call(this, event)) {
				return false;
			}
			
			if (_hasItems) {
				elHide(_container);
				//elHide(_spacer);
				
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
