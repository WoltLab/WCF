define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findUniqueElements = exports.wheneverSeen = void 0;
    let observer;
    const selectors = new Map();
    function findElements(node) {
        for (const [selector, callbacks] of selectors.entries()) {
            if (node.matches(selector)) {
                notifyCallbacks(node, callbacks);
            }
            const matches = node.querySelectorAll(selector);
            for (const element of matches) {
                notifyCallbacks(element, callbacks);
            }
        }
    }
    function notifyCallbacks(element, callbacks) {
        for (const callback of callbacks) {
            callback(element);
        }
    }
    function wheneverSeen(selector, callback) {
        if (!selectors.has(selector)) {
            selectors.set(selector, []);
        }
        selectors.get(selector).push(callback);
        findElements(document.body);
        if (observer === undefined) {
            observer = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    for (const node of mutation.addedNodes) {
                        if (node instanceof HTMLElement) {
                            findElements(node);
                        }
                    }
                }
            });
            observer.observe(document, { subtree: true, childList: true });
        }
    }
    exports.wheneverSeen = wheneverSeen;
    function findUniqueElements(selector, callback) {
        const knownElements = new WeakSet();
        wheneverSeen(selector, (element) => {
            if (!knownElements.has(element)) {
                knownElements.add(element);
                callback(element);
            }
        });
    }
    exports.findUniqueElements = findUniqueElements;
});
