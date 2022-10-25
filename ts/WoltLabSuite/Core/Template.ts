/**
 * Provides a high level wrapper around the Template/Compiler.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template
 */

import * as LanguageStore from "./Language/Store";
import * as StringUtil from "./StringUtil";
import { compile, CompiledTemplate } from "./Template/Compiler";

// @todo: still required?
// work around bug in AMD module generation of Jison
/*function Parser() {
  this.yy = {};
}

Parser.prototype = parser;
parser.Parser = Parser;
parser = new Parser();*/

const pluralRules = new Intl.PluralRules(document.documentElement.lang);

type PluralParameters = {
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
function selectPlural(parameters: PluralParameters): string {
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

class Template {
  private readonly compiled: CompiledTemplate;

  constructor(template: string) {
    try {
      this.compiled = compile(template);
    } catch (e) {
      console.debug(e.message);
      throw e;
    }
  }

  /**
   * Evaluates the Template using the given parameters.
   */
  fetch(v: object): string {
    return this.compiled(StringUtil, LanguageStore, selectPlural, v);
  }
}

export = Template;
