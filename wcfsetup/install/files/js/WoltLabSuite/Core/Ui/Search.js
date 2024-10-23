/**
 * Controls the behavior and placement of the search input depending on
 * the context (frontend or admin panel) and the active view (mobile or
 * desktop).
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Event/Handler", "./Alignment", "./CloseOverlay", "./Dropdown/Simple", "./Screen", "../Environment", "../Dom/Util"], function (require, exports, tslib_1, EventHandler, UiAlignment, CloseOverlay_1, Simple_1, UiScreen, Environment, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    EventHandler = tslib_1.__importStar(EventHandler);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    CloseOverlay_1 = tslib_1.__importStar(CloseOverlay_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    Environment = tslib_1.__importStar(Environment);
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
        _userPanelSearchButton?.addEventListener("click", (event) => {
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
            if (origin === CloseOverlay_1.Origin.Search) {
                return;
            }
            else if (origin === CloseOverlay_1.Origin.DropDown) {
                const button = document.getElementById("pageHeaderSearchTypeSelect");
                if (button.dataset.target === identifier) {
                    return;
                }
                // Exception for the search bar in the admin panel.
                if (_pageHeaderSearchInput.parentElement.id === identifier) {
                    return;
                }
            }
            closeSearch();
            _pageHeaderSearchMobile?.setAttribute("aria-expanded", "false");
        });
        window.addEventListener("resize", () => {
            if (_isMobile || !_pageHeader.classList.contains("searchBarOpen")) {
                return;
            }
            UiAlignment.set(_pageHeaderSearch, _topMenu, {
                horizontal: "right",
            });
        }, { passive: true });
    }
    function initMobileSearch() {
        const searchButton = document.getElementById("pageHeaderSearchMobile");
        _pageHeaderSearchMobile = searchButton;
        searchButton.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (searchButton.getAttribute("aria-expanded") === "true") {
                closeSearch();
            }
            else {
                // iOS Safari behaves unpredictable when the keyboard focus
                // is moved into a HTML element that is inside a parent with
                // fixed positioning *and* the page had been scrolled down.
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
                searchButton.querySelector("fa-icon").setIcon("xmark");
            }
        });
        _pageHeaderSearch.addEventListener("click", (event) => {
            event.stopPropagation();
            if (event.target === _pageHeaderSearch) {
                event.preventDefault();
                closeSearch();
            }
        });
    }
    function openSearch() {
        CloseOverlay_1.default.execute(CloseOverlay_1.Origin.Search);
        _pageHeader.classList.add("searchBarOpen");
        _userPanelSearchButton?.parentElement.classList.add("open");
        if (!_isMobile) {
            // Calculate the value for `right` on desktop.
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
        const wasOpen = _pageHeader.classList.contains("searchBarOpen") || _pageHeaderSearch.classList.contains("open");
        if (!wasOpen) {
            return;
        }
        _pageHeader.classList.remove("searchBarOpen");
        _pageHeaderSearch.classList.remove("open");
        _userPanelSearchButton?.parentElement.classList.remove("open");
        const positions = ["bottom", "left", "right", "top"];
        positions.forEach((propertyName) => {
            _pageHeaderSearch.style.removeProperty(propertyName);
        });
        if (Environment.platform() === "ios") {
            UiScreen.scrollEnable();
            if (_scrollTop !== undefined) {
                document.body.scrollTop = _scrollTop;
                _scrollTop = undefined;
            }
        }
        if (_isMobile) {
            _pageHeaderSearchInput.blur();
        }
        const searchButton = document.getElementById("pageHeaderSearchMobile");
        if (searchButton) {
            searchButton.setAttribute("aria-expanded", "false");
            searchButton.querySelector("fa-icon").setIcon("magnifying-glass");
        }
        const scope = _pageHeaderSearch.querySelector(".pageHeaderSearchType");
        Simple_1.default.close(scope.id);
    }
    /**
     * Initializes the sticky page header handler.
     */
    function init() {
        // The search bar is unavailable during WCFSetup or the login.
        if (_pageHeaderSearch === null) {
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
        EventHandler.add("com.woltlab.wcf.Search", "close", () => closeSearch());
    }
});
