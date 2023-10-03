define(["require", "exports", "tslib", "./Group"], function (require, exports, tslib_1, Group_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.MenuBuilder = void 0;
    Group_1 = tslib_1.__importDefault(Group_1);
    class MenuBuilder {
        #menu;
        constructor(menu) {
            this.#menu = menu;
        }
        addGroup(callback) {
            const group = new Group_1.default(this.#menu);
            callback(group);
            return this;
        }
        addItem(value, label) {
            const item = document.createElement("woltlab-core-menu-item");
            item.value = value;
            item.textContent = label;
            this.#menu.append(item);
            return this;
        }
        addItemWithHtml(value, html) {
            const item = document.createElement("woltlab-core-menu-item");
            item.value = value;
            item.innerHTML = html;
            this.#menu.append(item);
            return this;
        }
        addDivider() {
            const divider = document.createElement("woltlab-core-menu-separator");
            this.#menu.append(divider);
            return this;
        }
        finalize() {
            return this.#menu;
        }
    }
    exports.MenuBuilder = MenuBuilder;
    exports.default = MenuBuilder;
});
