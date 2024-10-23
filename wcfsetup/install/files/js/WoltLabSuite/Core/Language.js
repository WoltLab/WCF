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
    exports.getPhrase = getPhrase;
    exports.registerPhrase = registerPhrase;
    exports.get = get;
    exports.add = add;
    exports.addObject = addObject;
    function getPhrase(key, parameters = {}) {
        return window.WoltLabLanguage.getPhrase(key, parameters);
    }
    function registerPhrase(key, value) {
        window.WoltLabLanguage.registerPhrase(key, value);
    }
    /**
     * @deprecated 6.0 Use `getPhrase()` instead
     */
    function get(key, parameters = {}) {
        return getPhrase(key, parameters);
    }
    /**
     * @deprecated 6.0 Use `registerPhrase()` instead
     */
    function add(key, value) {
        registerPhrase(key, value);
    }
    /**
     * @deprecated 6.0 Use `registerPhrase()` instead
     */
    function addObject(object) {
        Object.entries(object).forEach(([key, value]) => {
            registerPhrase(key, value);
        });
    }
});
