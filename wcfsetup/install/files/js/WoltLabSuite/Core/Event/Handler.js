/**
 * Versatile event system similar to the WCF-PHP counter part.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  EventHandler (alias)
 * @module  WoltLabSuite/Core/Event/Handler
 */
define(["require", "exports", "tslib", "../Core", "../Devtools"], function (require, exports, tslib_1, Core, Devtools_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.removeAllBySuffix = exports.removeAll = exports.remove = exports.fire = exports.add = void 0;
    Core = tslib_1.__importStar(Core);
    Devtools_1 = tslib_1.__importDefault(Devtools_1);
    const _listeners = new Map();
    /**
     * Registers an event listener.
     */
    function add(identifier, action, callback) {
        if (typeof callback !== 'function') {
            throw new TypeError(`Expected a valid callback for '${action}'@'${identifier}'.`);
        }
        let actions = _listeners.get(identifier);
        if (actions === undefined) {
            actions = new Map();
            _listeners.set(identifier, actions);
        }
        let callbacks = actions.get(action);
        if (callbacks === undefined) {
            callbacks = new Map();
            actions.set(action, callbacks);
        }
        const uuid = Core.getUuid();
        callbacks.set(uuid, callback);
        return uuid;
    }
    exports.add = add;
    /**
     * Fires an event and notifies all listeners.
     */
    function fire(identifier, action, data) {
        var _a, _b;
        Devtools_1.default._internal_.eventLog(identifier, action);
        data = data || {};
        (_b = (_a = _listeners.get(identifier)) === null || _a === void 0 ? void 0 : _a.get(action)) === null || _b === void 0 ? void 0 : _b.forEach(callback => callback(data));
    }
    exports.fire = fire;
    /**
     * Removes an event listener, requires the uuid returned by add().
     */
    function remove(identifier, action, uuid) {
        var _a, _b;
        (_b = (_a = _listeners.get(identifier)) === null || _a === void 0 ? void 0 : _a.get(action)) === null || _b === void 0 ? void 0 : _b.delete(uuid);
    }
    exports.remove = remove;
    /**
     * Removes all event listeners for given action. Omitting the second parameter will
     * remove all listeners for this identifier.
     */
    function removeAll(identifier, action) {
        if (typeof action !== 'string')
            action = undefined;
        const actions = _listeners.get(identifier);
        if (actions === undefined) {
            return;
        }
        if (action === undefined) {
            _listeners.delete(identifier);
        }
        else {
            actions.delete(action);
        }
    }
    exports.removeAll = removeAll;
    /**
     * Removes all listeners registered for an identifier and ending with a special suffix.
     * This is commonly used to unbound event handlers for the editor.
     */
    function removeAllBySuffix(identifier, suffix) {
        const actions = _listeners.get(identifier);
        if (actions === undefined) {
            return;
        }
        suffix = '_' + suffix;
        const length = suffix.length * -1;
        actions.forEach((callbacks, action) => {
            if (action.substr(length) === suffix) {
                removeAll(identifier, action);
            }
        });
    }
    exports.removeAllBySuffix = removeAllBySuffix;
});
