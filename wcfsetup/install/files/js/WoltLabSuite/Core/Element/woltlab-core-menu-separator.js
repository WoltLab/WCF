define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuSeparatorElement = void 0;
    class WoltlabCoreMenuSeparatorElement extends HTMLElement {
        connectedCallback() {
            this.setAttribute("role", "separator");
        }
    }
    exports.WoltlabCoreMenuSeparatorElement = WoltlabCoreMenuSeparatorElement;
    exports.default = WoltlabCoreMenuSeparatorElement;
    window.customElements.define("woltlab-core-menu-separator", WoltlabCoreMenuSeparatorElement);
});
