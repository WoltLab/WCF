/**
 * Provides a touch-friendly fullscreen menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Menu/Abstract
 */
define(['Core', 'Environment', 'EventHandler', 'Language', 'ObjectMap', 'Dom/Traverse', 'Dom/Util', 'Ui/Screen'], function(Core, Environment, EventHandler, Language, ObjectMap, DomTraverse, DomUtil, UiScreen) {
	"use strict";
	
	var _pageContainer = elById('pageContainer');

	/**
	 * Which edge of the menu is touched? Empty string
	 * if no menu is currently touched.
	 * 
	 * One 'left', 'right' or ''.
	 */
	var _androidTouching = '';
	
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
			if (elData(document.body, 'template') === 'packageInstallationSetup') {
				// work-around for WCFSetup on mobile
				return;
			}
			
			this._activeList = [];
			this._depth = 0;
			this._enabled = true;
			this._eventIdentifier = eventIdentifier;
			this._items = new ObjectMap();
			this._menu = elById(elementId);
			this._removeActiveList = false;
			
			var callbackOpen = this.open.bind(this);
			this._button = elBySel(buttonSelector);
			this._button.addEventListener(WCF_CLICK_EVENT, callbackOpen);
			
			this._initItems();
			this._initHeader();
			
			EventHandler.add(this._eventIdentifier, 'open', callbackOpen);
			EventHandler.add(this._eventIdentifier, 'close', this.close.bind(this));
			EventHandler.add(this._eventIdentifier, 'updateButtonState', this._updateButtonState.bind(this));
			
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
			
			this._updateButtonState();
			
			if (Environment.platform() === 'android') {
				this._initializeAndroid();
			}
		},
		
		/**
		 * Opens the menu.
		 * 
		 * @param       {Event}         event   event object
		 * @return      {boolean}       true if menu has been opened
		 */
		open: function(event) {
			if (!this._enabled) {
				return false;
			}
			
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			this._menu.classList.add('open');
			this._menu.classList.add('allowScroll');
			this._menu.children[0].classList.add('activeList');
			
			UiScreen.scrollDisable();
			
			_pageContainer.classList.add('menuOverlay-' + this._menu.id);
			
			UiScreen.pageOverlayOpen();
			
			return true;
		},
		
		/**
		 * Closes the menu.
		 * 
		 * @param       {(Event|boolean)}       event   event object or boolean true to force close the menu
		 * @return      {boolean}               true if menu was open
		 */
		close: function(event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			if (this._menu.classList.contains('open')) {
				this._menu.classList.remove('open');
				
				UiScreen.scrollEnable();
				UiScreen.pageOverlayClose();
				
				_pageContainer.classList.remove('menuOverlay-' + this._menu.id);
				
				return true;
			}
			
			return false;
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
		 * Initializes the Android Touch Menu.
		 */
		_initializeAndroid: function() {
			var appearsAt, backdrop, touchStart;
			/** @const */ var AT_EDGE = 20;
			/** @const */ var MOVED_HORIZONTALLY = 5;
			/** @const */ var MOVED_VERTICALLY = 20;
			
			// specify on which side of the page the menu appears
			switch (this._menu.id) {
				case 'pageUserMenuMobile':
					appearsAt = 'right';
				break;
				case 'pageMainMenuMobile':
					appearsAt = 'left';
				break;
				default:
					return;
			}
			
			backdrop = this._menu.nextElementSibling;
			
			// horizontal position of the touch start
			touchStart = null;
			
			document.addEventListener('touchstart', (function(event) {
				var touches, isOpen, isLeftEdge, isRightEdge;
				touches = event.touches;
				
				isOpen = this._menu.classList.contains('open');
				
				// check whether we touch the edges of the menu
				if (appearsAt === 'left') {
					isLeftEdge = !isOpen && (touches[0].clientX < AT_EDGE);
					isRightEdge = isOpen && (Math.abs(this._menu.offsetWidth - touches[0].clientX) < AT_EDGE);
				}
				else if (appearsAt === 'right') {
					isLeftEdge = isOpen && (Math.abs(document.body.clientWidth - this._menu.offsetWidth - touches[0].clientX) < AT_EDGE);
					isRightEdge = !isOpen && ((document.body.clientWidth - touches[0].clientX) < AT_EDGE);
				}
				
				// abort if more than one touch
				if (touches.length > 1) {
					if (_androidTouching) {
						Core.triggerEvent(document, 'touchend');
					}
					return;
				}
				
				// break if a touch is in progress
				if (_androidTouching) return;
				// break if no edge has been touched
				if (!isLeftEdge && !isRightEdge) return;
				// break if a different menu is open
				if (UiScreen.pageOverlayIsActive()) {
					var found = false;
					for (var i = 0; i < _pageContainer.classList.length; i++) {
						if (_pageContainer.classList[i] === 'menuOverlay-' + this._menu.id) {
							found = true;
						}
					}
					if (!found) return;
				}
				// break if redactor is in use
				if (document.documentElement.classList.contains('redactorActive')) return;
				
				touchStart = {
					x: touches[0].clientX,
					y: touches[0].clientY
				};
				
				if (isLeftEdge) _androidTouching = 'left';
				if (isRightEdge) _androidTouching = 'right';
			}).bind(this));
			
			document.addEventListener('touchend', (function(event) {
				// break if we did not start a touch
				if (!_androidTouching || touchStart === null) return;
				
				// break if the menu did not even start opening
				if (!this._menu.classList.contains('open')) {
					// reset
					touchStart = null;
					_androidTouching = '';
					return;
				}
				
				// last known position of the finger
				var position;
				if (event) {
					position = event.changedTouches[0].clientX;
				}
				else {
					position = touchStart.x;
				}
				
				// clean up touch styles
				this._menu.classList.add('androidMenuTouchEnd');
				this._menu.style.removeProperty('transform');
				backdrop.style.removeProperty(appearsAt);
				this._menu.addEventListener('transitionend', (function() {
					this._menu.classList.remove('androidMenuTouchEnd');
				}).bind(this), { once: true });
				
				// check whether the user moved the finger far enough
				if (appearsAt === 'left') {
					if (_androidTouching === 'left' && position < (touchStart.x + 100)) this.close();
					if (_androidTouching === 'right' && position < (touchStart.x - 100)) this.close();
				}
				else if (appearsAt === 'right') {
					if (_androidTouching === 'left' && position > (touchStart.x + 100)) this.close();
					if (_androidTouching === 'right' && position > (touchStart.x - 100)) this.close();
				}
				
				// reset
				touchStart = null;
				_androidTouching = '';
			}).bind(this));
			
			document.addEventListener('touchmove', (function(event) {
				// break if we did not start a touch
				if (!_androidTouching || touchStart === null) return;
				
				var touches = event.touches;
				
				// check whether the user started moving in the correct direction
				// this avoids false positives, in case the user just wanted to tap
				var movedFromEdge = false, movedVertically = false;
				if (_androidTouching === 'left') movedFromEdge = touches[0].clientX > (touchStart.x + MOVED_HORIZONTALLY);
				if (_androidTouching === 'right') movedFromEdge = touches[0].clientX < (touchStart.x - MOVED_HORIZONTALLY);
				movedVertically = Math.abs(touches[0].clientY - touchStart.y) > MOVED_VERTICALLY;
				
				var isOpen = this._menu.classList.contains('open');
				
				if (!isOpen && movedFromEdge && !movedVertically) {
					// the menu is not yet open, but the user moved into the right direction
					this.open();
					isOpen = true;
				}
				
				if (isOpen) {
					// update CSS to the new finger position
					var position = touches[0].clientX;
					if (appearsAt === 'right') position = document.body.clientWidth - position;
					if (position > this._menu.offsetWidth) position = this._menu.offsetWidth;
					if (position < 0) position = 0;
					this._menu.style.setProperty('transform', 'translateX(' + (appearsAt === 'left' ? 1 : -1) * (position - this._menu.offsetWidth) + 'px)');
					backdrop.style.setProperty(appearsAt, Math.min(this._menu.offsetWidth, position) + 'px');
				}
			}).bind(this));
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
			
			var offset = this._depth * -100;
			if (Language.get('wcf.global.pageDirection') === 'rtl') {
				// reverse logic for RTL
				offset *= -1;
			}
			
			this._menu.children[0].style.setProperty('transform', 'translateX(' + offset + '%)', '');
		},
		
		_updateButtonState: function() {
			var hasNewContent = false;
			var itemList = elBySel('.menuOverlayItemList', this._menu);
			elBySelAll('.badgeUpdate', this._menu, function (badge) {
				if (~~badge.textContent > 0 && badge.closest('.menuOverlayItemList') === itemList) {
					hasNewContent = true;
				}
			});
			
			this._button.classList[(hasNewContent ? 'add' : 'remove')]('pageMenuMobileButtonHasContent');
		}
	};
	
	return UiPageMenuAbstract;
});
