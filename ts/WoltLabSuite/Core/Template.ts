/**
 * Provides a high level wrapper around the Template/Compiler.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template
 */

import * as Core from "./Core";
import * as I18nPlural from "./I18n/Plural";
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

class Template {
  private compiled: CompiledTemplate;

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
    return this.compiled(StringUtil, LanguageStore, I18nPlural, v);
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

Core.enableLegacyInheritance(Template);

export = Template;
