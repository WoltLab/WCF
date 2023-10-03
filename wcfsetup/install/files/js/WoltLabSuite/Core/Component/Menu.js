define(["require", "exports", "tslib", "./Menu/Builder", "../Element/woltlab-core-menu", "../Element/woltlab-core-menu-group", "../Element/woltlab-core-menu-item", "../Element/woltlab-core-menu-separator"], function (require, exports, tslib_1, Builder_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.create = void 0;
    Builder_1 = tslib_1.__importDefault(Builder_1);
    function create(label) {
        const menu = document.createElement("woltlab-core-menu");
        menu.label = label;
        return new Builder_1.default(menu);
    }
    exports.create = create;
});
