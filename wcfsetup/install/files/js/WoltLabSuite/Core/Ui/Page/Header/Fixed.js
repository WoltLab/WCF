/**
 * Manages the sticky page header.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Header/Fixed
 */
define(["require", "exports", "tslib", "../../../Event/Handler", "../../Alignment", "../../CloseOverlay", "../../Dropdown/Simple", "../../Screen"], function (require, exports, tslib_1, EventHandler, UiAlignment, CloseOverlay_1, Simple_1, UiScreen) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    let _isMobile = false;
    let _pageHeader;
    let _pageHeaderContainer;
    let _pageHeaderPanel;
    let _pageHeaderSearch;
    let _searchInput;
    let _topMenu;
    let _userPanelSearchButton;
    /**
     * Provides the collapsible search bar.
     *
     * @protected
     */
    function initSearchBar() {
        _pageHeaderSearch = document.getElementById('pageHeaderSearch');
        _pageHeaderSearch.addEventListener('click', ev => ev.stopPropagation());
        _pageHeaderPanel = document.getElementById('pageHeaderPanel');
        _searchInput = document.getElementById('pageHeaderSearchInput');
        _topMenu = document.getElementById('topMenu');
        _userPanelSearchButton = document.getElementById('userPanelSearchButton');
        _userPanelSearchButton.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            if (_pageHeader.classList.contains('searchBarOpen')) {
                closeSearchBar();
            }
            else {
                openSearchBar();
            }
        });
        CloseOverlay_1.default.add('WoltLabSuite/Core/Ui/Page/Header/Fixed', () => {
            if (_pageHeader.classList.contains('searchBarForceOpen')) {
                return;
            }
            closeSearchBar();
        });
        EventHandler.add('com.woltlab.wcf.MainMenuMobile', 'more', data => {
            if (data.identifier === 'com.woltlab.wcf.search') {
                data.handler.close(true);
                _userPanelSearchButton.click();
            }
        });
    }
    /**
     * Opens the search bar.
     *
     * @protected
     */
    function openSearchBar() {
        window.WCF.Dropdown.Interactive.Handler.closeAll();
        _pageHeader.classList.add('searchBarOpen');
        _userPanelSearchButton.parentElement.classList.add('open');
        if (!_isMobile) {
            // calculate value for `right` on desktop
            UiAlignment.set(_pageHeaderSearch, _topMenu, {
                horizontal: 'right',
            });
        }
        _pageHeaderSearch.style.setProperty('top', _pageHeaderPanel.clientHeight + 'px', '');
        _searchInput.focus();
        window.setTimeout(() => {
            _searchInput.selectionStart = _searchInput.selectionEnd = _searchInput.value.length;
        }, 1);
    }
    /**
     * Closes the search bar.
     *
     * @protected
     */
    function closeSearchBar() {
        _pageHeader.classList.remove('searchBarOpen');
        _userPanelSearchButton.parentElement.classList.remove('open');
        ['bottom', 'left', 'right', 'top'].forEach(propertyName => {
            _pageHeaderSearch.style.removeProperty(propertyName);
        });
        _searchInput.blur();
        // close the scope selection
        const scope = _pageHeaderSearch.querySelector('.pageHeaderSearchType');
        Simple_1.default.close(scope.id);
    }
    /**
     * Initializes the sticky page header handler.
     */
    function init() {
        _pageHeader = document.getElementById('pageHeader');
        _pageHeaderContainer = document.getElementById('pageHeaderContainer');
        initSearchBar();
        UiScreen.on('screen-md-down', {
            match() {
                _isMobile = true;
            },
            unmatch() {
                _isMobile = false;
            },
            setup() {
                _isMobile = true;
            },
        });
        EventHandler.add('com.woltlab.wcf.Search', 'close', closeSearchBar);
    }
    exports.init = init;
});
