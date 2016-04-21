/**
 * Provides a touch-friendly fullscreen menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Menu/Abstract
 */
define(['Environment', 'EventHandler', 'ObjectMap', 'Dom/Traverse', 'Dom/Util', 'Ui/Screen'], function(Environment, EventHandler, ObjectMap, DomTraverse, DomUtil, UiScreen) {
	"use strict";
	
	var _pageContainer = elById('pageContainer');
	
	/**
	 * @param       {string}        eventIdentifier         event namespace
	 * @param       {string}        elementId               menu element id
	 * @param       {string}        buttonSelector          CSS selector for toggle button
	 * @constructor
	 */
	function UiPageMenuAbstract(eventIdentifier, elementId, buttonSelector) { this.init(eventIdentifier, elementId, buttonSelector); }
	UiPageMenuAbstract.prototype = {
		/**
		 * Initializes a touch-friendly fullscreen menu.
		 * 
		 * @param       {string}        eventIdentifier         event namespace
		 * @param       {string}        elementId               menu element id
		 * @param       {string}        buttonSelector          CSS selector for toggle button
		 */
		init: function(eventIdentifier, elementId, buttonSelector) {
			this._activeList = [];
			this._depth = 0;
			this._enabled = true;
			this._eventIdentifier = eventIdentifier;
			this._items = new ObjectMap();
			this._menu = elById(elementId);
			this._removeActiveList = false;
			
			var callbackOpen = this.open.bind(this);
			var button = elBySel(buttonSelector);
			button.addEventListener(WCF_CLICK_EVENT, callbackOpen);
			
			this._initItems();
			this._initHeader();
			
			EventHandler.add(this._eventIdentifier, 'open', callbackOpen);
			EventHandler.add(this._eventIdentifier, 'close', this.close.bind(this));
			
			var itemList, itemLists = elByClass('menuOverlayItemList', this._menu);
			this._menu.addEventListener('animationend', (function() {
				if (!this._menu.classList.contains('open')) {
					for (var i = 0, length = itemLists.length; i < length; i++) {
						itemList = itemLists[i];
						
						// force the main list to be displayed
						itemList.classList.remove('active');
						itemList.classList.remove('hidden');
					}
				}
			}).bind(this));
			
			this._menu.children[0].addEventListener('transitionend', (function() {
				this._menu.classList.add('allowScroll');
				
				if (this._removeActiveList) {
					this._removeActiveList = false;
					
					var list = this._activeList.pop();
					if (list) {
						list.classList.remove('activeList');
					}
				}
			}).bind(this));
			
			var backdrop = elCreate('div');
			backdrop.className = 'menuOverlayMobileBackdrop';
			backdrop.addEventListener(WCF_CLICK_EVENT, this.close.bind(this));
			
			DomUtil.insertAfter(backdrop, this._menu);
		},
		
		/**
		 * Opens the menu.
		 * 
		 * @param       {Event}         event   event object
		 */
		open: function(event) {
			if (!this._enabled) {
				return;
			}
			
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			this._menu.classList.add('open');
			this._menu.classList.add('allowScroll');
			this._menu.children[0].classList.add('activeList');
			
			UiScreen.scrollDisable();
			
			_pageContainer.classList.add('menuOverlay-' + this._menu.id);
		},
		
		/**
		 * Closes the menu.
		 * 
		 * @param       {(Event|boolean)}       event   event object or boolean true to force close the menu
		 */
		close: function(event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			if (this._menu.classList.contains('open')) {
				this._menu.classList.remove('open');
				
				UiScreen.scrollEnable();
				
				_pageContainer.classList.remove('menuOverlay-' + this._menu.id);
			}
		},
		
		/**
		 * Enables the touch menu.
		 */
		enable: function() {
			this._enabled = true;
		},
		
		/**
		 * Disables the touch menu.
		 */
		disable: function() {
			this._enabled = false;
			
			this.close(true);
		},
		
		/**
		 * Initializes all menu items.
		 * 
		 * @protected
		 */
		_initItems: function() {
			elBySelAll('.menuOverlayItemLink', this._menu, this._initItem.bind(this));
		},
		
		/**
		 * Initializes a single menu item.
		 * 
		 * @param       {Element}       item    menu item
		 * @protected
		 */
		_initItem: function(item) {
			// check if it should contain a 'more' link w/ an external callback
			var parent = item.parentNode;
			var more = elData(parent, 'more');
			if (more) {
				item.addEventListener(WCF_CLICK_EVENT, (function(event) {
					event.preventDefault();
					event.stopPropagation();
					
					EventHandler.fire(this._eventIdentifier, 'more', {
						handler: this,
						identifier: more,
						item: item,
						parent: parent
					});
				}).bind(this));
				
				return;
			}
			
			var itemList = item.nextElementSibling, wrapper;
			if (itemList === null) {
				return;
			}
			
			// handle static items with an icon-type button next to it (acp menu)
			if (itemList.nodeName !== 'OL' && itemList.classList.contains('menuOverlayItemLinkIcon')) {
				// add wrapper
				wrapper = elCreate('span');
				wrapper.className = 'menuOverlayItemWrapper';
				parent.insertBefore(wrapper, item);
				wrapper.appendChild(item);
				
				while (wrapper.nextElementSibling) {
					wrapper.appendChild(wrapper.nextElementSibling);
				}
				
				return;
			}
			
			var isLink = (elAttr(item, 'href') !== '#');
			var parentItemList = parent.parentNode;
			var itemTitle = elData(itemList, 'title');
			
			this._items.set(item, {
				itemList: itemList,
				parentItemList: parentItemList
			});
			
			if (itemTitle === '') {
				itemTitle = DomTraverse.childByClass(item, 'menuOverlayItemTitle').textContent;
				elData(itemList, 'title', itemTitle);
			}
			
			var callbackLink = this._showItemList.bind(this, item);
			if (isLink) {
				wrapper = elCreate('span');
				wrapper.className = 'menuOverlayItemWrapper';
				parent.insertBefore(wrapper, item);
				wrapper.appendChild(item);
				
				var moreLink = elCreate('a');
				elAttr(moreLink, 'href', '#');
				moreLink.className = 'menuOverlayItemLinkIcon' + (item.classList.contains('active') ? ' active' : '');
				moreLink.innerHTML = '<span class="icon icon24 fa-angle-right"></span>';
				moreLink.addEventListener(WCF_CLICK_EVENT, callbackLink);
				wrapper.appendChild(moreLink);
			}
			else {
				item.classList.add('menuOverlayItemLinkMore');
				item.addEventListener(WCF_CLICK_EVENT, callbackLink);
			}
			
			var backLinkItem = elCreate('li');
			backLinkItem.className = 'menuOverlayHeader';
			
			wrapper = elCreate('span');
			wrapper.className = 'menuOverlayItemWrapper';
			
			var backLink = elCreate('a');
			elAttr(backLink, 'href', '#');
			backLink.className = 'menuOverlayItemLink menuOverlayBackLink';
			backLink.textContent = elData(parentItemList, 'title');
			backLink.addEventListener(WCF_CLICK_EVENT, this._hideItemList.bind(this, item));
			
			var closeLink = elCreate('a');
			elAttr(closeLink, 'href', '#');
			closeLink.className = 'menuOverlayItemLinkIcon';
			closeLink.innerHTML = '<span class="icon icon24 fa-times"></span>';
			closeLink.addEventListener(WCF_CLICK_EVENT, this.close.bind(this));
			
			wrapper.appendChild(backLink);
			wrapper.appendChild(closeLink);
			backLinkItem.appendChild(wrapper);
			
			itemList.insertBefore(backLinkItem, itemList.firstElementChild);
			
			if (!backLinkItem.nextElementSibling.classList.contains('menuOverlayTitle')) {
				var titleItem = elCreate('li');
				titleItem.className = 'menuOverlayTitle';
				var title = elCreate('span');
				title.textContent = itemTitle;
				titleItem.appendChild(title);
				
				itemList.insertBefore(titleItem, backLinkItem.nextElementSibling);
			}
		},
		
		/**
		 * Renders the menu item list header.
		 * 
		 * @protected
		 */
		_initHeader: function() {
			var listItem = elCreate('li');
			listItem.className = 'menuOverlayHeader';
			
			var wrapper = elCreate('span');
			wrapper.className = 'menuOverlayItemWrapper';
			listItem.appendChild(wrapper);
			
			var logoWrapper = elCreate('span');
			logoWrapper.className = 'menuOverlayLogoWrapper';
			wrapper.appendChild(logoWrapper);
			
			var logo = elCreate('span');
			logo.className = 'menuOverlayLogo';
			logo.style.setProperty('background-image', 'url("' + elData(this._menu, 'page-logo') + '")', '');
			logoWrapper.appendChild(logo);
			
			var closeLink = elCreate('a');
			elAttr(closeLink, 'href', '#');
			closeLink.className = 'menuOverlayItemLinkIcon';
			closeLink.innerHTML = '<span class="icon icon24 fa-times"></span>';
			closeLink.addEventListener(WCF_CLICK_EVENT, this.close.bind(this));
			wrapper.appendChild(closeLink);
			
			var list = DomTraverse.childByClass(this._menu, 'menuOverlayItemList');
			list.insertBefore(listItem, list.firstElementChild);
		},
		
		/**
		 * Hides an item list, return to the parent item list.
		 * 
		 * @param       {Element}       item    menu item
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_hideItemList: function(item, event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			this._menu.classList.remove('allowScroll');
			this._removeActiveList = true;
			
			var data = this._items.get(item);
			data.parentItemList.classList.remove('hidden');
			
			this._updateDepth(false);
		},
		
		/**
		 * Shows the child item list.
		 * 
		 * @param       {Element}       item    menu item
		 * @param event
		 * @private
		 */
		_showItemList: function(item, event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			var data = this._items.get(item);
			
			var load = elData(data.itemList, 'load');
			if (load) {
				if (!elDataBool(item, 'loaded')) {
					var icon = event.currentTarget.firstElementChild;
					if (icon.classList.contains('fa-angle-right')) {
						icon.classList.remove('fa-angle-right');
						icon.classList.add('fa-spinner');
					}
					
					EventHandler.fire(this._eventIdentifier, 'load_' + load);
					
					return;
				}
			}
			
			this._menu.classList.remove('allowScroll');
			
			data.itemList.classList.add('activeList');
			data.parentItemList.classList.add('hidden');
			
			this._activeList.push(data.itemList);
			
			this._updateDepth(true);
		},
		
		_updateDepth: function(increase) {
			this._depth += (increase) ? 1 : -1;
			
			this._menu.children[0].style.setProperty('transform', 'translateX(' + (this._depth * -100) + '%)', '')
		}
	};
	
	return UiPageMenuAbstract;
});
