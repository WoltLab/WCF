/**
 * Dictionary implementation relying on an object or if supported on a Map to hold key => value data.
 *
 * If you're looking for a dictionary with object keys, please see `WoltLabSuite/Core/ObjectMap`.
 *
 * This is a legacy implementation, that does not implement all methods of `Map`, furthermore it has
 * the side effect of converting all numeric keys to string values, treating 1 === "1".
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dictionary (alias)
 * @module  WoltLabSuite/Core/Dictionary
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    /** @deprecated 5.4 Use a `Map` instead. */
    class Dictionary {
        constructor() {
            this._dictionary = new Map();
        }
        /**
         * Sets a new key with given value, will overwrite an existing key.
         */
        set(key, value) {
            this._dictionary.set(key.toString(), value);
        }
        /**
         * Removes a key from the dictionary.
         */
        delete(key) {
            return this._dictionary.delete(key.toString());
        }
        /**
         * Returns true if dictionary contains a value for given key and is not undefined.
         */
        has(key) {
            return this._dictionary.has(key.toString());
        }
        /**
         * Retrieves a value by key, returns undefined if there is no match.
         */
        get(key) {
            return this._dictionary.get(key.toString());
        }
        /**
         * Iterates over the dictionary keys and values, callback function should expect the
         * value as first parameter and the key name second.
         */
        forEach(callback) {
            if (typeof callback !== "function") {
                throw new TypeError("forEach() expects a callback as first parameter.");
            }
            this._dictionary.forEach(callback);
        }
        /**
         * Merges one or more Dictionary instances into this one.
         */
        merge(...dictionaries) {
            for (let i = 0, length = dictionaries.length; i < length; i++) {
                const dictionary = dictionaries[i];
                dictionary.forEach((value, key) => this.set(key, value));
            }
        }
        /**
         * Returns the object representation of the dictionary.
         */
        toObject() {
            const object = {};
            this._dictionary.forEach((value, key) => (object[key] = value));
            return object;
        }
        /**
         * Creates a new Dictionary based on the given object.
         * All properties that are owned by the object will be added
         * as keys to the resulting Dictionary.
         */
        static fromObject(object) {
            const result = new Dictionary();
            Object.keys(object).forEach((key) => {
                result.set(key, object[key]);
            });
            return result;
        }
        get size() {
            return this._dictionary.size;
        }
    }
    return Dictionary;
});
