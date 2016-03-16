/**
 * Manages the sticky page header.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/Header/Fixed
 */
define(['Ui/CloseOverlay', 'Ui/SimpleDropdown'], function(UiCloseOverlay, UiSimpleDropdown) {
	"use strict";
	
	var _pageHeader, _pageHeaderContainer, _pageHeaderSearchInputContainer, _isFixed = false;
	
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
			_pageHeaderSearchInputContainer = elById('pageHeaderSearchInputContainer');
			
			this._initStickyPageHeader();
			this._initSearchBar();
		},
		
		/**
		 * Enforces a min-height for the original header's location to prevent collapsing
		 * when setting the header to `position: fixed`.
		 * 
		 * @protected
		 */
		_initStickyPageHeader: function() {
			_pageHeader.style.setProperty('min-height', _pageHeader.clientHeight + 'px');
			
			this._scroll();
			window.addEventListener('scroll', this._scroll.bind(this));
		},
		
		/**
		 * Provides the collapsible search bar.
		 * 
		 * @protected
		 */
		_initSearchBar: function() {
			var searchInput = elById('pageHeaderSearchInput');
			
			UiSimpleDropdown.registerCallback('pageHeaderSearchInputContainer', function() {
				if (_isFixed && !_pageHeaderSearchInputContainer.classList.contains('open')) {
					_pageHeaderSearchInputContainer.classList.add('open');
					searchInput.focus();
				}
			});
			
			UiCloseOverlay.add('WoltLab/WCF/Ui/Page/Header/Fixed', function() {
				_pageHeaderSearchInputContainer.classList.remove('open');
			});
		},
		
		/**
		 * Updates the page header state after scrolling.
		 * 
		 * @protected
		 */
		_scroll: function() {
			_isFixed = (document.body.scrollTop > 50);
			
			_pageHeader.classList[_isFixed ? 'add' : 'remove']('sticky');
			_pageHeaderContainer.classList[_isFixed ? 'add' : 'remove']('stickyPageHeader');
			
			_pageHeaderSearchInputContainer.classList.remove('open');
		}
	};
});
