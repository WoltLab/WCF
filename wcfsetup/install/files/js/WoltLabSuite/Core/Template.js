/**
 * Provides a high level wrapper around the Template/Compiler.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Template
 */
define(["require", "exports", "tslib", "./I18n/Plural", "./Language/Store", "./StringUtil", "./Template/Compiler"], function (require, exports, tslib_1, I18nPlural, LanguageStore, StringUtil, Compiler_1) {
    "use strict";
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
        compiled;
        constructor(template) {
            try {
                this.compiled = (0, Compiler_1.compile)(template);
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
    return Template;
});
