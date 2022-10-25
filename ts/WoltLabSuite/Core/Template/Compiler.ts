/**
 * WoltLabSuite/Core/Template/Compiler provides a template scripting compiler
 * similar to the PHP one of WoltLab Suite Core. It supports a limited set of
 * useful commands and compiles templates down to a pure JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template/Compiler
 */

import * as parser from "../Template.grammar";
import type { escapeHTML, formatNumeric } from "../StringUtil";
import type { get as getLanguage } from "../Language";

interface TemplateLanguage {
  get: typeof getLanguage;
}

interface TemplateStringUtil {
  escapeHTML: typeof escapeHTML;
  formatNumeric: typeof formatNumeric;
}

export type CompiledTemplate = (
  S: TemplateStringUtil,
  L: TemplateLanguage,
  selectPlural: (object: object) => string,
  v: object,
) => string;

/**
 * Compiles the given template.
 */
export function compile(template: string): CompiledTemplate {
  const compiled = `var tmp = {};
    for (var key in v) tmp[key] = v[key];
    v = tmp;
    v.__wcf = window.WCF; v.__window = window;
    return ${parser.parse(template)}
    `;

  // eslint-disable-next-line @typescript-eslint/no-implied-eval
  return new Function("StringUtil", "Language", "selectPlural", "v", compiled) as CompiledTemplate;
}
