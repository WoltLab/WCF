/**
 * Dropdown based style changer.
 *
 * The compact page footer renders the dropdown itself, other templates only provide
 * a `.jsButtonStyleChanger` button that is turned into a dropdown.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Styles/ChangeStyle", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, ChangeStyle_1, PromiseMutex_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.showDialog = showDialog;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    let initialized = false;
    const selectStyle = (0, PromiseMutex_1.promiseMutex)(async (styleId) => {
        await (0, ChangeStyle_1.changeStyle)(styleId);
        window.location.reload();
    });
    function createMenu(styles) {
        const list = document.createElement("ul");
        list.classList.add("scrollableDropdownMenu");
        for (const style of styles) {
            const button = document.createElement("button");
            button.type = "button";
            button.dataset.changeStyle = style.styleId.toString();
            button.textContent = style.name;
            const listItem = document.createElement("li");
            if (style.active) {
                listItem.classList.add("active");
                button.setAttribute("aria-current", "true");
            }
            listItem.append(button);
            list.append(listItem);
        }
        const menu = document.createElement("div");
        menu.classList.add("dropdownMenu");
        menu.dataset.dropdownAlignmentVertical = "top";
        menu.dataset.dropdownAlignmentHorizontal = "right";
        menu.append(list);
        return menu;
    }
    function initDropdown(button, styles) {
        const dropdown = document.createElement("div");
        dropdown.classList.add("dropdown");
        button.before(dropdown);
        dropdown.append(button, createMenu(styles));
        button.classList.add("dropdownToggle");
        Simple_1.default.init(button);
    }
    /**
     * Adds the style changer to the page footer.
     */
    function setup(styles) {
        if (initialized) {
            return;
        }
        initialized = true;
        document.querySelectorAll(".jsButtonStyleChanger:not(.dropdownToggle)").forEach((button) => {
            initDropdown(button, styles);
        });
        // The dropdown moves its menu into `.dropdownMenuContainer` when it opens.
        document.addEventListener("click", (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }
            const element = event.target.closest("[data-change-style]");
            if (element === null) {
                return;
            }
            event.preventDefault();
            if (element.getAttribute("aria-current") === "true") {
                return;
            }
            selectStyle(parseInt(element.dataset.changeStyle, 10));
        });
    }
    /**
     * Opens the style changer dropdown.
     *
     * @deprecated 6.3 The style changer is a dropdown that opens through its toggle button.
     */
    function showDialog(event) {
        event.preventDefault();
        event.stopPropagation();
        document.querySelector(".jsButtonStyleChanger")?.click();
    }
});
