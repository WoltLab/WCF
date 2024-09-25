/**
 * Visibility toggle for password input fields.
 *
 * @author Marcel Werk
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Dom/Change/Listener", "../Language"], function (require, exports, tslib_1, Listener_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Language = tslib_1.__importStar(Language);
    const _knownElements = new WeakSet();
    function setup() {
        initElements();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Password", () => initElements());
    }
    function initElements() {
        document.querySelectorAll("input[type=password]").forEach((input) => {
            if (!_knownElements.has(input)) {
                initElement(input);
            }
        });
    }
    function initElement(input) {
        _knownElements.add(input);
        const activeElement = document.activeElement;
        const inputAddon = document.createElement("div");
        inputAddon.classList.add("inputAddon");
        input.insertAdjacentElement("beforebegin", inputAddon);
        inputAddon.appendChild(input);
        const button = document.createElement("button");
        button.type = "button";
        button.title = Language.get("wcf.global.form.password.button.show");
        button.classList.add("button", "inputSuffix", "jsTooltip");
        button.setAttribute("aria-hidden", "true");
        inputAddon.appendChild(button);
        const icon = document.createElement("fa-icon");
        icon.setIcon("eye");
        button.appendChild(icon);
        button.addEventListener("click", () => {
            toggle(input, button, icon);
        });
        // Hide the password when the form is being submitted to prevent
        // it from being stored within the web browser's autocomplete list.
        // see https://github.com/WoltLab/WCF/issues/4554
        input.form?.addEventListener("submit", () => {
            if (input.type !== "password") {
                toggle(input, button, icon);
            }
        });
        if (activeElement === input) {
            input.focus();
        }
    }
    function toggle(input, button, icon) {
        if (input.type === "password") {
            icon.setIcon("eye-slash");
            button.dataset.tooltip = Language.get("wcf.global.form.password.button.hide");
            input.type = "text";
        }
        else {
            icon.setIcon("eye");
            button.dataset.tooltip = Language.get("wcf.global.form.password.button.show");
            input.type = "password";
        }
    }
});
