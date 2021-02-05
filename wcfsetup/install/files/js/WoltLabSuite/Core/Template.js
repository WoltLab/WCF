/**
 * Provides a high level wrapper around the Template/Compiler.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template
 */
define(["require", "exports", "tslib", "./Core", "./I18n/Plural", "./Language/Store", "./StringUtil", "./Template/Compiler"], function (require, exports, tslib_1, Core, I18nPlural, LanguageStore, StringUtil, Compiler_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    I18nPlural = tslib_1.__importStar(I18nPlural);
    LanguageStore = tslib_1.__importStar(LanguageStore);
    StringUtil = tslib_1.__importStar(StringUtil);
    // @todo: still required?
    // work around bug in AMD module generation of Jison
    /*function Parser() {
      this.yy = {};
    }
    
    Parser.prototype = parser;
    parser.Parser = Parser;
    parser = new Parser();*/
    class Template {
        constructor(template) {
            try {
                this.compiled = Compiler_1.compile(template);
            }
            catch (e) {
                console.debug(e.message);
                throw e;
            }
        }
        /**
         * Evaluates the Template using the given parameters.
         */
        fetch(v) {
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
    return Template;
});
