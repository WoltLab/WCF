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
        const token = (0, Core_1.getXsrfToken)();
        (0, Selector_1.wheneverFirstSeen)(".xsrfTokenInput", (node) => {
            if (!isInput(node)) {
                return;
            }
            node.value = token;
            node.classList.add("xsrfTokenInputHandled");
        });
    }
});
