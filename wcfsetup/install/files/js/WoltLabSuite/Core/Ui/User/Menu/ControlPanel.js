/**
 * User menu for the control panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/ControlPanel
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../CloseOverlay", "./Manager", "focus-trap", "../../Alignment", "../../../Dom/Util"], function (require, exports, tslib_1, CloseOverlay_1, Manager_1, focus_trap_1, Alignment, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.getElement = void 0;
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    Alignment = tslib_1.__importStar(Alignment);
    Util_1 = tslib_1.__importDefault(Util_1);
    let button;
    let element;
    let focusTrap;
    let link;
    function open() {
        if (!element.hidden) {
            return;
        }
        CloseOverlay_1.default.execute();
        element.hidden = false;
        button.classList.add("open");
        link.setAttribute("aria-expanded", "true");
        focusTrap.activate();
        setAlignment(element, button);
    }
    function setAlignment(element, referenceElement) {
        Alignment.set(element, referenceElement, { horizontal: "right" });
        if (window.getComputedStyle(element).position === "fixed" && Util_1.default.getFixedParent(referenceElement) !== null) {
            const { top, height } = referenceElement.getBoundingClientRect();
            element.style.setProperty("top", `${top + height}px`);
        }
    }
    function close() {
        focusTrap.deactivate();
        element.hidden = true;
        button.classList.remove("open");
        link.setAttribute("aria-expanded", "false");
    }
    function getElement() {
        return element;
    }
    exports.getElement = getElement;
    let isInitialized = false;
    function setup() {
        if (!isInitialized) {
            button = document.getElementById("userMenu");
            element = button.querySelector(".userMenu");
            link = button.querySelector("a");
            CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/User/Menu/ControlPanel", () => close());
            (0, Manager_1.getContainer)().append(element);
            element.addEventListener("click", (event) => event.stopPropagation());
            window.addEventListener("resize", () => {
                if (element.hidden) {
                    return;
                }
                setAlignment(element, button);
            }, { passive: true });
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
            const logoutLink = element.querySelector(".userMenuFooterLink");
            logoutLink.addEventListener("click", (event) => {
                event.preventDefault();
                logoutLink.closest("form").submit();
            });
            isInitialized = true;
        }
    }
    exports.setup = setup;
});
