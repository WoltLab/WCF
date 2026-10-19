/**
 * Opens the language list of the page footer, a dropdown on large screens and
 * a modal sheet on small screens. `Component/LanguageSwitcher` handles the
 * selection of a language.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "focus-trap", "../../Ui/CloseOverlay", "../../Ui/Screen"], function (require, exports, tslib_1, focus_trap_1, CloseOverlay_1, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    CloseOverlay_1 = tslib_1.__importStar(CloseOverlay_1);
    let container;
    let toggle;
    let popover;
    let backdrop;
    let focusTrap;
    let isSheet = false;
    function isOpen() {
        return !popover.hidden;
    }
    function open() {
        if (isOpen()) {
            return;
        }
        // Closes dropdowns and other overlays.
        CloseOverlay_1.default.execute();
        popover.hidden = false;
        backdrop.hidden = false;
        toggle.setAttribute("aria-expanded", "true");
        isSheet = (0, Screen_1.is)("screen-md-down");
        if (isSheet) {
            popover.setAttribute("aria-modal", "true");
            (0, Screen_1.pageOverlayOpen)();
            (0, Screen_1.scrollDisable)();
            focusTrap.activate();
        }
        else {
            popover.querySelector('[aria-current="true"]')?.focus();
        }
    }
    // The focus returns to the toggle unless it has moved elsewhere, e.g. by clicking outside.
    function close(returnFocus = popover.contains(document.activeElement)) {
        if (!isOpen()) {
            return;
        }
        popover.hidden = true;
        backdrop.hidden = true;
        toggle.setAttribute("aria-expanded", "false");
        if (isSheet) {
            popover.removeAttribute("aria-modal");
            (0, Screen_1.pageOverlayClose)();
            (0, Screen_1.scrollEnable)();
            focusTrap.deactivate({ returnFocus });
        }
        else if (returnFocus) {
            toggle.focus();
        }
    }
    function setup() {
        const element = document.querySelector(".pageFooterLanguage");
        if (element === null) {
            return;
        }
        container = element;
        toggle = container.querySelector(".pageFooterLanguage__toggle");
        popover = container.querySelector(".pageFooterLanguage__popover");
        backdrop = container.querySelector(".pageFooterLanguage__backdrop");
        focusTrap = (0, focus_trap_1.createFocusTrap)(popover, {
            allowOutsideClick: true,
            escapeDeactivates: false,
            fallbackFocus: popover,
            initialFocus: () => popover.querySelector('[aria-current="true"]') ?? false,
            setReturnFocus: toggle,
        });
        // Safari does not focus a button on click, the focus would leave the open dropdown and close it
        // before the click on the toggle could.
        toggle.addEventListener("mousedown", (event) => {
            if (isOpen()) {
                event.preventDefault();
            }
        });
        toggle.addEventListener("click", () => {
            if (isOpen()) {
                close();
            }
            else {
                open();
            }
        });
        backdrop.addEventListener("click", () => close());
        container.querySelector(".pageFooterLanguage__close").addEventListener("click", () => close());
        container.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && isOpen()) {
                event.preventDefault();
                close();
            }
        });
        // The page navigates away, the focus must not jump back to the toggle meanwhile.
        popover.addEventListener("click", (event) => {
            if (event.target.closest("[data-switch-language]") !== null) {
                close(false);
            }
        });
        // The sheet traps the focus, the dropdown closes when the focus leaves it.
        container.addEventListener("focusout", (event) => {
            if (isOpen() && !isSheet && !container.contains(event.relatedTarget)) {
                close();
            }
        });
        document.addEventListener("click", (event) => {
            if (isOpen() && !container.contains(event.target)) {
                close();
            }
        });
        // Clicks bubbling up to the body are handled above, they would close the list right after opening it.
        CloseOverlay_1.default.add("WoltLabSuite/Core/Component/PageFooter/LanguageSwitch", (origin) => {
            if (origin !== CloseOverlay_1.Origin.Document) {
                close();
            }
        });
        // The dropdown and the sheet use different positions.
        (0, Screen_1.on)("screen-md-down", {
            match: () => close(),
            unmatch: () => close(),
        });
    }
});
