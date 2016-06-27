/**
 * Manages the sticky page header.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Header/Fixed
 */
define(['Core', 'EventHandler', 'Ui/Alignment', 'Ui/CloseOverlay', 'Ui/Screen', 'Ui/SimpleDropdown'], function(Core, EventHandler, UiAlignment, UiCloseOverlay, UiScreen, UiSimpleDropdown) {
	"use strict";
	
	var _pageHeader, _pageHeaderContainer, _searchInputContainer, _triggerHeight;
	var _isFixed = false, _isMobile = false;
	
	/**
	 * @exports     WoltLab/WCF/Ui/Page/Header/Fixed
	 */
	return {
		/**
		 * Initializes the sticky page header handler.
		 */
		init: function() {
			_pageHeader = elById('pageHeader');
			_pageHeaderContainer = elById('pageHeaderContainer');
			
			this._initStickyPageHeader();
			this._initSearchBar();
			
			UiScreen.on('screen-md-down', {
				match: function() { _isMobile = true; },
				unmatch: function() { _isMobile = false; },
				setup: function() { _isMobile = true; }
			});
		},
		
		/**
		 * Enforces a min-height for the original header's location to prevent collapsing
		 * when setting the header to `position: fixed`.
		 * 
		 * @protected
		 */
		_initStickyPageHeader: function() {
			if (_pageHeader.clientHeight) {
				_pageHeader.style.setProperty('min-height', _pageHeader.clientHeight + 'px');
			}
			
			_triggerHeight = _pageHeader.clientHeight - elBySel('.mainMenu', _pageHeader).clientHeight;
			
			this._scroll();
			window.addEventListener('scroll', this._scroll.bind(this));
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
			
			var searchInput = elById('pageHeaderSearchInput');
			
			var searchLabel = elBySel('.pageHeaderSearchLabel');
			_searchInputContainer = elById('pageHeaderSearchInputContainer');
			
			var menu = elById('topMenu');
			searchLabel.addEventListener(WCF_CLICK_EVENT, function() {
				if ((_isFixed || _isMobile) && !_pageHeader.classList.contains('searchBarOpen')) {
					UiAlignment.set(_searchInputContainer, menu, {
						horizontal: 'right'
					});
					
					_pageHeader.classList.add('searchBarOpen');
					searchInput.focus();
				}
			});
			
			UiCloseOverlay.add('WoltLab/WCF/Ui/Page/Header/Fixed', function() {
				_pageHeader.classList.remove('searchBarOpen');
			});
			
			EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', (function(data) {
				if (data.identifier === 'com.woltlab.wcf.search') {
					data.handler.close(true);
					
					Core.triggerEvent(elById('pageHeaderSearchInput'), WCF_CLICK_EVENT);
				}
			}).bind(this));
		},
		
		/**
		 * Updates the page header state after scrolling.
		 * 
		 * @protected
		 */
		_scroll: function() {
			var wasFixed = _isFixed;
			
			_isFixed = (window.scrollY > _triggerHeight);
			
			_pageHeader.classList[_isFixed ? 'add' : 'remove']('sticky');
			_pageHeaderContainer.classList[_isFixed ? 'add' : 'remove']('stickyPageHeader');
			
			if (!_isFixed && wasFixed) {
				_pageHeader.classList.remove('searchBarOpen');
				['bottom', 'left', 'right', 'top'].forEach(function(propertyName) {
					_searchInputContainer.style.removeProperty(propertyName);
				});
			}
		}
	};
});
