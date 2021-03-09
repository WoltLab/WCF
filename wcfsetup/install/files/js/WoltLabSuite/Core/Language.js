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
        Object.entries(object).forEach(([key, value]) => {
            add(key, value);
        });
    }
    exports.addObject = addObject;
    /**
     * Adds a single language item to the store.
     */
    function add(key, value) {
        if (typeof value === "string") {
            Store_1.add(key, compile(value));
        }
        else {
            // Historically a few items that are added to the language store do not represent actual phrases, but
            // instead contain a collection (i.e. Array) of items. Most notably these are entries related to date
            // processing, containg lists of localized month / weekday names.
            //
            // Despite this method technically only taking `string`s as the `value` we need to correctly handle
            // them which we do by simply storing a function that returns the value as-is.
            Store_1.add(key, function () {
                return value;
            });
        }
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
