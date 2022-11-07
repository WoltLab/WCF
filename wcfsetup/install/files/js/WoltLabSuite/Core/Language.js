/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addObject = exports.add = exports.get = void 0;
    function get(key, parameters = {}) {
        return window.WoltLabLanguage.get(key, parameters);
    }
    exports.get = get;
    function add(key, value) {
        window.WoltLabLanguage.add(key, value);
    }
    exports.add = add;
    function addObject(object) {
        window.WoltLabLanguage.addObject(object);
    }
    exports.addObject = addObject;
});
