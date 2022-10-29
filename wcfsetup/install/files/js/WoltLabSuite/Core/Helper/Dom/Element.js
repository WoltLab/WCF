/**
 * Decorators to allow runtime access to DOM elements
 * in a fragment of the DOM without requiring to manually
 * fetch the elements. Depends on `DomView` to access
 * a scoped part of the DOM instead of running selectors
 * against the entire document.
 *
 * The decorators will create new getters on the prototype
 * to access the elements. This collides with the
 * `useDefineForClassFields` setting of TypeScript that
 * will emit properties for class fields.
 *
 * WARNING: You MUST prepend `declare` to your properties
 * that are upgraded with these decorators.
 *
 * Based on the ideas of GitHub’s Catalyst library.
 * https://github.com/github/catalyst
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Helper/Dom/Element
 * @since 6.0
 */
define(["require", "exports", "./View"], function (require, exports, View_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DomElementList = exports.DomElement = void 0;
    function DomElement(selector) {
        return function (target, propertyKey) {
            if (!(target instanceof View_1.DomView)) {
                throw new Error("@DomElement() is only supported on `DomView`");
            }
            Object.defineProperty(target, propertyKey, {
                configurable: true,
                get() {
                    const element = this.root.querySelector(selector);
                    if (element === null) {
                        throw new Error(`Unable to find an element with the selector '${selector}'.`);
                    }
                    return element;
                },
            });
        };
    }
    exports.DomElement = DomElement;
    function DomElementList(selector) {
        return function (target, propertyKey) {
            if (!(target instanceof View_1.DomView)) {
                throw new Error("@DomElement() is only supported on `DomView`");
            }
            Object.defineProperty(target, propertyKey, {
                configurable: true,
                get() {
                    return Array.from(this.root.querySelectorAll(selector));
                },
            });
        };
    }
    exports.DomElementList = DomElementList;
});
