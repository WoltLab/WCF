/**
 * Simple interface to work with reusable dropdowns that are not bound to a specific item.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Simple"], function (require, exports, tslib_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.getDropdownMenu = getDropdownMenu;
    exports.registerCallback = registerCallback;
    exports.toggleDropdown = toggleDropdown;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    const _dropdowns = new Map();
    let _ghostElementId = 0;
    /**
     * Returns dropdown name by internal identifier.
     */
    function getDropdownName(identifier) {
        if (!_dropdowns.has(identifier)) {
            throw new Error("Unknown dropdown identifier '" + identifier + "'");
        }
        return _dropdowns.get(identifier);
    }
    /**
     * Initializes a new reusable dropdown.
     */
    function init(identifier, menu) {
        if (_dropdowns.has(identifier)) {
            return;
        }
        const ghostElement = document.createElement("div");
        ghostElement.id = `reusableDropdownGhost${_ghostElementId++}`;
        Simple_1.default.initFragment(ghostElement, menu);
        _dropdowns.set(identifier, ghostElement.id);
    }
    /**
     * Returns the dropdown menu element.
     */
    function getDropdownMenu(identifier) {
        return Simple_1.default.getDropdownMenu(getDropdownName(identifier));
    }
    /**
     * Registers a callback invoked upon open and close.
     */
    function registerCallback(identifier, callback) {
        Simple_1.default.registerCallback(getDropdownName(identifier), callback);
    }
    /**
     * Toggles a dropdown.
     */
    function toggleDropdown(identifier, referenceElement) {
        Simple_1.default.toggleDropdown(getDropdownName(identifier), referenceElement);
    }
});
