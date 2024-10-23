/**
 * Manages user permissions.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.add = add;
    exports.addObject = addObject;
    exports.get = get;
    const _permissions = new Map();
    /**
     * Adds a single permission to the store.
     */
    function add(permission, value) {
        if (typeof value !== "boolean") {
            throw new TypeError("The permission value has to be boolean.");
        }
        _permissions.set(permission, value);
    }
    /**
     * Adds all the permissions in the given object to the store.
     */
    function addObject(object) {
        Object.keys(object).forEach((key) => add(key, object[key]));
    }
    /**
     * Returns the value of a permission.
     *
     * If the permission is unknown, false is returned.
     */
    function get(permission) {
        if (_permissions.has(permission)) {
            return _permissions.get(permission);
        }
        return false;
    }
});
