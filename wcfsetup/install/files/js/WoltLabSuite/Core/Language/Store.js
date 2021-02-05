/**
 * Handles the low level management of language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Language/Store
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.add = exports.get = void 0;
    const languageItems = new Map();
    /**
     * Fetches the language item specified by the given key.
     *
     * The given parameters are passed to the compiled Phrase.
     */
    function get(key, parameters = {}) {
        const value = languageItems.get(key);
        if (value === undefined) {
            return key;
        }
        return value(parameters);
    }
    exports.get = get;
    /**
     * Adds a single language item to the store.
     */
    function add(key, value) {
        languageItems.set(key, value);
    }
    exports.add = add;
});
