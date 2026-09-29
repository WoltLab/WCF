/**
 * Manages the values of the hidden form inputs storing the XsrfToken.
 *
 * @author  Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.5
 */
define(["require", "exports", "../Core", "../Helper/Selector"], function (require, exports, Core_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function isInput(node) {
        return node.nodeName === "INPUT";
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".xsrfTokenInput", (node) => {
            if (!isInput(node)) {
                return;
            }
            node.value = (0, Core_1.getXsrfToken)();
            node.classList.add("xsrfTokenInputHandled");
        });
        // A guest's session, and with it the token, can be started by a request made
        // after the page was loaded, leaving the inputs with an outdated value.
        document.addEventListener("submit", (event) => {
            const form = event.target;
            const token = (0, Core_1.getXsrfToken)();
            form.querySelectorAll("input.xsrfTokenInput").forEach((input) => {
                input.value = token;
            });
        }, { capture: true });
    }
});
