/**
 * Inline editor action to restore an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Inline/Actions/Simple"], function (require, exports, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Restore = void 0;
    class Restore extends Simple_1.Simple {
        constructor(inlineEditor, endpoint) {
            super(inlineEditor, "wcf.global.button.restore", endpoint);
        }
        responseOk() {
            this.inlineEditor.update({
                isDeleted: 0,
            });
        }
        isVisible() {
            return this.inlineEditor.getPermissions()["canRestore"] && this.inlineEditor.getState("isDeleted");
        }
    }
    exports.Restore = Restore;
});
