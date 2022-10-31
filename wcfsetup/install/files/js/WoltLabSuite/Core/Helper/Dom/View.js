/**
 * Helper class to provide access to a fragment of the
 * DOM for use with the decorators `@DomElement` and
 * `@DomElementList`.
 *
 * This base class is required to allow the decorators
 * to make assumptions about how to access the DOM,
 * preserving the scope instead of running selectors
 * against the entire document.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Helper/Dom/View
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DomView = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class DomView {
        #root;
        constructor(root) {
            this.#root = this.#createRootElement(root);
        }
        get root() {
            return this.#root;
        }
        #createRootElement(root) {
            if (root instanceof HTMLElement) {
                return root;
            }
            if (typeof root === "string") {
                root = Util_1.default.createFragmentFromHtml(root);
            }
            if (root.children.length === 1) {
                return root.children[0];
            }
            const div = document.createElement("div");
            div.append(root);
            return div;
        }
    }
    exports.DomView = DomView;
    exports.default = DomView;
});
