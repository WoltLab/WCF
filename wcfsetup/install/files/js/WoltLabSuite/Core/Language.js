/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "./Template"], function (require, exports, Template_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.get = exports.add = exports.addObject = void 0;
    Template_1 = __importDefault(Template_1);
    const _languageItems = new Map();
    /**
     * Adds all the language items in the given object to the store.
     */
    function addObject(object) {
        Object.keys(object).forEach(key => {
            _languageItems.set(key, object[key]);
        });
    }
    exports.addObject = addObject;
    /**
     * Adds a single language item to the store.
     */
    function add(key, value) {
        _languageItems.set(key, value);
    }
    exports.add = add;
    /**
     * Fetches the language item specified by the given key.
     * If the language item is a string it will be evaluated as
     * WoltLabSuite/Core/Template with the given parameters.
     *
     * @param  {string}  key    Language item to return.
     * @param  {Object=}  parameters  Parameters to provide to WoltLabSuite/Core/Template.
     * @return  {string}
     */
    function get(key, parameters) {
        let value = _languageItems.get(key);
        if (value === undefined) {
            return key;
        }
        // fetch Template, as it cannot be provided because of a circular dependency
        if (Template_1.default === undefined) { //@ts-ignore
            Template_1.default = require('./Template');
        }
        if (typeof value === 'string') {
            // lazily convert to WCF.Template
            try {
                _languageItems.set(key, new Template_1.default(value));
            }
            catch (e) {
                _languageItems.set(key, new Template_1.default('{literal}' + value.replace(/{\/literal}/g, '{/literal}{ldelim}/literal}{literal}') + '{/literal}'));
            }
            value = _languageItems.get(key);
        }
        if (value instanceof Template_1.default) {
            value = value.fetch(parameters || {});
        }
        return value;
    }
    exports.get = get;
});
