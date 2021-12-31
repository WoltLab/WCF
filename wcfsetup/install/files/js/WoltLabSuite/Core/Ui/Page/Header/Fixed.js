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
    exports.init = exports.closeSearchBar = exports.openSearchBar = void 0;
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    UiAlignment = (0, tslib_1.__importStar)(UiAlignment);
    CloseOverlay_1 = (0, tslib_1.__importStar)(CloseOverlay_1);
    Simple_1 = (0, tslib_1.__importDefault)(Simple_1);
    UiScreen = (0, tslib_1.__importStar)(UiScreen);
    let _isMobile = false;
    const _pageHeader = document.getElementById("pageHeader");
    const _pageHeaderPanel = document.getElementById("pageHeaderPanel");
    const _pageHeaderSearch = document.getElementById("pageHeaderSearch");
    const _searchInput = document.getElementById("pageHeaderSearchInput");
    const _topMenu = document.getElementById("topMenu");
    let _userPanelSearchButton = null;
    /**
     * Provides the collapsible search bar.
     */
    function initSearchBar() {
        _pageHeaderSearch.addEventListener("click", (ev) => ev.stopPropagation());
        _userPanelSearchButton = document.getElementById("userPanelSearchButton");
        _userPanelSearchButton.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (_pageHeader.classList.contains("searchBarOpen")) {
                closeSearchBar();
            }
            else {
                openSearchBar();
            }
        });
        CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/Page/Header/Fixed", (origin, identifier) => {
            if (origin === CloseOverlay_1.Origin.DropDown) {
                const button = document.getElementById("pageHeaderSearchTypeSelect");
                if (button.dataset.target === identifier) {
                    return;
                }
            }
            if (_pageHeader.classList.contains("searchBarForceOpen")) {
                return;
            }
            closeSearchBar();
        });
    }
    /**
     * Opens the search bar.
     */
    function openSearchBar() {
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
        _searchInput.focus();
        window.setTimeout(() => {
            _searchInput.selectionStart = _searchInput.selectionEnd = _searchInput.value.length;
        }, 1);
    }
    exports.openSearchBar = openSearchBar;
    /**
     * Closes the search bar.
     */
    function closeSearchBar() {
        _pageHeader.classList.remove("searchBarOpen");
        _userPanelSearchButton === null || _userPanelSearchButton === void 0 ? void 0 : _userPanelSearchButton.parentElement.classList.remove("open");
        ["bottom", "left", "right", "top"].forEach((propertyName) => {
            _pageHeaderSearch.style.removeProperty(propertyName);
        });
        _searchInput.blur();
        // close the scope selection
        const scope = _pageHeaderSearch.querySelector(".pageHeaderSearchType");
        Simple_1.default.close(scope.id);
    }
    exports.closeSearchBar = closeSearchBar;
    /**
     * Initializes the sticky page header handler.
     */
    function init() {
        initSearchBar();
        UiScreen.on("screen-md-down", {
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
        EventHandler.add("com.woltlab.wcf.Search", "close", closeSearchBar);
    }
    exports.init = init;
});
