/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */
define(["require", "exports", "tslib", "./Template", "./Language/Store", "./Language/Store"], function (require, exports, tslib_1, Template_1, Store_1, Store_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.add = exports.addObject = exports.get = void 0;
    Template_1 = tslib_1.__importDefault(Template_1);
    Object.defineProperty(exports, "get", { enumerable: true, get: function () { return Store_2.get; } });
    /**
     * Adds all the language items in the given object to the store.
     */
    function addObject(object) {
        Object.keys(object).forEach((key) => {
            add(key, object[key]);
        });
    }
    exports.addObject = addObject;
    /**
     * Adds a single language item to the store.
     */
    function add(key, value) {
        Store_1.add(key, compile(value));
    }
    exports.add = add;
    /**
     * Compiles the given value into a phrase.
     */
    function compile(value) {
        try {
            const template = new Template_1.default(value);
            return template.fetch.bind(template);
        }
        catch (e) {
            return function () {
                return value;
            };
        }
    }
});
