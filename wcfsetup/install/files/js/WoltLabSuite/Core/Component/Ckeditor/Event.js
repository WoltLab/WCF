define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.listenToCkeditor = exports.dispatchToCkeditor = void 0;
    class EventDispatcher {
        #element;
        constructor(element) {
            this.#element = element;
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
