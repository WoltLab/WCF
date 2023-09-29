define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuElement = void 0;
    class WoltlabCoreMenuElement extends HTMLElement {
        connectedCallback() {
            this.setAttribute("role", "menu");
            this.label = this.getAttribute("label");
        }
        get label() {
            return this.getAttribute("label");
        }
        set label(label) {
            this.setAttribute("label", label);
            this.setAttribute("aria-label", label);
        }
    }
    exports.WoltlabCoreMenuElement = WoltlabCoreMenuElement;
    exports.default = WoltlabCoreMenuElement;
    window.customElements.define("woltlab-core-menu", WoltlabCoreMenuElement);
});
