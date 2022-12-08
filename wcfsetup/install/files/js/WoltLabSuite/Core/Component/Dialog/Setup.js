/**
 * Helper module to expose a fluent API to create
 * dialogs through `dialogFactory()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Dialog/Setup
 * @since 6.0
 */
define(["require", "exports", "tslib", "./Controls", "../../Dom/Util", "../FormBuilder/Setup"], function (require, exports, tslib_1, Controls_1, DomUtil, Setup_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DialogSetup = void 0;
    Controls_1 = tslib_1.__importDefault(Controls_1);
    DomUtil = tslib_1.__importStar(DomUtil);
    Setup_1 = tslib_1.__importDefault(Setup_1);
    class DialogSetup {
        fromElement(element) {
            if (element instanceof HTMLTemplateElement) {
                element = element.content.cloneNode(true);
            }
            const dialog = document.createElement("woltlab-core-dialog");
            dialog.content.append(element);
            return new Controls_1.default(dialog);
        }
        fromId(id) {
            const element = document.getElementById(id);
            if (element === null) {
                throw new Error(`Unable to find the element identified by '${id}'.`);
            }
            return this.fromElement(element);
        }
        fromHtml(html) {
            const fragment = DomUtil.createFragmentFromHtml(html);
            if (fragment.childElementCount === 0 && fragment.textContent.trim() === "") {
                throw new TypeError("The provided HTML string was empty.");
            }
            return this.fromElement(fragment);
        }
        usingFormBuilder() {
            return new Setup_1.default();
        }
        withoutContent() {
            const dialog = document.createElement("woltlab-core-dialog");
            return new Controls_1.default(dialog);
        }
    }
    exports.DialogSetup = DialogSetup;
    exports.default = DialogSetup;
});
