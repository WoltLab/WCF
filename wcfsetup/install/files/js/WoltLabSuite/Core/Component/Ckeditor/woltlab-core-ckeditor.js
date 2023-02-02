define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreCkeditorElement = void 0;
    class WoltlabCoreCkeditorElement extends HTMLElement {
        #features;
        #sourceElement;
        connectedCallback() {
            this.setAttribute("source", this.#sourceElement.id);
        }
        setSourceElement(element) {
            if (this.#sourceElement !== undefined) {
                throw new Error("Cannot initialize the editor element twice.");
            }
            this.#sourceElement = element;
            this.#sourceElement.insertAdjacentElement("beforebegin", this);
            this.append(this.#sourceElement);
        }
        setupFeatures(features) {
            if (this.#features !== undefined) {
                throw new Error("Cannot set the features of the editor, features have already been set.");
            }
            this.dispatchEvent(new CustomEvent("setup:features", {
                detail: features,
            }));
            Object.freeze(features);
            this.#features = features;
        }
        get features() {
            if (this.#features === undefined) {
                throw new Error("Cannot access the features before the initilization took place.");
            }
            return this.#features;
        }
        get source() {
            return this.getAttribute("source");
        }
        addEventListener(type, listener, options) {
            super.addEventListener(type, listener, options);
        }
    }
    exports.WoltlabCoreCkeditorElement = WoltlabCoreCkeditorElement;
    exports.default = WoltlabCoreCkeditorElement;
    window.customElements.define("woltlab-core-ckeditor", WoltlabCoreCkeditorElement);
});
