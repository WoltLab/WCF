define(["require", "exports", "tslib", "./woltlab-core-menu-group"], function (require, exports, tslib_1, woltlab_core_menu_group_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuItemElement = void 0;
    woltlab_core_menu_group_1 = tslib_1.__importDefault(woltlab_core_menu_group_1);
    class WoltlabCoreMenuItemElement extends HTMLElement {
        #type = 1 /* MenuItemType.Item */;
        connectedCallback() {
            const shadow = this.attachShadow({ mode: "open" });
            const defaultSlot = document.createElement("slot");
            shadow.append(defaultSlot);
            this.tabIndex = -1;
            this.disabled = this.hasAttribute("disabled");
            if (this.parentElement instanceof woltlab_core_menu_group_1.default) {
                this.#type = 0 /* MenuItemType.Checkbox */;
                this.setAttribute("role", "menuitemcheckbox");
                this.selected = this.hasAttribute("selected");
            }
            else {
                this.#type = 1 /* MenuItemType.Item */;
                this.setAttribute("role", "menuitem");
                this.removeAttribute("aria-checked");
            }
        }
        get selected() {
            if (this.#type !== 1 /* MenuItemType.Item */) {
                return false;
            }
            return this.hasAttribute("selected");
        }
        set selected(checked) {
            if (this.#type !== 0 /* MenuItemType.Checkbox */) {
                return;
            }
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
    }
    exports.WoltlabCoreMenuItemElement = WoltlabCoreMenuItemElement;
    exports.default = WoltlabCoreMenuItemElement;
    window.customElements.define("woltlab-core-menu-item", WoltlabCoreMenuItemElement);
});
