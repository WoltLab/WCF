/**
 * Modifies the interface to provide a better usability for mobile devices.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Mobile
 */
define(['Core', 'Environment', 'EventHandler', 'Language', 'List', 'Dom/ChangeListener', 'Dom/Traverse', 'Dom/Util', 'Ui/Alignment', 'Ui/CloseOverlay', 'Ui/Screen', './Page/Menu/Main', './Page/Menu/User', 'WoltLabSuite/Core/Ui/Dropdown/Reusable'], function (Core, Environment, EventHandler, Language, List, DomChangeListener, DomTraverse, DomUtil, UiAlignment, UiCloseOverlay, UiScreen, UiPageMenuMain, UiPageMenuUser, UiDropdownReusable) {
    "use strict";
    var _buttonGroupNavigations = elByClass('buttonGroupNavigation');
    var _callbackCloseDropdown = null;
    var _dropdownMenu = null;
    var _dropdownMenuMessage = null;
    var _enabled = false;
    var _enabledLGTouchNavigation = false;
    var _knownMessages = new List();
    var _main = null;
    var _messages = elByClass('message');
    var _mobileSidebarEnabled = false;
    var _options = {};
    var _pageMenuMain = null;
    var _pageMenuUser = null;
    var _messageGroups = null;
    var _sidebars = [];
    /**
     * @exports	WoltLabSuite/Core/Ui/Mobile
     */
    return {
        /**
         * Initializes the mobile UI.
         *
         * @param       {Object=}       options         initialization options
         */
        setup: function (options) {
            _options = Core.extend({
                enableMobileMenu: true
            }, options);
            _main = elById('main');
            elBySelAll('.sidebar', undefined, function (sidebar) {
                _sidebars.push(sidebar);
            });
            if (Environment.touch()) {
                document.documentElement.classList.add('touch');
            }
            if (Environment.platform() !== 'desktop') {
                document.documentElement.classList.add('mobile');
            }
            var messageGroupList = elBySel('.messageGroupList');
            if (messageGroupList)
                _messageGroups = elByClass('messageGroup', messageGroupList);
            UiScreen.on('screen-md-down', {
                match: this.enable.bind(this),
                unmatch: this.disable.bind(this),
                setup: this._init.bind(this)
            });
            UiScreen.on('screen-sm-down', {
                match: this.enableShadow.bind(this),
                unmatch: this.disableShadow.bind(this),
                setup: this.enableShadow.bind(this)
            });
            UiScreen.on('screen-md-down', {
                match: this._enableMobileSidebar.bind(this),
                unmatch: this._disableMobileSidebar.bind(this),
                setup: this._setupMobileSidebar.bind(this)
            });
            // On the large tablets (e.g. iPad Pro) the navigation is not usable, because there is not the mobile
            // layout displayed, but the normal one for the desktop. The navigation reacts to a hover status if a
            // menu item has several submenu items. Logically, this cannot be created with the tablet, so that we
            // display the submenu here after a single click and only follow the link after another click.
            if (Environment.touch() && (Environment.platform() === 'ios' || Environment.platform() === 'android')) {
                UiScreen.on('screen-lg', {
                    match: this._enableLGTouchNavigation.bind(this),
                    unmatch: this._disableLGTouchNavigation.bind(this),
                    setup: this._setupLGTouchNavigation.bind(this)
                });
            }
        },
        /**
         * Enables the mobile UI.
         */
        enable: function () {
            _enabled = true;
            if (_options.enableMobileMenu) {
                _pageMenuMain.enable();
                _pageMenuUser.enable();
            }
        },
        /**
         * Enables shadow links for larger click areas on messages.
         */
        enableShadow: function () {
            if (_messageGroups)
                this.rebuildShadow(_messageGroups, '.messageGroupLink');
        },
        /**
         * Disables the mobile UI.
         */
        disable: function () {
            _enabled = false;
            if (_options.enableMobileMenu) {
                _pageMenuMain.disable();
                _pageMenuUser.disable();
            }
        },
        /**
         * Disables shadow links.
         */
        disableShadow: function () {
            if (_messageGroups)
                this.removeShadow(_messageGroups);
            if (_dropdownMenu)
                _callbackCloseDropdown();
        },
        _init: function () {
            _enabled = true;
            this._initSearchBar();
            this._initButtonGroupNavigation();
            this._initMessages();
            this._initMobileMenu();
            UiCloseOverlay.add('WoltLabSuite/Core/Ui/Mobile', this._closeAllMenus.bind(this));
            DomChangeListener.add('WoltLabSuite/Core/Ui/Mobile', (function () {
                this._initButtonGroupNavigation();
                this._initMessages();
            }).bind(this));
        },
        _initSearchBar: function () {
            var _searchBar = elById('pageHeaderSearch');
            var _searchInput = elById('pageHeaderSearchInput');
            var scrollTop = null;
            EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', function (data) {
                if (data.identifier === 'com.woltlab.wcf.search') {
                    data.handler.close(true);
                    if (Environment.platform() === 'ios') {
                        scrollTop = document.body.scrollTop;
                        UiScreen.scrollDisable();
                    }
                    _searchBar.style.setProperty('top', elById('pageHeader').offsetHeight + 'px', '');
                    _searchBar.classList.add('open');
                    _searchInput.focus();
                    if (Environment.platform() === 'ios') {
                        document.body.scrollTop = 0;
                    }
                }
            });
            _main.addEventListener(WCF_CLICK_EVENT, function () {
                if (_searchBar)
                    _searchBar.classList.remove('open');
                if (Environment.platform() === 'ios' && scrollTop !== null) {
                    UiScreen.scrollEnable();
                    document.body.scrollTop = scrollTop;
                    scrollTop = null;
                }
            });
        },
        _initButtonGroupNavigation: function () {
            for (var i = 0, length = _buttonGroupNavigations.length; i < length; i++) {
                var navigation = _buttonGroupNavigations[i];
                if (navigation.classList.contains('jsMobileButtonGroupNavigation'))
                    continue;
                else
                    navigation.classList.add('jsMobileButtonGroupNavigation');
                var list = elBySel('.buttonList', navigation);
                if (list.childElementCount === 0) {
                    // ignore objects without options
                    continue;
                }
                navigation.parentNode.classList.add('hasMobileNavigation');
                var button = elCreate('a');
                button.className = 'dropdownLabel';
                var span = elCreate('span');
                span.className = 'icon icon24 fa-ellipsis-v';
                button.appendChild(span);
                (function (navigation, button, list) {
                    button.addEventListener(WCF_CLICK_EVENT, function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        navigation.classList.toggle('open');
                    });
                    list.addEventListener(WCF_CLICK_EVENT, function (event) {
                        event.stopPropagation();
                        navigation.classList.remove('open');
                    });
                })(navigation, button, list);
                navigation.insertBefore(button, navigation.firstChild);
            }
        },
        _initMessages: function () {
            Array.prototype.forEach.call(_messages, (function (message) {
                if (_knownMessages.has(message)) {
                    return;
                }
                var navigation = elBySel('.jsMobileNavigation', message);
                if (navigation) {
                    navigation.addEventListener(WCF_CLICK_EVENT, function (event) {
                        event.stopPropagation();
                        // mimic dropdown behavior
                        window.setTimeout(function () {
                            navigation.classList.remove('open');
                        }, 10);
                    });
                    var quickOptions = elBySel('.messageQuickOptions', message);
                    if (quickOptions && navigation.childElementCount) {
                        quickOptions.classList.add('active');
                        quickOptions.addEventListener(WCF_CLICK_EVENT, (function (event) {
                            if (_enabled && UiScreen.is('screen-sm-down') && event.target.nodeName !== 'LABEL' && event.target.nodeName !== 'INPUT') {
                                event.preventDefault();
                                event.stopPropagation();
                                this._toggleMobileNavigation(message, quickOptions, navigation);
                            }
                        }).bind(this));
                    }
                }
                _knownMessages.add(message);
            }).bind(this));
        },
        _initMobileMenu: function () {
            if (_options.enableMobileMenu) {
                _pageMenuMain = new UiPageMenuMain();
                _pageMenuUser = new UiPageMenuUser();
            }
        },
        _closeAllMenus: function () {
            elBySelAll('.jsMobileButtonGroupNavigation.open, .jsMobileNavigation.open', null, function (menu) {
                menu.classList.remove('open');
            });
            if (_enabled && _dropdownMenu)
                _callbackCloseDropdown();
        },
        rebuildShadow: function (elements, linkSelector) {
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
        removeShadow: function (elements) {
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
        },
        _enableMobileSidebar: function () {
            _mobileSidebarEnabled = true;
        },
        _disableMobileSidebar: function () {
            _mobileSidebarEnabled = false;
            _sidebars.forEach(function (sidebar) {
                sidebar.classList.remove('open');
            });
        },
        _setupMobileSidebar: function () {
            _sidebars.forEach(function (sidebar) {
                sidebar.addEventListener('mousedown', function (event) {
                    if (_mobileSidebarEnabled && event.target === sidebar) {
                        event.preventDefault();
                        sidebar.classList.toggle('open');
                    }
                });
            });
            _mobileSidebarEnabled = true;
        },
        _toggleMobileNavigation: function (message, quickOptions, navigation) {
            if (_dropdownMenu === null) {
                _dropdownMenu = elCreate('ul');
                _dropdownMenu.className = 'dropdownMenu';
                UiDropdownReusable.init('com.woltlab.wcf.jsMobileNavigation', _dropdownMenu);
                _callbackCloseDropdown = function () {
                    _dropdownMenu.classList.remove('dropdownOpen');
                };
            }
            else if (_dropdownMenu.classList.contains('dropdownOpen')) {
                _callbackCloseDropdown();
                if (_dropdownMenuMessage === message) {
                    // toggle behavior
                    return;
                }
            }
            _dropdownMenu.innerHTML = '';
            UiCloseOverlay.execute();
            this._rebuildMobileNavigation(navigation);
            var previousNavigation = navigation.previousElementSibling;
            if (previousNavigation && previousNavigation.classList.contains('messageFooterButtonsExtra')) {
                var divider = elCreate('li');
                divider.className = 'dropdownDivider';
                _dropdownMenu.appendChild(divider);
                this._rebuildMobileNavigation(previousNavigation);
            }
            UiAlignment.set(_dropdownMenu, quickOptions, {
                horizontal: 'right',
                allowFlip: 'vertical'
            });
            _dropdownMenu.classList.add('dropdownOpen');
            _dropdownMenuMessage = message;
        },
        _setupLGTouchNavigation: function () {
            _enabledLGTouchNavigation = true;
            elBySelAll('.boxMenuHasChildren > a', null, function (element) {
                element.addEventListener('touchstart', function (event) {
                    if (_enabledLGTouchNavigation && elAttr(element, 'aria-expanded') === 'false') {
                        event.preventDefault();
                        elAttr(element, 'aria-expanded', 'true');
                        // Register an new event listener after the touch ended, which is triggered once when an 
                        // element on the page is pressed. This allows us to reset the touch status of the navigation 
                        // entry when the entry is no longer open, so that it does not redirect to the page when you 
                        // click it again. 
                        element.addEventListener('touchend', function () {
                            document.body.addEventListener('touchstart', function () {
                                document.body.addEventListener('touchend', function (event) {
                                    if (!DomUtil.contains(element.parentNode, event.target) && event.target !== element.parentNode) {
                                        elAttr(element, 'aria-expanded', 'false');
                                    }
                                }, {
                                    once: true
                                });
                            }, {
                                once: true
                            });
                        }, {
                            once: true
                        });
                    }
                });
            });
        },
        _enableLGTouchNavigation: function () {
            _enabledLGTouchNavigation = true;
        },
        _disableLGTouchNavigation: function () {
            _enabledLGTouchNavigation = false;
        },
        _rebuildMobileNavigation: function (navigation) {
            elBySelAll('.button', navigation, function (button) {
                if (button.classList.contains('ignoreMobileNavigation')) {
                    // The reaction button was hidden up until 5.2.2, but was enabled again in 5.2.3. This check
                    // exists to make sure that there is no unexpected behavior in 3rd party apps or plugins that
                    // used the same code and hid the reaction button via a CSS class in the template.
                    if (!button.classList.contains('reactButton')) {
                        return;
                    }
                }
                var item = elCreate('li');
                if (button.classList.contains('active'))
                    item.className = 'active';
                item.innerHTML = '<a href="#">' + elBySel('span:not(.icon)', button).textContent + '</a>';
                item.children[0].addEventListener(WCF_CLICK_EVENT, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (button.nodeName === 'A')
                        button.click();
                    else
                        Core.triggerEvent(button, WCF_CLICK_EVENT);
                    _callbackCloseDropdown();
                });
                _dropdownMenu.appendChild(item);
            });
        }
    };
});
