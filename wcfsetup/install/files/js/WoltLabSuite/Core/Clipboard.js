/**
 * Wrapper around the web browser's various clipboard APIs.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Clipboard
 */
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.copyElementTextToClipboard = exports.copyTextToClipboard = void 0;
    function copyTextToClipboard(text) {
        return __awaiter(this, void 0, void 0, function* () {
            if (navigator.clipboard) {
                return navigator.clipboard.writeText(text);
            }
            throw new Error("navigator.clipboard is not supported.");
        });
    }
    exports.copyTextToClipboard = copyTextToClipboard;
    function copyElementTextToClipboard(element) {
        return __awaiter(this, void 0, void 0, function* () {
            return copyTextToClipboard(element.textContent);
        });
    }
    exports.copyElementTextToClipboard = copyElementTextToClipboard;
});
