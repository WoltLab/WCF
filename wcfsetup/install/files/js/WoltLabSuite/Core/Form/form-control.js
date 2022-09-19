define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.FormControl = void 0;
    Language = tslib_1.__importStar(Language);
    class FormControl extends HTMLElement {
        #primaryButton;
        set primary(primary) {
            this.setAttribute("primary", primary);
        }
        get primary() {
            let label = this.getAttribute("default");
            if (!label) {
                label = Language.get("wcf.global.confirmation.confirm");
            }
            return label;
        }
        connectedCallback() {
            this.classList.add("formControl");
            if (!this.hasAttribute("default")) {
                this.setAttribute("default", "");
            }
            if (this.#primaryButton === undefined) {
                this.#primaryButton = document.createElement("button");
                this.#primaryButton.type = "button";
                this.#primaryButton.classList.add("button", "buttonPrimary", "formControl__button", "formControl__button--primary");
                this.#primaryButton.textContent = this.primary;
                this.append(this.#primaryButton);
            }
        }
    }
    exports.FormControl = FormControl;
    function setup() {
        const name = "form-control";
        if (window.customElements.get(name) === undefined) {
            window.customElements.define(name, FormControl);
        }
    }
    exports.setup = setup;
    exports.default = FormControl;
});
