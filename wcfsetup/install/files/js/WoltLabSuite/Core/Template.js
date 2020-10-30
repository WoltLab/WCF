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
define(["require", "exports", "tslib", "./Template.grammar", "./StringUtil", "./Language", "./I18n/Plural"], function (require, exports, tslib_1, parser, StringUtil, Language, I18nPlural) {
    "use strict";
    parser = tslib_1.__importStar(parser);
    StringUtil = tslib_1.__importStar(StringUtil);
    Language = tslib_1.__importStar(Language);
    I18nPlural = tslib_1.__importStar(I18nPlural);
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
            if (Language === undefined) {
                // @ts-expect-error: This is required due to a circular dependency.
                Language = require("./Language");
            }
            if (StringUtil === undefined) {
                // @ts-expect-error: This is required due to a circular dependency.
                StringUtil = require("./StringUtil");
            }
            try {
                template = parser.parse(template);
                template =
                    "var tmp = {};\n" +
                        "for (var key in v) tmp[key] = v[key];\n" +
                        "v = tmp;\n" +
                        "v.__wcf = window.WCF; v.__window = window;\n" +
                        "return " +
                        template;
                this.fetch = new Function("StringUtil", "Language", "I18nPlural", "v", template).bind(undefined, StringUtil, Language, I18nPlural);
            }
            catch (e) {
                console.debug(e.message);
                throw e;
            }
        }
        /**
         * Evaluates the Template using the given parameters.
         *
         * @param  {object}  v  Parameters to pass to the template.
         */
        fetch(v) {
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
        set: function (value) {
            throw new Error("WCF.Template.callbacks is no longer supported");
        },
    });
    return Template;
});
