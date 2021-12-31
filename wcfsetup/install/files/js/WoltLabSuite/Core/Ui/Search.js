/**
 * Manages the sticky page header.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Header/Fixed
 */
define(["require", "exports", "tslib", "../Event/Handler", "./Alignment", "./CloseOverlay", "./Dropdown/Simple", "./Screen", "../Environment", "../Dom/Util"], function (require, exports, tslib_1, EventHandler, UiAlignment, CloseOverlay_1, Simple_1, UiScreen, Environment, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    UiAlignment = (0, tslib_1.__importStar)(UiAlignment);
    CloseOverlay_1 = (0, tslib_1.__importStar)(CloseOverlay_1);
    Simple_1 = (0, tslib_1.__importDefault)(Simple_1);
    UiScreen = (0, tslib_1.__importStar)(UiScreen);
    Environment = (0, tslib_1.__importStar)(Environment);
    let _isMobile = false;
    let _scrollTop = undefined;
    const _isAcp = document.body.classList.contains("wcfAcp");
    const _pageHeader = document.getElementById("pageHeader");
    const _pageHeaderPanel = document.getElementById("pageHeaderPanel");
    const _pageHeaderSearch = document.getElementById("pageHeaderSearch");
    let _pageHeaderSearchMobile = undefined;
    const _pageHeaderSearchInput = document.getElementById("pageHeaderSearchInput");
    const _topMenu = document.getElementById("topMenu");
    const _userPanelSearchButton = document.getElementById("userPanelSearchButton");
    /**
     * Provides the collapsible search bar.
     */
    function initSearchBar() {
        _pageHeaderSearch.addEventListener("click", (event) => event.stopPropagation());
        const searchType = document.querySelector(".pageHeaderSearchType");
        const dropdownMenuId = (0, Util_1.identify)(searchType);
        const dropdownMenu = Simple_1.default.getDropdownMenu(dropdownMenuId);
        dropdownMenu.addEventListener("click", (event) => {
            // This prevents triggering the `UiCloseOverlay`.
            event.stopPropagation();
            Simple_1.default.close(dropdownMenuId);
        });
        _userPanelSearchButton === null || _userPanelSearchButton === void 0 ? void 0 : _userPanelSearchButton.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (_pageHeader.classList.contains("searchBarOpen")) {
                closeSearch();
            }
            else {
                openSearch();
            }
        });
        CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/Search", (origin, identifier) => {
            if (origin === CloseOverlay_1.Origin.DropDown) {
                const button = document.getElementById("pageHeaderSearchTypeSelect");
                if (button.dataset.target === identifier) {
                    return;
                }
            }
            closeSearch();
            _pageHeaderSearchMobile === null || _pageHeaderSearchMobile === void 0 ? void 0 : _pageHeaderSearchMobile.setAttribute("aria-expanded", "false");
        });
    }
    function initMobileSearch() {
        const searchButton = document.getElementById("pageHeaderSearchMobile");
        _pageHeaderSearchMobile = searchButton;
        searchButton.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (searchButton.getAttribute("aria-expanded") === "true") {
                closeSearch();
                searchButton.setAttribute("aria-expanded", "false");
            }
            else {
                if (Environment.platform() === "ios") {
                    _scrollTop = document.body.scrollTop;
                    UiScreen.scrollDisable();
                }
                openSearch();
                _pageHeaderSearch.style.setProperty("top", `${_pageHeader.offsetHeight}px`, "");
                _pageHeaderSearch.classList.add("open");
                _pageHeaderSearchInput.focus();
                if (Environment.platform() === "ios") {
                    document.body.scrollTop = 0;
                }
                searchButton.setAttribute("aria-expanded", "true");
            }
        });
        _pageHeaderSearch.addEventListener("click", (event) => {
            event.stopPropagation();
            if (event.target === _pageHeaderSearch) {
                event.preventDefault();
                closeSearch();
                searchButton.setAttribute("aria-expanded", "false");
            }
        });
    }
    function openSearch() {
        CloseOverlay_1.default.execute();
        _pageHeader.classList.add("searchBarOpen");
        _userPanelSearchButton === null || _userPanelSearchButton === void 0 ? void 0 : _userPanelSearchButton.parentElement.classList.add("open");
        if (!_isMobile) {
            // calculate value for `right` on desktop
            UiAlignment.set(_pageHeaderSearch, _topMenu, {
                horizontal: "right",
            });
        }
        _pageHeaderSearch.style.setProperty("top", `${_pageHeaderPanel.clientHeight}px`, "");
        _pageHeaderSearchInput.focus();
        window.setTimeout(() => {
            // Places the caret at the end of the search input.
            const length = _pageHeaderSearchInput.value.length;
            _pageHeaderSearchInput.selectionStart = length;
            _pageHeaderSearchInput.selectionEnd = length;
        }, 1);
    }
    function closeSearch() {
        _pageHeader.classList.remove("searchBarOpen");
        _pageHeaderSearch.classList.remove("open");
        _userPanelSearchButton === null || _userPanelSearchButton === void 0 ? void 0 : _userPanelSearchButton.parentElement.classList.remove("open");
        ["bottom", "left", "right", "top"].forEach((propertyName) => {
            _pageHeaderSearch.style.removeProperty(propertyName);
        });
        if (Environment.platform() === "ios") {
            UiScreen.scrollEnable();
            if (_scrollTop !== undefined) {
                document.body.scrollTop = _scrollTop;
                _scrollTop = undefined;
            }
        }
        _pageHeaderSearchInput.blur();
        // close the scope selection
        const scope = _pageHeaderSearch.querySelector(".pageHeaderSearchType");
        Simple_1.default.close(scope.id);
    }
    /**
     * Initializes the sticky page header handler.
     */
    function init() {
        // The search bar is unavailable during WCFSetup or the login.
        if (_isAcp && _pageHeaderSearch === null) {
            return;
        }
        initSearchBar();
        UiScreen.on("screen-md-down", {
            match() {
                _isMobile = true;
            },
            unmatch() {
                _isMobile = false;
                _scrollTop = undefined;
            },
            setup() {
                _isMobile = true;
                initMobileSearch();
            },
        });
        EventHandler.add("com.woltlab.wcf.Search", "close", closeSearch);
    }
    exports.init = init;
});
