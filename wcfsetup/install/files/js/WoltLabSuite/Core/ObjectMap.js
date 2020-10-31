/**
 * Simple `object` to `object` map using a WeakMap.
 *
 * If you're looking for a dictionary with string keys, please see `WoltLabSuite/Core/Dictionary`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  ObjectMap (alias)
 * @module  WoltLabSuite/Core/ObjectMap
 */
define(["require", "exports", "tslib", "./Core"], function (require, exports, tslib_1, Core) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    /** @deprecated 5.4 Use a `WeakMap` instead. */
    class ObjectMap {
        constructor() {
            this._map = new WeakMap();
        }
        /**
         * Sets a new key with given value, will overwrite an existing key.
         */
        set(key, value) {
            if (typeof key !== "object" || key === null) {
                throw new TypeError("Only objects can be used as key");
            }
            if (typeof value !== "object" || value === null) {
                throw new TypeError("Only objects can be used as value");
            }
            this._map.set(key, value);
        }
        /**
         * Removes a key from the map.
         */
        delete(key) {
            this._map.delete(key);
        }
        /**
         * Returns true if dictionary contains a value for given key.
         */
        has(key) {
            return this._map.has(key);
        }
        /**
         * Retrieves a value by key, returns undefined if there is no match.
         */
        get(key) {
            return this._map.get(key);
        }
    }
    Core.enableLegacyInheritance(ObjectMap);
    return ObjectMap;
});
