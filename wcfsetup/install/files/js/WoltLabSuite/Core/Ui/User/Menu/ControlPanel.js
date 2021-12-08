/**
 * User menu for the control panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/ControlPanel
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../CloseOverlay", "./Manager", "focus-trap", "../../Alignment"], function (require, exports, tslib_1, CloseOverlay_1, Manager_1, focus_trap_1, Alignment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    CloseOverlay_1 = (0, tslib_1.__importDefault)(CloseOverlay_1);
    Alignment = (0, tslib_1.__importStar)(Alignment);
    const button = document.getElementById("userMenu");
    const element = button.querySelector(".userMenu");
    let focusTrap;
    const link = button.querySelector("a");
    function open() {
        if (!element.hidden) {
            return;
        }
        CloseOverlay_1.default.execute();
        element.hidden = false;
        button.classList.add("open");
        link.setAttribute("aria-expanded", "true");
        focusTrap.activate();
        Alignment.set(element, button, { horizontal: "right" });
    }
    function close() {
        focusTrap.deactivate();
        element.hidden = true;
        button.classList.remove("open");
        link.setAttribute("aria-expanded", "false");
    }
    let isInitialized = false;
    function setup() {
        if (!isInitialized) {
            CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/User/Menu/ControlPanel", () => close());
            (0, Manager_1.getContainer)().append(element);
            element.addEventListener("click", (event) => event.stopPropagation());
            button.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                if (element.hidden) {
                    open();
                }
                else {
                    close();
                }
            });
            focusTrap = (0, focus_trap_1.createFocusTrap)(element, {
                allowOutsideClick: true,
                escapeDeactivates() {
                    close();
                    return false;
                },
                fallbackFocus: element,
            });
            isInitialized = true;
        }
    }
    exports.setup = setup;
});
