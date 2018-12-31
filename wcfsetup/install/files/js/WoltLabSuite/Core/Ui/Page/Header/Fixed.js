/**
 * Manages the sticky page header.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Page/Header/Fixed
 */
define(['Core', 'EventHandler', 'Ui/Alignment', 'Ui/CloseOverlay', 'Ui/Screen'], function(Core, EventHandler, UiAlignment, UiCloseOverlay, UiScreen) {
	"use strict";
	
	var _pageHeader, _pageHeaderContainer, _pageHeaderPanel, _pageHeaderSearch, _searchInput, _topMenu, _userPanelSearchButton;
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
			
			EventHandler.add('com.woltlab.wcf.Search', 'close', this._closeSearchBar.bind(this));
		},
		
		/**
		 * Provides the collapsible search bar.
		 * 
		 * @protected
		 */
		_initSearchBar: function() {
			_pageHeaderSearch = elById('pageHeaderSearch');
			_pageHeaderSearch.addEventListener(WCF_CLICK_EVENT, function(event) { event.stopPropagation(); });
			
			_pageHeaderPanel = elById('pageHeaderPanel');
			_searchInput = elById('pageHeaderSearchInput');
			_topMenu = elById('topMenu');
			
			_userPanelSearchButton = elById('userPanelSearchButton');
			_userPanelSearchButton.addEventListener(WCF_CLICK_EVENT, (function(event) {
				event.preventDefault();
				event.stopPropagation();
				
				if (_pageHeader.classList.contains('searchBarOpen')) {
					this._closeSearchBar();
				}
				else {
					this._openSearchBar();
				}
			}).bind(this));
			
			UiCloseOverlay.add('WoltLabSuite/Core/Ui/Page/Header/Fixed', (function() {
				if (_pageHeader.classList.contains('searchBarForceOpen')) return;
				
				this._closeSearchBar();
			}).bind(this));
			
			EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', (function(data) {
				if (data.identifier === 'com.woltlab.wcf.search') {
					data.handler.close(true);
					
					Core.triggerEvent(_userPanelSearchButton, WCF_CLICK_EVENT);
				}
			}).bind(this));
		},
		
		/**
		 * Opens the search bar.
		 * 
		 * @protected
		 */
		_openSearchBar: function() {
			window.WCF.Dropdown.Interactive.Handler.closeAll();
			
			_pageHeader.classList.add('searchBarOpen');
			_userPanelSearchButton.parentNode.classList.add('open');
			
			if (!_isMobile) {
				// calculate value for `right` on desktop
				UiAlignment.set(_pageHeaderSearch, _topMenu, {
					horizontal: 'right'
				});
			}
			
			_pageHeaderSearch.style.setProperty('top', _pageHeaderPanel.clientHeight + 'px', '');
			_searchInput.focus();
			window.setTimeout(function() {
				_searchInput.selectionStart = _searchInput.selectionEnd = _searchInput.value.length;
			}, 1);
		},
		
		/**
		 * Closes the search bar.
		 * 
		 * @protected
		 */
		_closeSearchBar: function () {
			_pageHeader.classList.remove('searchBarOpen');
			_userPanelSearchButton.parentNode.classList.remove('open');
			
			['bottom', 'left', 'right', 'top'].forEach(function(propertyName) {
				_pageHeaderSearch.style.removeProperty(propertyName);
			});
			
			_searchInput.blur();
		}
	};
});
