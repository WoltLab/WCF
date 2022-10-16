/**
 * Generates plural phrases for the `plural` template plugin.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/I18n/Plural
 */

import * as StringUtil from "../StringUtil";

const pluralRules = new Intl.PluralRules(document.documentElement.lang);

type Parameters = {
  value: number;
  other: string;
} & {
  [rule in Intl.LDMLPluralRule]?: string;
} & {
  [number: number]: string;
};

/**
 * Returns the value for a `plural` element used in the template.
 *
 * @see    wcf\system\template\plugin\PluralFunctionTemplatePlugin::execute()
 */
export function getCategoryFromTemplateParameters(parameters: Parameters): string {
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

  const string = parameters[category]!;
  if (string.includes("#")) {
    return string.replace("#", StringUtil.formatNumeric(value));
  }

  return string;
}
