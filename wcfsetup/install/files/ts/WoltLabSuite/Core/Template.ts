/**
 * WoltLabSuite/Core/Template provides a template scripting compiler similar
 * to the PHP one of WoltLab Suite Core. It supports a limited
 * set of useful commands and compiles templates down to a pure
 * JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template
 */

import * as parser from "./Template.grammar";
import * as StringUtil from "./StringUtil";
import * as Language from "./Language";
import * as I18nPlural from "./I18n/Plural";

// @todo: still required?
// work around bug in AMD module generation of Jison
/*function Parser() {
  this.yy = {};
}

Parser.prototype = parser;
parser.Parser = Parser;
parser = new Parser();*/

class Template {
  constructor(template: string) {
    if (Language === undefined) {
      // @ts-expect-error: This is required due to a circular dependency.
      Language = require("./Language");
    }
    if (StringUtil === undefined) {
      // @ts-expect-error: This is required due to a circular dependency.
      StringUtil = require("./StringUtil");
    }

    try {
      template = parser.parse(template) as string;
      template =
        "var tmp = {};\n" +
        "for (var key in v) tmp[key] = v[key];\n" +
        "v = tmp;\n" +
        "v.__wcf = window.WCF; v.__window = window;\n" +
        "return " +
        template;

      // eslint-disable-next-line @typescript-eslint/no-implied-eval
      this.fetch = new Function("StringUtil", "Language", "I18nPlural", "v", template).bind(
        undefined,
        StringUtil,
        Language,
        I18nPlural,
      );
    } catch (e) {
      console.debug(e.message);
      throw e;
    }
  }

  /**
   * Evaluates the Template using the given parameters.
   */
  fetch(_v: object): string {
    // this will be replaced in the init function
    throw new Error("This Template is not initialized.");
  }
}

Object.defineProperty(Template, "callbacks", {
  enumerable: false,
  configurable: false,
  get: function () {
    throw new Error("WCF.Template.callbacks is no longer supported");
  },
  set: function (_value) {
    throw new Error("WCF.Template.callbacks is no longer supported");
  },
});

export = Template;
