/**
 * Simple API to store and invoke multiple callbacks per identifier.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  CallbackList (alias)
 * @module  WoltLabSuite/Core/CallbackList
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    class CallbackList {
        constructor() {
            this._callbacks = new Map();
        }
        /**
         * Adds a callback for given identifier.
         */
        add(identifier, callback) {
            if (typeof callback !== 'function') {
                throw new TypeError('Expected a valid callback as second argument for identifier \'' + identifier + '\'.');
            }
            if (!this._callbacks.has(identifier)) {
                this._callbacks.set(identifier, []);
            }
            this._callbacks.get(identifier).push(callback);
        }
        /**
         * Removes all callbacks registered for given identifier
         */
        remove(identifier) {
            this._callbacks.delete(identifier);
        }
        /**
         * Invokes callback function on each registered callback.
         */
        forEach(identifier, callback) {
            var _a;
            if (identifier === null) {
                this._callbacks.forEach((callbacks, identifier) => {
                    callbacks.forEach(callback);
                });
            }
            else {
                (_a = this._callbacks.get(identifier)) === null || _a === void 0 ? void 0 : _a.forEach(callback);
            }
        }
    }
    return CallbackList;
});
