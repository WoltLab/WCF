define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreMenuGroupElement = void 0;
    class WoltlabCoreMenuGroupElement extends HTMLElement {
        connectedCallback() {
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
    }
    exports.WoltlabCoreMenuGroupElement = WoltlabCoreMenuGroupElement;
    exports.default = WoltlabCoreMenuGroupElement;
    window.customElements.define("woltlab-core-menu-group", WoltlabCoreMenuGroupElement);
});
