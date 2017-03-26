/**
 * Common interface for tab menu access.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/TabMenu
 */
define(['Dictionary', 'EventHandler', 'Dom/ChangeListener', 'Dom/Util', 'Ui/CloseOverlay', 'Ui/Screen', './TabMenu/Simple'], function(Dictionary, EventHandler, DomChangeListener, DomUtil, UiCloseOverlay, UiScreen, SimpleTabMenu) {
	"use strict";
	
	var _activeList = null;
	var _enableTabScroll = false;
	var _tabMenus = new Dictionary();
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/TabMenu
	 */
	return {
		/**
		 * Sets up tab menus and binds listeners.
		 */
		setup: function() {
			this._init();
			this._selectErroneousTabs();
			
			DomChangeListener.add('WoltLabSuite/Core/Ui/TabMenu', this._init.bind(this));
			UiCloseOverlay.add('WoltLabSuite/Core/Ui/TabMenu', function() {
				if (_activeList) {
					_activeList.classList.remove('active');
					
					_activeList = null;
				}
			});
			
			//noinspection JSUnresolvedVariable
			UiScreen.on('screen-sm-down', {
				enable: this._scrollEnable.bind(this, false),
				disable: this._scrollDisable.bind(this),
				setup: this._scrollEnable.bind(this, true)
			});
			
			window.addEventListener('hashchange', function () {
				var hash = window.location.hash.replace(/^#/, '');
				var element = (hash) ? elById(hash) : null;
				if (element !== null && element.classList.contains('tabMenuContent')) {
					_tabMenus.forEach(function (tabMenu) {
						if (tabMenu.hasTab(hash)) {
							tabMenu.select(hash);
						}
					});
				}
			});
			
			if (window.location.hash.match(/^#(.*)$/)) {
				var hash = RegExp.$1;
				window.setTimeout(function () {
					// check if page was initially scrolled using a tab id
					var tabMenuContent = elById(hash);
					if (tabMenuContent && tabMenuContent.classList.contains('tabMenuContent')) {
						var scrollY = (window.scrollY || window.pageYOffset);
						if (scrollY > 0) {
							var parent = tabMenuContent.parentNode;
							var offsetTop = parent.offsetTop - 50;
							if (offsetTop < 0) offsetTop = 0;
							
							if (scrollY > offsetTop) {
								var y = DomUtil.offset(parent).top;
								
								if (y <= 50) {
									y = 0;
								}
								else {
									y -= 50;
								}
								
								window.scrollTo(0, y);
							}
						}
					}
				}, 100);
			}
		},
		
		/**
		 * Initializes available tab menus.
		 */
		_init: function() {
			var container, containerId, list, returnValue, tabMenu, tabMenus = elBySelAll('.tabMenuContainer:not(.staticTabMenuContainer)');
			for (var i = 0, length = tabMenus.length; i < length; i++) {
				container = tabMenus[i];
				containerId = DomUtil.identify(container);
				
				if (_tabMenus.has(containerId)) {
					continue;
				}
				
				tabMenu = new SimpleTabMenu(container);
				if (tabMenu.validate()) {
					returnValue = tabMenu.init();
					
					_tabMenus.set(containerId, tabMenu);
					
					if (returnValue instanceof Element) {
						tabMenu = this.getTabMenu(returnValue.parentNode.id);
						tabMenu.select(returnValue.id, null, true);
					}
					
					list = elBySel('#' + containerId + ' > nav > ul');
					(function(list) {
						list.addEventListener(WCF_CLICK_EVENT, function(event) {
							event.preventDefault();
							event.stopPropagation();
							
							if (event.target === list) {
								list.classList.add('active');
								
								_activeList = list;
							}
							else {
								list.classList.remove('active');
								
								_activeList = null;
							}
						});
					})(list);
					
					// bind scroll listener
					elBySelAll('.tabMenu, .menu', container, (function(menu) {
						var callback = this._rebuildMenuOverflow.bind(this, menu);
						
						var timeout = null;
						elBySel('ul', menu).addEventListener('scroll', function () {
							if (timeout !== null) {
								window.clearTimeout(timeout);
							}
							
							// slight delay to avoid calling this function too often
							timeout = window.setTimeout(callback, 10);
						});
					}).bind(this));
				}
			}
		},
		
		/**
		 * Selects the first tab containing an element with class `formError`.
		 */
		_selectErroneousTabs: function() {
			_tabMenus.forEach(function(tabMenu) {
				var foundError = false;
				tabMenu.getContainers().forEach(function(container) {
					if (!foundError && elByClass('formError', container).length) {
						foundError = true;
						
						tabMenu.select(container.id);
					}
				});
			});
		},
		
		/**
		 * Returns a SimpleTabMenu instance for given container id.
		 * 
		 * @param	{string}	containerId	tab menu container id
		 * @return	{(SimpleTabMenu|undefined)}	tab menu object
		 */
		getTabMenu: function(containerId) {
			return _tabMenus.get(containerId);
		},
		
		_scrollEnable: function (isSetup) {
			_enableTabScroll = true;
			
			_tabMenus.forEach((function (tabMenu) {
				var activeTab = tabMenu.getActiveTab();
				if (isSetup) {
					this._rebuildMenuOverflow(activeTab.closest('.menu, .tabMenu'));
				}
				else {
					this.scrollToTab(activeTab);
				}
			}).bind(this));
		},
		
		_scrollDisable: function () {
			_enableTabScroll = false;
		},
		
		scrollToTab: function (tab) {
			if (!_enableTabScroll) {
				return;
			}
			
			var list = tab.closest('ul');
			var width = list.clientWidth;
			var scrollLeft = list.scrollLeft;
			var scrollWidth = list.scrollWidth;
			if (width === scrollWidth) {
				// no overflow, ignore
				return;
			}
			
			// check if tab is currently visible
			var left = tab.offsetLeft;
			var shouldScroll = false;
			if (left < scrollLeft) {
				shouldScroll = true;
			}
			
			var paddingRight = false;
			if (!shouldScroll) {
				var visibleWidth = width - (left - scrollLeft);
				var virtualWidth = tab.clientWidth;
				if (tab.nextElementSibling !== null) {
					paddingRight = true;
					virtualWidth += 20;
				}
				
				if (visibleWidth < virtualWidth) {
					shouldScroll = true;
				}
			}
			
			if (shouldScroll) {
				// allow some padding to indicate overflow
				if (paddingRight) {
					left -= 15;
				}
				else if (left > 0) {
					left -= 15;
				}
				
				if (left < 0) {
					left = 0;
				}
				else {
					// ensure that our left value is always within the boundaries
					left = Math.min(left, scrollWidth - width);
				}
				
				if (scrollLeft === left) {
					return;
				}
				
				list.classList.add('enableAnimation');
				
				// new value is larger, we're scrolling towards the end
				if (scrollLeft < left) {
					list.firstElementChild.style.setProperty('margin-left', (scrollLeft - left) + 'px', '');
				}
				else {
					// new value is smaller, we're scrolling towards the start
					list.style.setProperty('padding-left', (scrollLeft - left) + 'px', '');
				}
				
				setTimeout(function () {
					list.classList.remove('enableAnimation');
					
					list.firstElementChild.style.removeProperty('margin-left');
					list.style.removeProperty('padding-left');
					
					list.scrollLeft = left;
				}, 300);
			}
		},
		
		_rebuildMenuOverflow: function (menu) {
			if (!_enableTabScroll) {
				return;
			}
			
			var width = menu.clientWidth;
			var list = elBySel('ul', menu);
			var scrollLeft = list.scrollLeft;
			var scrollWidth = list.scrollWidth;
			
			var overflowLeft = (scrollLeft > 0);
			var overlayLeft = elBySel('.tabMenuOverlayLeft', menu);
			if (overflowLeft) {
				if (overlayLeft === null) {
					overlayLeft = elCreate('span');
					overlayLeft.className = 'tabMenuOverlayLeft icon icon24 fa-angle-left';
					menu.insertBefore(overlayLeft, menu.firstChild);
				}
				
				overlayLeft.classList.add('active');
			}
			else if (overlayLeft !== null) {
				overlayLeft.classList.remove('active');
			}
			
			var overflowRight = (width + scrollLeft < scrollWidth);
			var overlayRight = elBySel('.tabMenuOverlayRight', menu);
			if (overflowRight) {
				if (overlayRight === null) {
					overlayRight = elCreate('span');
					overlayRight.className = 'tabMenuOverlayRight icon icon24 fa-angle-right';
					menu.appendChild(overlayRight);
				}
				
				overlayRight.classList.add('active');
			}
			else if (overlayRight !== null) {
				overlayRight.classList.remove('active');
			}
		}
	};
});
