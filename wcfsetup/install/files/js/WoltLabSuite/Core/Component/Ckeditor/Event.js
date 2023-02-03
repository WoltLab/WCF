define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.listenToCkeditor = exports.dispatchToCkeditor = void 0;
    class EventDispatcher {
        #element;
        constructor(element) {
            this.#element = element;
        }
        configuration(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:setup-configuration" /* EventNames.SetupConfiguration */, {
                detail: payload,
            }));
        }
        features(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:setup-features" /* EventNames.SetupFeatures */, {
                detail: payload,
            }));
        }
        ready(payload) {
            this.#element.dispatchEvent(new CustomEvent("ckeditor5:ready" /* EventNames.Ready */, {
                detail: payload,
            }));
        }
    }
    class EventListener {
        #element;
        constructor(element) {
            this.#element = element;
        }
        configuration(callback) {
            this.#element.addEventListener("ckeditor5:setup-configuration" /* EventNames.SetupConfiguration */, (event) => {
                callback(event.detail);
            }, { once: true });
        }
        features(callback) {
            this.#element.addEventListener("ckeditor5:setup-features" /* EventNames.SetupFeatures */, (event) => {
                callback(event.detail);
            }, { once: true });
        }
        ready(callback) {
            this.#element.addEventListener("ckeditor5:ready" /* EventNames.Ready */, (event) => {
                callback(event.detail);
            }, { once: true });
        }
    }
    function dispatchToCkeditor(element) {
        return new EventDispatcher(element);
    }
    exports.dispatchToCkeditor = dispatchToCkeditor;
    function listenToCkeditor(element) {
        return new EventListener(element);
    }
    exports.listenToCkeditor = listenToCkeditor;
});
