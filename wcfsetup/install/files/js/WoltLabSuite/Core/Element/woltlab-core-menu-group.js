define(["require", "exports", "tslib", "./woltlab-core-menu-item"], function (require, exports, tslib_1, woltlab_core_menu_item_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuGroupElement = void 0;
    woltlab_core_menu_item_1 = tslib_1.__importDefault(woltlab_core_menu_item_1);
    class WoltlabCoreMenuGroupElement extends HTMLElement {
        #items = new Set();
        #value = "";
        connectedCallback() {
            const shadow = this.attachShadow({ mode: "open" });
            const slot = document.createElement("slot");
            slot.addEventListener("slotchange", () => {
                this.#items.clear();
                for (const element of slot.assignedElements()) {
                    if (!(element instanceof woltlab_core_menu_item_1.default)) {
                        element.remove();
                        continue;
                    }
                    this.#items.add(element);
                    element.setRole("menuitemcheckbox");
                    element.addEventListener("change", () => {
                        this.#updateValue();
                    });
                }
            });
            shadow.append(slot);
            this.setAttribute("role", "group");
            this.label = this.getAttribute("label");
        }
        get label() {
            return this.getAttribute("label");
        }
        set label(label) {
            this.setAttribute("label", label);
            this.setAttribute("aria-label", label);
        }
        get value() {
            return this.#value;
        }
        set value(value) {
            const values = value.split(",");
            this.#items.forEach((item) => {
                item.selected = values.includes(item.value);
            });
            this.#updateValue();
        }
        #updateValue() {
            this.#value = Array.from(this.#items)
                .filter((item) => item.selected)
                .map((item) => item.value)
                .join(",");
            this.setAttribute("value", this.#value);
        }
    }
    exports.WoltlabCoreMenuGroupElement = WoltlabCoreMenuGroupElement;
    exports.default = WoltlabCoreMenuGroupElement;
    window.customElements.define("woltlab-core-menu-group", WoltlabCoreMenuGroupElement);
});
