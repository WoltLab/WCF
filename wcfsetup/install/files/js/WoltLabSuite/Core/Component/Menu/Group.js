define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MenuGroup = void 0;
    class MenuGroup {
        #group;
        constructor(menu) {
            this.#group = document.createElement("woltlab-core-menu-group");
            menu.append(this.#group);
        }
        addItem(value, label) {
            const item = document.createElement("woltlab-core-menu-item");
            item.value = value;
            item.textContent = label;
            this.#group.append(item);
            return this;
        }
        addItemWithHtml(value, html) {
            const item = document.createElement("woltlab-core-menu-item");
            item.value = value;
            item.innerHTML = html;
            this.#group.append(item);
            return this;
        }
        addDivider() {
            const divider = document.createElement("woltlab-core-menu-separator");
            this.#group.append(divider);
            return this;
        }
    }
    exports.MenuGroup = MenuGroup;
    exports.default = MenuGroup;
});
