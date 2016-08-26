/**
 * Modifies the interface to provide a better usability for mobile devices.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Mobile
 */
define(
	[        'Core', 'Environment', 'EventHandler', 'Language', 'List', 'Dom/ChangeListener', 'Dom/Traverse', 'Ui/CloseOverlay', 'Ui/Screen', './Page/Menu/Main', './Page/Menu/User'],
	function(Core,    Environment,   EventHandler,   Language,   List,   DomChangeListener,    DomTraverse,    UiCloseOverlay,    UiScreen,    UiPageMenuMain,     UiPageMenuUser)
{
	"use strict";
	
	var _buttonGroupNavigations = elByClass('buttonGroupNavigation');
	var _enabled = false;
	var _knownMessages = new List();
	var _main = null;
	var _messages = elByClass('message');
	var _options = {};
	var _pageMenuMain = null;
	var _pageMenuUser = null;
	var _messageGroups = null;
	
	/**
	 * @exports	WoltLabSuite/Core/Ui/Mobile
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
			
			_main = elById('main');
			
			if (Environment.touch()) {
				document.documentElement.classList.add('touch');
			}
			
			if (Environment.platform() !== 'desktop') {
				document.documentElement.classList.add('mobile');
			}
			
			var messageGroupList = elBySel('.messageGroupList');
			if (messageGroupList) _messageGroups = elByClass('messageGroup', messageGroupList);
			
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
			
			if (_messageGroups) this.rebuildShadow(_messageGroups, '.messageGroupLink');
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
			
			if (_messageGroups) this.removeShadow(_messageGroups);
		},
		
		_init: function() {
			_enabled = true;
			
			this._initSearchBar();
			this._initButtonGroupNavigation();
			this._initMessages();
			this._initMobileMenu();
			
			UiCloseOverlay.add('WoltLabSuite/Core/Ui/Mobile', this._closeAllMenus.bind(this));
			DomChangeListener.add('WoltLabSuite/Core/Ui/Mobile', (function() {
				this._initButtonGroupNavigation();
				this._initMessages();
			}).bind(this));
			
			if (_messageGroups) this.rebuildShadow(_messageGroups, '.messageGroupLink');
		},
		
		_initSearchBar: function() {
			var _searchBar = elById('pageHeaderSearch');
			var _searchInput = elById('pageHeaderSearchInput');
			
			EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', function(data) {
				if (data.identifier === 'com.woltlab.wcf.search') {
					_searchBar.style.setProperty('top', elById('pageHeader').offsetHeight + 'px', '');
					_searchBar.classList.add('open');
					_searchInput.focus();
					
					data.handler.close(true);
				}
			});
			
			_main.addEventListener(WCF_CLICK_EVENT, function() { _searchBar.classList.remove('open'); });
		},
		
		_initButtonGroupNavigation: function() {
			for (var i = 0, length = _buttonGroupNavigations.length; i < length; i++) {
				var navigation = _buttonGroupNavigations[i];
				
				if (navigation.classList.contains('jsMobileButtonGroupNavigation')) continue;
				else navigation.classList.add('jsMobileButtonGroupNavigation');
				
				navigation.parentNode.classList.add('hasMobileNavigation');
				
				var button = elCreate('a');
				button.className = 'dropdownLabel';
				
				var span = elCreate('span');
				span.className = 'icon icon24 fa-ellipsis-v';
				button.appendChild(span);
				
				var list = elBySel('.buttonList', navigation);
				list.addEventListener(WCF_CLICK_EVENT, function(event) {
					event.stopPropagation();
				});
				
				(function(navigation, button) {
					button.addEventListener(WCF_CLICK_EVENT, function(event) {
						event.preventDefault();
						event.stopPropagation();
						
						navigation.classList.toggle('open');
					});
				})(navigation, button);
				
				navigation.insertBefore(button, navigation.firstChild);
			}
		},
		
		_initMessages: function() {
			Array.prototype.forEach.call(_messages, function(message) {
				if (_knownMessages.has(message)) {
					return;
				}
				
				var navigation = elBySel('.jsMobileNavigation', message);
				var quickOptions = elBySel('.messageQuickOptions', message);
				
				if (quickOptions) {
					quickOptions.addEventListener(WCF_CLICK_EVENT, function (event) {
						if (_enabled) {
							event.preventDefault();
							event.stopPropagation();
							
							navigation.classList.toggle('open');
						}
					});
				}
				if (navigation) {
					navigation.addEventListener(WCF_CLICK_EVENT, function(event) {
						event.stopPropagation();
						
						// mimic dropdown behavior
						window.setTimeout(function () {
							navigation.classList.remove('open');
						}, 10);
					});
				}
				
				_knownMessages.add(message);
			});
		},
		
		_initMobileMenu: function() {
			if (_options.enableMobileMenu) {
				_pageMenuMain = new UiPageMenuMain();
				_pageMenuUser = new UiPageMenuUser();
			}
			
			elBySelAll('.boxMenu', null, function(boxMenu) {
				boxMenu.addEventListener(WCF_CLICK_EVENT, function(event) {
					event.stopPropagation();
					
					if (event.target === boxMenu) {
						event.preventDefault();
						
						boxMenu.classList.add('open');
					}
				});
			});
		},
		
		_closeAllMenus: function() {
			elBySelAll('.jsMobileButtonGroupNavigation.open, .jsMobileNavigation.open, .boxMenu.open', null, function (menu) {
				menu.classList.remove('open');
			});
		},
		
		rebuildShadow: function(elements, linkSelector) {
			var element, parent, shadow;
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				parent = element.parentNode;
				
				shadow = DomTraverse.childByClass(parent, 'mobileLinkShadow');
				if (shadow === null) {
					if (elBySel(linkSelector, element).href) {
						shadow = elCreate('a');
						shadow.className = 'mobileLinkShadow';
						shadow.href = elBySel(linkSelector, element).href;
						
						parent.appendChild(shadow);
						parent.classList.add('mobileLinkShadowContainer');
					}
				}
			}
		},
		
		removeShadow: function(elements) {
			var element, parent, shadow;
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				parent = element.parentNode;
				
				if (parent.classList.contains('mobileLinkShadowContainer')) {
					shadow = DomTraverse.childByClass(parent, 'mobileLinkShadow');
					if (shadow !== null) {
						elRemove(shadow);
					}
					
					parent.classList.remove('mobileLinkShadowContainer');
				}
			}
		}
	};
});
