/**
 * Template provides a template scripting compiler
 * similar to the PHP one of WoltLab Suite Core. It supports a limited set of
 * useful commands and compiles templates down to a pure JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as parser from "./Template.grammar.js";
import * as LanguageStore from "./LanguageStore.js";

function escapeHTML(string: string): string {
  return String(string).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatNumeric(string: string | number): string {
  return Number(string)
    .toLocaleString(document.documentElement.lang, {
      maximumFractionDigits: 2,
    })
    .replace("-", "\u2212");
}

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
  if (Object.hasOwn(parameters, value.toString())) {
    return parameters[value];
  }

  let category = pluralRules.select(value);
  if (parameters[category] === undefined) {
    category = "other";
  }

  const string = parameters[category]!;
  if (string.includes("#")) {
    return string.replace("#", formatNumeric(value));
  }

  return string;
}

type CompiledTemplate = (
  L: typeof LanguageStore,
  h: {
    escapeHTML: typeof escapeHTML;
    formatNumeric: typeof formatNumeric;
    selectPlural: typeof selectPlural;
  },
  v: object,
) => string;

/**
 * Compiles the given template.
 */
function compile(template: string): CompiledTemplate {
  const compiled = `var tmp = {};
    for (var key in v) tmp[key] = v[key];
    v = tmp;
    v.__wcf = window.WCF; v.__window = window;
    return ${parser.parse(template)}
    `;

  // eslint-disable-next-line @typescript-eslint/no-implied-eval
  return new Function("Language", "h", "v", compiled) as CompiledTemplate;
}

export class Template {
  private readonly compiled: CompiledTemplate;

  constructor(template: string) {
    try {
      this.compiled = compile(template);
    } catch (e) {
      if (e instanceof Error) {
        console.debug(e.message);
      }

      throw e;
    }
  }

  /**
   * Evaluates the Template using the given parameters.
   */
  fetch(v: object): string {
    return this.compiled(LanguageStore, { selectPlural, escapeHTML, formatNumeric }, v);
  }
}
