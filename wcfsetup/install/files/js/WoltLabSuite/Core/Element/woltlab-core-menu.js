define(["require", "exports", "tslib", "./woltlab-core-menu-group", "./woltlab-core-menu-item"], function (require, exports, tslib_1, woltlab_core_menu_group_1, woltlab_core_menu_item_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuElement = void 0;
    woltlab_core_menu_group_1 = tslib_1.__importDefault(woltlab_core_menu_group_1);
    woltlab_core_menu_item_1 = tslib_1.__importDefault(woltlab_core_menu_item_1);
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class WoltlabCoreMenuElement extends HTMLElement {
        #index = -1;
        #items = new Set();
        constructor() {
            super();
            this.addEventListener("keydown", (event) => {
                this.#keydown(event);
            });
        }
        connectedCallback() {
            const shadow = this.attachShadow({ mode: "open" });
            const slot = document.createElement("slot");
            shadow.append(slot);
            slot.addEventListener("slotchange", () => {
                for (const element of slot.assignedElements()) {
                    if (!(element instanceof HTMLAnchorElement) &&
                        !(element instanceof woltlab_core_menu_group_1.default) &&
                        !(element instanceof woltlab_core_menu_item_1.default)) {
                        element.remove();
                        continue;
                    }
                    if (this.#items.has(element)) {
                        continue;
                    }
                    if (element instanceof HTMLAnchorElement) {
                        if (element.href === "" || element.href === "#") {
                            throw new Error("Anchor elements may only use for actual navigation and must contain a valid 'href' target. Use a `<woltlab-core-menu-item>` button for non navigational items.", {
                                cause: { element },
                            });
                        }
                        element.setAttribute("role", "menuitem");
                    }
                    this.#items.add(element);
                    element.addEventListener("change", () => {
                        this.#items.forEach((item) => {
                            if (item === element) {
                                return;
                            }
                            if (item instanceof woltlab_core_menu_group_1.default) {
                                item.value = "";
                            }
                            else if (item instanceof woltlab_core_menu_item_1.default) {
                                item.selected = false;
                            }
                        });
                        const evt = new CustomEvent("change");
                        this.dispatchEvent(evt);
                        if (element instanceof woltlab_core_menu_item_1.default) {
                            const evt = new CustomEvent("close");
                            this.dispatchEvent(evt);
                        }
                    });
                }
            });
            this.setAttribute("role", "menu");
            this.label = this.getAttribute("label");
            this.#index = 0;
            this.#focusCurrentItem();
        }
        get label() {
            return this.getAttribute("label");
        }
        set label(label) {
            this.setAttribute("label", label);
            this.setAttribute("aria-label", label);
        }
        get value() {
            for (const item of Array.from(this.#items)) {
                if (item instanceof HTMLAnchorElement) {
                    continue;
                }
                const value = item.value;
                if (item instanceof woltlab_core_menu_group_1.default) {
                    if (value !== "") {
                        return value;
                    }
                }
                else if (item.selected) {
                    return value;
                }
            }
            return "";
        }
        #keydown(event) {
            const { code, key } = event;
            // Ignore any keystrokes that are most likely keyboard shortcuts.
            if (event.altKey !== false || event.ctrlKey !== false || event.metaKey !== false) {
                return;
            }
            if (code === "ArrowDown") {
                this.#index++;
                this.#focusCurrentItem();
                event.preventDefault();
                return;
            }
            if (code === "ArrowUp") {
                this.#index--;
                this.#focusCurrentItem();
                event.preventDefault();
                return;
            }
            if (code === "End") {
                this.#index = this.#getItems().length - 1;
                this.#focusCurrentItem();
                event.preventDefault();
                return;
            }
            if (code === "Home") {
                this.#index = 0;
                this.#focusCurrentItem();
                event.preventDefault();
                return;
            }
            if (key.length !== 1) {
                return;
            }
            const value = event.key.toLowerCase();
            const newIndex = this.#getItems().findIndex((item) => {
                return item.textContent.trim().toLowerCase().startsWith(value);
            });
            if (newIndex !== -1) {
                this.#index = newIndex;
                this.#focusCurrentItem();
                event.preventDefault();
            }
        }
        #focusCurrentItem() {
            const items = this.#getItems();
            if (items.length === 0) {
                throw new Error("There are no focusable items");
            }
            if (this.#index < 0) {
                this.#index = items.length - 1;
            }
            else if (this.#index >= items.length) {
                this.#index = 0;
            }
            items[this.#index].focus();
        }
        #getItems() {
            return Array.from(this.querySelectorAll("woltlab-core-menu-item:not([disabled])"));
        }
    }
    exports.WoltlabCoreMenuElement = WoltlabCoreMenuElement;
    window.customElements.define("woltlab-core-menu", WoltlabCoreMenuElement);
    exports.default = WoltlabCoreMenuElement;
});
