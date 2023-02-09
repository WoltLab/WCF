/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addObject = exports.add = exports.get = exports.registerPhrase = exports.getPhrase = void 0;
    function getPhrase(key, parameters = {}) {
        return window.WoltLabLanguage.getPhrase(key, parameters);
    }
    exports.getPhrase = getPhrase;
    function registerPhrase(key, value) {
        window.WoltLabLanguage.registerPhrase(key, value);
    }
    exports.registerPhrase = registerPhrase;
    /**
     * @deprecated 6.0 Use `getPhrase()` instead
     */
    function get(key, parameters = {}) {
        return getPhrase(key, parameters);
    }
    exports.get = get;
    /**
     * @deprecated 6.0 Use `registerPhrase()` instead
     */
    function add(key, value) {
        registerPhrase(key, value);
    }
    exports.add = add;
    /**
     * @deprecated 6.0 Use `registerPhrase()` instead
     */
    function addObject(object) {
        Object.entries(object).forEach(([key, value]) => {
            registerPhrase(key, value);
        });
    }
    exports.addObject = addObject;
});
