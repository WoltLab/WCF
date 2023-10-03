define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuItemElement = void 0;
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class WoltlabCoreMenuItemElement extends HTMLElement {
        #checkmark;
        constructor() {
            super();
            this.addEventListener("click", () => {
                if (this.disabled) {
                    return;
                }
                const evt = new CustomEvent("beforeSelect", {
                    cancelable: true,
                });
                this.dispatchEvent(evt);
                if (!evt.defaultPrevented) {
                    this.selected = !this.selected;
                    const evt = new CustomEvent("change");
                    this.dispatchEvent(evt);
                }
            });
        }
        connectedCallback() {
            const shadow = this.attachShadow({ mode: "open" });
            const checkmarkSlot = document.createElement("slot");
            checkmarkSlot.name = "checkmark";
            shadow.append(checkmarkSlot);
            const defaultSlot = document.createElement("slot");
            defaultSlot.id = "slot";
            shadow.append(defaultSlot);
            this.tabIndex = -1;
            this.setAttribute("role", "menuitem");
        }
        get selected() {
            return this.hasAttribute("selected");
        }
        set selected(checked) {
            if (checked) {
                this.setAttribute("selected", "");
            }
            else {
                this.removeAttribute("selected");
            }
            this.setAttribute("aria-checked", String(checked === true));
        }
        get disabled() {
            return this.hasAttribute("disabled");
        }
        set disabled(disabled) {
            if (disabled) {
                this.setAttribute("disabled", "");
            }
            else {
                this.removeAttribute("disabled");
            }
            this.setAttribute("aria-disabled", String(disabled === true));
        }
        get value() {
            return this.getAttribute("value");
        }
        set value(value) {
            this.setAttribute("value", value);
        }
        setRole(role) {
            this.setAttribute("role", role);
            this.#updateAriaSelected();
            if (role === "menuitem") {
                this.#checkmark?.remove();
            }
            else if (role === "menuitemcheckbox") {
                if (this.#checkmark === undefined) {
                    this.#checkmark = document.createElement("fa-icon");
                    this.#checkmark.setIcon("check");
                    this.#checkmark.slot = "checkmark";
                }
                this.append(this.#checkmark);
            }
        }
        #updateAriaSelected() {
            const role = this.getAttribute("role");
            if (role === "menuitemcheckbox") {
                this.setAttribute("aria-checked", String(this.selected === true));
            }
        }
    }
    exports.WoltlabCoreMenuItemElement = WoltlabCoreMenuItemElement;
    window.customElements.define("woltlab-core-menu-item", WoltlabCoreMenuItemElement);
    exports.default = WoltlabCoreMenuItemElement;
});
