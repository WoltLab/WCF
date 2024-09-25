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
 * @since 6.0
 */
define(["require", "exports", "./View", "reflect-metadata"], function (require, exports, View_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DomElement = DomElement;
    exports.DomElementList = DomElementList;
    function DomElement(selector, options) {
        return function (target, propertyKey) {
            if (!(target instanceof View_1.DomView)) {
                throw new Error("@DomElement() is only supported on `DomView`");
            }
            const { nullable, type } = Object.assign({
                nullable: false,
                type: Reflect.getMetadata("design:type", target, propertyKey),
            }, options);
            Object.defineProperty(target, propertyKey, {
                configurable: true,
                get() {
                    const element = this.root.querySelector(selector);
                    if (element === null) {
                        if (nullable) {
                            return null;
                        }
                        throw new Error(`Unable to find an element with the selector '${selector}'.`);
                    }
                    if (!(element instanceof type)) {
                        throw new Error(`Expected an element of type '${type.name}' but found '${element.nodeName}'.`);
                    }
                    return element;
                },
            });
        };
    }
    function DomElementList(selector, options) {
        return function (target, propertyKey) {
            if (!(target instanceof View_1.DomView)) {
                throw new Error("@DomElementList() is only supported on `DomView`");
            }
            const reflectedType = Reflect.getMetadata("design:type", target, propertyKey);
            if (reflectedType.prototype !== Array.prototype) {
                throw new Error("The type must be an array of elements.");
            }
            const { type } = options;
            Object.defineProperty(target, propertyKey, {
                configurable: true,
                get() {
                    const elements = Array.from(this.root.querySelectorAll(selector));
                    for (const element of elements) {
                        if (!(element instanceof type)) {
                            throw new Error(`Expected an element of type '${type.name}' but found '${element.nodeName}'.`);
                        }
                    }
                    return elements;
                },
            });
        };
    }
});
