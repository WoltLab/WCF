/**
 * Template provides a template scripting compiler
 * similar to the PHP one of WoltLab Suite Core. It supports a limited set of
 * useful commands and compiles templates down to a pure JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    const Template = window.WoltLabTemplate;
    return class extends Template {
    };
});
