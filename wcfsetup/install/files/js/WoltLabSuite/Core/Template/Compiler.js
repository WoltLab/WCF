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
define(["require", "exports", "tslib", "../Template.grammar"], function (require, exports, tslib_1, parser) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.compile = void 0;
    parser = tslib_1.__importStar(parser);
    /**
     * Compiles the given template.
     */
    function compile(template) {
        const compiled = `var tmp = {};
    for (var key in v) tmp[key] = v[key];
    v = tmp;
    v.__wcf = window.WCF; v.__window = window;
    return ${parser.parse(template)}
    `;
        // eslint-disable-next-line @typescript-eslint/no-implied-eval
        return new Function("Language", "h", "v", compiled);
    }
    exports.compile = compile;
});
