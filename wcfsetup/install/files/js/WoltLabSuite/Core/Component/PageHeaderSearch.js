/**
 * Opens the search of the `system_pageHeader` in place of the main menu.
 *
 * The suggestions, the search scope and the submission are handled by `Ui/Search/Page`.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
define(["require", "exports", "tslib", "../Ui/CloseOverlay", "../Ui/Dropdown/Simple", "../Dom/Util"], function (require, exports, tslib_1, CloseOverlay_1, Simple_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    CloseOverlay_1 = tslib_1.__importStar(CloseOverlay_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    let header;
    let search;
    let input;
    let button;
    function isOpen() {
        return header.classList.contains("searchBarOpen");
    }
    function open() {
        if (isOpen()) {
            return;
        }
        CloseOverlay_1.default.execute(CloseOverlay_1.Origin.Search);
        header.classList.add("searchBarOpen");
        button.setAttribute("aria-expanded", "true");
        button.querySelector("fa-icon").setIcon("xmark");
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }
    function close(returnFocus) {
        if (!isOpen()) {
            return;
        }
        header.classList.remove("searchBarOpen");
        button.setAttribute("aria-expanded", "false");
        button.querySelector("fa-icon").setIcon("magnifying-glass");
        if (returnFocus) {
            button.focus();
        }
    }
    function setup() {
        const element = document.getElementById("pageHeaderSearch");
        if (element === null) {
            return;
        }
        search = element;
        header = document.getElementById("pageHeader");
        input = document.getElementById("pageHeaderSearchInput");
        button = document.getElementById("userPanelSearchButton");
        button.addEventListener("click", (event) => {
            event.preventDefault();
            if (isOpen()) {
                close(true);
            }
            else {
                open();
            }
        });
        // The link has the role of a button, which is activated by the space key too.
        button.addEventListener("keydown", (event) => {
            if (event.key === " ") {
                event.preventDefault();
                button.click();
            }
        });
        const searchType = search.querySelector(".pageHeaderSearchType");
        const searchTypeId = Util_1.default.identify(searchType);
        const dropdownIds = [Util_1.default.identify(input.parentElement), searchTypeId];
        // The scope list is moved out of the search when it opens.
        Simple_1.default.getDropdownMenu(searchTypeId)?.addEventListener("click", (event) => {
            if (event.target instanceof Element && event.target.closest("a[data-object-type]") !== null) {
                input.focus();
            }
        });
        // The suggestions and the scope list live outside the search, Escape closes them before the search.
        search.addEventListener("keydown", (event) => {
            if (event.key !== "Escape") {
                return;
            }
            event.preventDefault();
            const openDropdownId = dropdownIds.find((id) => Simple_1.default.isOpen(id));
            if (openDropdownId !== undefined) {
                Simple_1.default.close(openDropdownId);
            }
            else {
                close(true);
            }
        });
        // `UiCloseOverlay` does not expose the target of a click, which is needed to spare the dropdowns of the search.
        document.addEventListener("click", (event) => {
            if (!isOpen()) {
                return;
            }
            const target = event.target;
            if (!(target instanceof Node) || !target.isConnected) {
                return;
            }
            if (search.contains(target) || button.contains(target)) {
                return;
            }
            if (dropdownIds.some((id) => Simple_1.default.getDropdownMenu(id)?.contains(target))) {
                return;
            }
            close(false);
        });
        // The suggestions and the scope selection are dropdowns that must not close the search.
        CloseOverlay_1.default.add("WoltLabSuite/Core/Component/PageHeaderSearch", (origin, identifier) => {
            if (origin === CloseOverlay_1.Origin.Document || origin === CloseOverlay_1.Origin.Search) {
                return;
            }
            if (origin === CloseOverlay_1.Origin.DropDown && identifier !== undefined) {
                const dropdown = document.getElementById(identifier);
                if (dropdown !== null && search.contains(dropdown)) {
                    return;
                }
            }
            close(false);
        });
    }
});
