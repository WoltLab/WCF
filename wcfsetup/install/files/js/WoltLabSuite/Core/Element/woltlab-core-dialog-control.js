define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = exports.WoltlabCoreDialogControlElement = void 0;
    Language = tslib_1.__importStar(Language);
    class WoltlabCoreDialogControlElement extends HTMLElement {
        #cancelButton;
        #extraButton;
        #primaryButton;
        set primary(primary) {
            this.setAttribute("primary", primary);
        }
        get primary() {
            let label = this.getAttribute("primary");
            if (!label) {
                label = Language.get("wcf.dialog.button.primary");
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
        set extra(extra) {
            if (extra === undefined) {
                this.removeAttribute("extra");
            }
            else {
                this.setAttribute("extra", extra);
            }
        }
        get extra() {
            const label = this.getAttribute("extra");
            if (label === null) {
                return undefined;
            }
            return label;
        }
        connectedCallback() {
            this.classList.add("formControl");
            if (!this.hasAttribute("primary")) {
                this.setAttribute("primary", "");
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
                const dialog = this.closest("woltlab-core-dialog");
                if (dialog) {
                    dialog.addEventListener("backdrop", (event) => {
                        event.preventDefault();
                        this.#cancelButton.click();
                    });
                }
            }
            if (this.#extraButton === undefined && this.extra !== undefined) {
                this.#extraButton = document.createElement("button");
                this.#extraButton.type = "button";
                this.#extraButton.value = "extra";
                this.#extraButton.classList.add("button", "formControl__button", "formControl__button--extra");
                this.#extraButton.textContent = this.extra;
                this.#extraButton.addEventListener("click", () => {
                    const event = new CustomEvent("extra");
                    this.dispatchEvent(event);
                });
                this.append(this.#extraButton);
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
