define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.FormControl = void 0;
    Language = tslib_1.__importStar(Language);
    class FormControl extends HTMLElement {
        #cancelButton;
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
        set cancel(cancel) {
            if (cancel === undefined) {
                this.removeAttribute("cancel");
            }
            else {
                this.setAttribute("cancel", cancel);
            }
        }
        get cancel() {
            let label = this.getAttribute("cancel");
            if (label === null) {
                return undefined;
            }
            if (label === "") {
                label = Language.get("wcf.global.confirmation.cancel");
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
                this.#primaryButton.autofocus = true;
                this.#primaryButton.classList.add("button", "buttonPrimary", "formControl__button", "formControl__button--primary");
                this.#primaryButton.textContent = this.primary;
                this.append(this.#primaryButton);
            }
            if (this.#cancelButton === undefined && this.cancel !== undefined) {
                this.#cancelButton = document.createElement("button");
                this.#cancelButton.type = "button";
                this.#cancelButton.classList.add("button", "formControl__button", "formControl__button--cancel");
                this.#cancelButton.textContent = this.cancel;
                this.append(this.#cancelButton);
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
