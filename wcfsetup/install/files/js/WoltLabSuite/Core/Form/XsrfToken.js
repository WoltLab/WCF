/**
 * Manages the values of the hidden form inputs storing the XsrfToken.
 *
 * @author  Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/XsrfToken
 * @since 5.5
 */
define(["require", "exports", "../Core"], function (require, exports, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function isInput(node) {
        return node.nodeName === "INPUT";
    }
    function createObserver() {
        const observer = new MutationObserver((mutations) => {
            const token = (0, Core_1.getXsrfToken)();
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (!isInput(node)) {
                        return;
                    }
                    if (!node.classList.contains("xsrfTokenInput")) {
                        return;
                    }
                    node.value = token;
                    node.classList.add("xsrfTokenInputHandled");
                });
            });
        });
        observer.observe(document, { subtree: true, childList: true });
    }
    function setup() {
        createObserver();
        const token = (0, Core_1.getXsrfToken)();
        document.querySelectorAll(".xsrfTokenInput").forEach((node) => {
            if (!isInput(node)) {
                return;
            }
            node.value = token;
            node.classList.add("xsrfTokenInputHandled");
        });
    }
    exports.setup = setup;
});
