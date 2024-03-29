/**
 * Wrapper around the web browser's various clipboard APIs.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.copyElementTextToClipboard = exports.copyTextToClipboard = void 0;
    async function copyTextToClipboard(text) {
        if (navigator.clipboard) {
            return navigator.clipboard.writeText(text);
        }
        throw new Error("navigator.clipboard is not supported.");
    }
    exports.copyTextToClipboard = copyTextToClipboard;
    async function copyElementTextToClipboard(element) {
        return copyTextToClipboard(element.textContent.replace(/\u200B/g, "").replace(/\u00A0/g, " "));
    }
    exports.copyElementTextToClipboard = copyElementTextToClipboard;
});
