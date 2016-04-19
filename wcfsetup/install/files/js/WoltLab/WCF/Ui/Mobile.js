/**
 * Modifies the interface to provide a better usability for mobile devices.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Mobile
 */
define(
	[        'Core', 'Environment', 'Language', 'Dom/ChangeListener', 'Ui/CloseOverlay', 'Ui/Screen', './Page/Menu/Main', './Page/Menu/User'],
	function(Core,    Environment,   Language,   DomChangeListener,    UiCloseOverlay,    UiScreen,    UiPageMenuMain,     UiPageMenuUser)
{
	"use strict";
	
	var _buttonGroupNavigations = null;
	var _enabled = false;
	var _main = null;
	var _options = {};
	var _pageMenuMain = null;
	var _pageMenuUser = null;
	var _sidebar = null;
	
	/**
	 * @exports	WoltLab/WCF/Ui/Mobile
	 */
	return {
		/**
		 * Initializes the mobile UI.
		 * 
		 * @param       {Object=}       options         initialization options
		 */
		setup: function(options) {
			_options = Core.extend({
				enableMobileMenu: true
			}, options);
			
			_buttonGroupNavigations = elByClass('buttonGroupNavigation');
			_main = elById('main');
			_sidebar = elBySel('#main > div > div > .sidebar', _main);
			
			if (Environment.touch()) {
				document.documentElement.classList.add('touch');
			}
			
			if (Environment.platform() !== 'desktop') {
				document.documentElement.classList.add('mobile');
			}
			
			UiScreen.on('screen-md-down', {
				match: this.enable.bind(this),
				unmatch: this.disable.bind(this),
				setup: this._init.bind(this)
			});
		},
		
		/**
		 * Enables the mobile UI.
		 */
		enable: function() {
			_enabled = true;
			
			if (_options.enableMobileMenu) {
				_pageMenuMain.enable();
				_pageMenuUser.enable();
			}
		},
		
		/**
		 * Disables the mobile UI.
		 */
		disable: function() {
			_enabled = false;
			
			if (_options.enableMobileMenu) {
				_pageMenuMain.disable();
				_pageMenuUser.disable();
			}
		},
		
		_init: function() {
			//this._initSidebarToggleButtons();
			//this._initSearchBar();
			this._initButtonGroupNavigation();
			this._initMobileMenu();
			
			UiCloseOverlay.add('WoltLab/WCF/Ui/Mobile', this._closeAllMenus.bind(this));
			DomChangeListener.add('WoltLab/WCF/Ui/Mobile', this._initButtonGroupNavigation.bind(this));
		},
		
		_initSidebarToggleButtons: function() {
			if (_sidebar === null) return;
			
			var sidebarPosition = (_main.classList.contains('sidebarOrientationLeft')) ? 'Left' : '';
			sidebarPosition = (sidebarPosition) ? sidebarPosition : (_main.classList.contains('sidebarOrientationRight') ? 'Right' : '');
			
			if (!sidebarPosition) {
				return;
			}
			
			// use icons if language item is empty/non-existent
			var languageShowSidebar = 'wcf.global.sidebar.show' + sidebarPosition + 'Sidebar';
			if (languageShowSidebar === Language.get(languageShowSidebar) || Language.get(languageShowSidebar) === '') {
				languageShowSidebar = elCreate('span');
				languageShowSidebar.className = 'icon icon16 fa-angle-double-' + sidebarPosition.toLowerCase();
			}
			
			var languageHideSidebar = 'wcf.global.sidebar.hide' + sidebarPosition + 'Sidebar';
			if (languageHideSidebar === Language.get(languageHideSidebar) || Language.get(languageHideSidebar) === '') {
				languageHideSidebar = elCreate('span');
				languageHideSidebar.className = 'icon icon16 fa-angle-double-' + (sidebarPosition === 'Left' ? 'right' : 'left');
			}
			
			// add toggle buttons
			var showSidebar = elCreate('span');
			showSidebar.className = 'button small mobileSidebarToggleButton';
			showSidebar.addEventListener('click', function() { _main.classList.add('mobileShowSidebar'); });
			if (languageShowSidebar instanceof Element) showSidebar.appendChild(languageShowSidebar);
			else showSidebar.textContent = languageShowSidebar;
			
			var hideSidebar = elCreate('span');
			hideSidebar.className = 'button small mobileSidebarToggleButton';
			hideSidebar.addEventListener('click', function() { _main.classList.remove('mobileShowSidebar'); });
			if (languageHideSidebar instanceof Element) hideSidebar.appendChild(languageHideSidebar);
			else hideSidebar.textContent = languageHideSidebar;
			
			elBySel('.content').appendChild(showSidebar);
			_sidebar.appendChild(hideSidebar);
		},
		
		_initSearchBar: function() {
			var _searchBar = elBySel('.searchBar');
			
			_searchBar.addEventListener('click', function() {
				if (_enabled) {
					_searchBar.classList.add('searchBarOpen');
					
					return false;
				}
				
				return false;
			});
			
			_main.addEventListener('click', function() { _searchBar.classList.remove('searchBarOpen'); });
		},
		
		_initButtonGroupNavigation: function() {
			for (var i = 0, length = _buttonGroupNavigations.length; i < length; i++) {
				var navigation = _buttonGroupNavigations[i];
				
				if (navigation.classList.contains('jsMobileButtonGroupNavigation')) continue;
				else navigation.classList.add('jsMobileButtonGroupNavigation');
				
				var button = elCreate('a');
				button.classList.add('dropdownLabel');
				
				var span = elCreate('span');
				span.className = 'icon icon24 fa-list';
				button.appendChild(span);
				
				(function(button) {
					button.addEventListener('click', function(ev) {
						var next = button.nextElementSibling;
						if (next !== null) {
							next.classList.toggle('open');
							
							ev.stopPropagation();
							return false;
						}
						
						return true;
					});
				})(button);
				
				navigation.insertBefore(button, navigation.firstChild);
			}
		},
		
		_initMobileMenu: function() {
			if (_options.enableMobileMenu) {
				_pageMenuMain = new UiPageMenuMain();
				_pageMenuUser = new UiPageMenuUser();
			}
		},
		
		_closeAllMenus: function() {
			var openMenus = elBySelAll('.jsMobileButtonGroupNavigation > ul.open');
			for (var i = 0, length = openMenus.length; i < length; i++) {
				openMenus[i].classList.remove('open');
			}
		}
	};
});
