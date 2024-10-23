/**
 * Efficient lazy loader that executes a callback once a selector matches
 * for the first time and the document has finished loading.
 *
 * Designed for actions that do not require eager execution, such as
 * binding specific event listeners on runtime. It should not be used for
 * components that alter the visible UI to prevent layout shifts.
 *
 * Based on the work of GitHub‘s Catalyst library (MIT license).
 * See https://github.com/github/catalyst/blob/c7983581adffd88f059e3c70674350b4fea4ee47/src/lazy-define.ts
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.whenFirstSeen = whenFirstSeen;
    let observer;
    const selectors = new Map();
    const timers = new Map();
    const documentReady = new Promise((resolve) => {
        if (document.readyState === "loading") {
            document.addEventListener("readystatechange", () => resolve(), { once: true });
        }
        else {
            resolve();
        }
    });
    function testElement(element) {
        // Debounce the checks against the same target.
        if (timers.get(element) !== undefined) {
            window.cancelAnimationFrame(timers.get(element));
        }
        timers.set(element, window.requestAnimationFrame(() => {
            for (const selector of selectors.keys()) {
                // Check if the element itself or any of its descendants
                // matches the provided selector.
                if (element.matches(selector) || element.querySelector(selector) !== null) {
                    for (const callback of selectors.get(selector)) {
                        // Wait for the document to fully load before notifying
                        // the callbacks to avoid layout shifts during page load.
                        void documentReady.then(() => callback());
                    }
                    selectors.delete(selector);
                }
            }
            timers.delete(element);
        }));
    }
    function whenFirstSeen(selector, callback) {
        if (!selectors.has(selector)) {
            selectors.set(selector, []);
        }
        selectors.get(selector).push(callback);
        // Immediately schedule a check to find matching elements
        // that already exist in the document at call time.
        testElement(document.body);
        if (observer === undefined) {
            // Check for elements added to the document on runtime.
            observer = new MutationObserver((mutations) => {
                if (selectors.size === 0) {
                    return;
                }
                for (const mutation of mutations) {
                    for (const node of mutation.addedNodes) {
                        // Skip changes to SVG elements or text nodes.
                        if (node instanceof HTMLElement) {
                            testElement(node);
                        }
                    }
                }
            });
        }
        observer.observe(document, { subtree: true, childList: true });
    }
});
