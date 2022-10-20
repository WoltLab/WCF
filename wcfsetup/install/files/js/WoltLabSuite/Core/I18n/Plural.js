/**
 * Generates plural phrases for the `plural` template plugin.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/I18n/Plural
 */
define(["require", "exports", "tslib", "../StringUtil"], function (require, exports, tslib_1, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getCategoryFromTemplateParameters = void 0;
    StringUtil = tslib_1.__importStar(StringUtil);
    const pluralRules = new Intl.PluralRules(document.documentElement.lang);
    /**
     * Returns the value for a `plural` element used in the template.
     *
     * @see    wcf\system\template\plugin\PluralFunctionTemplatePlugin::execute()
     */
    function getCategoryFromTemplateParameters(parameters) {
        if (!Object.hasOwn(parameters, "value")) {
            throw new Error("Missing parameter value");
        }
        if (!parameters.other) {
            throw new Error("Missing parameter other");
        }
        let value = parameters.value;
        if (Array.isArray(value)) {
            value = value.length;
        }
        // handle numeric attributes
        const numericAttribute = Object.keys(parameters).find((key) => {
            return key.toString() === parseInt(key).toString() && key.toString() === value.toString();
        });
        if (numericAttribute) {
            return numericAttribute;
        }
        let category = pluralRules.select(value);
        if (parameters[category] === undefined) {
            category = "other";
        }
        const string = parameters[category];
        if (string.includes("#")) {
            return string.replace("#", StringUtil.formatNumeric(value));
        }
        return string;
    }
    exports.getCategoryFromTemplateParameters = getCategoryFromTemplateParameters;
});
