/**
 * Manages the sticky page header.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Header/Fixed
 */
define(['Core', 'EventHandler', 'Ui/Alignment', 'Ui/CloseOverlay', 'Ui/Screen', 'Ui/Scroll', 'Ui/SimpleDropdown'], function(Core, EventHandler, UiAlignment, UiCloseOverlay, UiScreen, UiScroll, UiSimpleDropdown) {
	"use strict";
	
	var _callbackScroll = null;
	var _pageHeader, _pageHeaderContainer, _searchInput, _searchInputContainer;
	var _isMobile = false;
	
	/**
	 * @exports     WoltLabSuite/Core/Ui/Page/Header/Fixed
	 */
	return {
		/**
		 * Initializes the sticky page header handler.
		 */
		init: function() {
			_pageHeader = elById('pageHeader');
			_pageHeaderContainer = elById('pageHeaderContainer');
			
			this._initSearchBar();
			
			UiScreen.on('screen-md-down', {
				match: function () { _isMobile = true; },
				unmatch: function () { _isMobile = false; },
				setup: function () { _isMobile = true; }
			});
		},
		
		/**
		 * Provides the collapsible search bar.
		 * 
		 * @protected
		 */
		_initSearchBar: function() {
			var searchContainer = elById('pageHeaderSearch');
			searchContainer.addEventListener(WCF_CLICK_EVENT, function(event) {
				event.stopPropagation();
			});
			
			_searchInput = elById('pageHeaderSearchInput');
			
			var userPanelSearchButton = elById('userPanelSearchButton');
			var pageHeaderFacade = elById('pageHeaderFacade');
			
			_searchInputContainer = elById('pageHeaderSearchInputContainer');
			
			var menu = elById('topMenu');
			userPanelSearchButton.addEventListener(WCF_CLICK_EVENT, (function(event) {
				event.preventDefault();
				event.stopPropagation();
				
				var facadeHeight = pageHeaderFacade.clientHeight;
				var scrollTop = window.pageYOffset;
				var skipScrollHandler = false;
				var isVisible = !_isMobile && (facadeHeight > scrollTop);
				
				if (!isVisible && !_pageHeader.classList.contains('searchBarOpen')) {
					UiAlignment.set(_searchInputContainer, menu, {
						horizontal: 'right'
					});
					
					_pageHeader.classList.add('searchBarOpen');
					_searchInput.focus();
				}
				else if (!_isMobile) {
					if (scrollTop) {
						// setting focus could lead to the search bar to be
						// hidden behind the fixed panel
						UiScroll.element(elById('top'), function () {
							_searchInput.focus();
						});
					}
					else {
						_searchInput.focus();
					}
					
					skipScrollHandler = true;
				}
				
				WCF.Dropdown.Interactive.Handler.closeAll();
				
				if (!skipScrollHandler && !_isMobile && _callbackScroll === null) {
					_callbackScroll = (function () {
						if (pageHeaderFacade.clientHeight > window.pageYOffset) {
							this._closeSearchBar();
						}
					}).bind(this);
					window.addEventListener('scroll', _callbackScroll);
				}
			}).bind(this));
			
			UiCloseOverlay.add('WoltLabSuite/Core/Ui/Page/Header/Fixed', (function() {
				if (_pageHeader.classList.contains('searchBarForceOpen')) return;
				
				this._closeSearchBar();
			}).bind(this));
			
			EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', (function(data) {
				if (data.identifier === 'com.woltlab.wcf.search') {
					data.handler.close(true);
					
					Core.triggerEvent(elById('pageHeaderSearchInput'), WCF_CLICK_EVENT);
				}
			}).bind(this));
		},
		
		_closeSearchBar: function () {
			_pageHeader.classList.remove('searchBarOpen');
			
			['bottom', 'left', 'right', 'top'].forEach(function(propertyName) {
				_searchInputContainer.style.removeProperty(propertyName);
			});
			
			_searchInput.blur();
			
			if (_callbackScroll !== null) {
				window.removeEventListener('scroll', _callbackScroll);
				_callbackScroll = null;
			}
		}
	};
});
