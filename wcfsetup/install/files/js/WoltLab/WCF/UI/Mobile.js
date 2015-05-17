/**
 * Modifies the interface to provide a better usability for mobile devices.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Mobile
 */
define(['jquery', 'enquire', 'Language', 'DOM/Traverse'], function($, enquire, Language, DOMTraverse) {
	"use strict";
	
	var _buttonGroupNavigations = null;
	var _enabled = false;
	var _main = null;
	var _sidebar = null;
	
	/**
	 * @constructor
	 */
	function UIMobile() {};
	UIMobile.prototype = {
		/**
		 * Initializes the mobile UI using enquire.js.
		 */
		setup: function() {
			_buttonGroupNavigations = document.getElementsByClassName('buttonGroupNavigation');
			_main = document.getElementById('main');
			_sidebar = _main.querySelector('#main > div > div > .sidebar');
			
			if ($.browser.touch) {
				document.documentElement.classList.add('touch');
			}
			
			enquire.register('screen and (max-width: 800px)', {
				match: this.enable.bind(this),
				unmatch: this.disable.bind(this),
				setup: this._init.bind(this),
				deferSetup: true
			});
			
			if ($.browser.msie && _sidebar.clientWidth > 305) {
				this._fixSidebarIE();
			}
		},
		
		/**
		 * Enables the mobile UI.
		 */
		enable: function() {
			_enabled = true;
			
			if ($.browser.msie) this._fixSidebarIE();
		},
		
		/**
		 * Disables the mobile UI.
		 */
		disable: function() {
			_enabled = false;
			
			if ($.browser.msie) this._fixSidebarIE();
		},
		
		_fixSidebarIE: function() {
			// sidebar is rarely broken on IE9/IE10
			_sidebar.style.setProperty('display', 'none');
			_sidebar.style.removeProperty('display');
		},
		
		_init: function() {
			this._initSidebarToggleButtons();
			this._initSearchBar();
			this._initButtonGroupNavigation();
			
			WCF.CloseOverlayHandler.addCallback('WoltLab/WCF/UI/Mobile', this._closeAllMenus.bind(this));
			WCF.DOMNodeInsertedHandler.addCallback('WoltLab/WCF/UI/Mobile', this._initButtonGroupNavigation.bind(this));
		},
		
		_initSidebarToggleButtons: function() {
			var sidebarPosition = (_main.classList.contains('sidebarOrientationLeft')) ? 'Left' : '';
			sidebarPosition = (sidebarPosition) ? sidebarPosition : (_main.classList.contains('sidebarOrientationRight') ? 'Right' : '');
			
			if (!sidebarPosition) {
				return;
			}
			
			// use icons if language item is empty/non-existant
			var languageShowSidebar = 'wcf.global.sidebar.show' + sidebarPosition + 'Sidebar';
			if (languageShowSidebar === Language.get(languageShowSidebar) || Language.get(languageShowSidebar) === '') {
				languageShowSidebar = document.createElement('span');
				languageShowSidebar.className = 'icon icon16 fa-angle-double-' + sidebarPosition.toLowerCase();
			}
			
			var languageHideSidebar = 'wcf.global.sidebar.hide' + sidebarPosition + 'Sidebar';
			if (languageHideSidebar === Language.get(languageHideSidebar) || Language.get(languageHideSidebar) === '') {
				languageHideSidebar = document.createElement('span');
				languageHideSidebar.className = 'icon icon16 fa-angle-double-' + (sidebarPosition === 'Left' ? 'right' : 'left');
			}
			
			// add toggle buttons
			var showSidebar = document.createElement('span');
			showSidebar.className = 'button small mobileSidebarToggleButton';
			showSidebar.addEventListener('click', function() { _main.classList.add('mobileShowSidebar'); });
			if (languageShowSidebar instanceof Element) showSidebar.appendChild(languageShowSidebar);
			else showSidebar.textContent = languageShowSidebar;
			
			var hideSidebar = document.createElement('span');
			hideSidebar.className = 'button small mobileSidebarToggleButton';
			hideSidebar.addEventListener('click', function() { _main.classList.remove('mobileShowSidebar'); });
			if (languageHideSidebar instanceof Element) hideSidebar.appendChild(languageHideSidebar);
			else hideSidebar.textContent = languageHideSidebar;
			
			document.querySelector('.content').appendChild(showSidebar);
			_sidebar.appendChild(hideSidebar);
		},
		
		_initSearchBar: function() {
			var _searchBar = document.querySelector('.searchBar');
			
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
				
				var button = document.createElement('a');
				button.classList.add('dropdownLabel');
				
				var span = document.createElement('span');
				span.className = 'icon icon24 fa-list';
				button.appendChild(span);
				
				button.addEventListener('click', function(ev) {
					var next = DOMTraverse.next(button);
					if (next !== null) {
						next.classList.toggle('open');
						
						ev.stopPropagation();
						return false;
					}
					
					return true;
				});
				
				navigation.insertBefore(button, navigation.firstChild);
			}
		},
		
		_closeAllMenus: function() {
			var openMenus = document.querySelectorAll('.jsMobileButtonGroupNavigation > ul.open');
			for (var i = 0, length = openMenus.length; i < length; i++) {
				openMenus[i].classList.remove('open');
			}
		}
	};
	
	return new UIMobile();
});
