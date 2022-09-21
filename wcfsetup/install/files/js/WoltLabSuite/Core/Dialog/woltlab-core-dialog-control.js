define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.WoltlabCoreDialogControlElement = void 0;
    Language = tslib_1.__importStar(Language);
    class WoltlabCoreDialogControlElement extends HTMLElement {
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
                this.#primaryButton.type = "submit";
                this.#primaryButton.value = "primary";
                this.#primaryButton.autofocus = true;
                this.#primaryButton.classList.add("button", "buttonPrimary", "formControl__button", "formControl__button--primary");
                this.#primaryButton.textContent = this.primary;
                this.append(this.#primaryButton);
            }
            if (this.#cancelButton === undefined && this.cancel !== undefined) {
                this.#cancelButton = document.createElement("button");
                this.#cancelButton.type = "button";
                this.#cancelButton.value = "cancel";
                this.#cancelButton.classList.add("button", "formControl__button", "formControl__button--cancel");
                this.#cancelButton.textContent = this.cancel;
                this.#cancelButton.addEventListener("click", () => {
                    const event = new CustomEvent("cancel");
                    this.dispatchEvent(event);
                });
                this.append(this.#cancelButton);
            }
        }
        addEventListener(type, listener, options) {
            super.addEventListener(type, listener, options);
        }
    }
    exports.WoltlabCoreDialogControlElement = WoltlabCoreDialogControlElement;
    function setup() {
        const name = "woltlab-core-dialog-control";
        if (window.customElements.get(name) === undefined) {
            window.customElements.define(name, WoltlabCoreDialogControlElement);
        }
    }
    exports.setup = setup;
    exports.default = WoltlabCoreDialogControlElement;
});
