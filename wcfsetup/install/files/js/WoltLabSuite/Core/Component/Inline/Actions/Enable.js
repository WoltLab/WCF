/**
 * Inline editor action to enable an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Enable = void 0;
    class Enable {
        inlineEditor;
        endpoint;
        constructor(inlineEditor, endpoint) {
            this.inlineEditor = inlineEditor;
            this.endpoint = endpoint;
        }
        get item() {
            return {
                label: "wcf.global.button.enable",
                callback: async () => {
                    // TODO
                },
            };
        }
        isVisible() {
            return (this.inlineEditor.getPermissions()["canEnable"] &&
                !this.inlineEditor.getState("isDeleted") &&
                this.inlineEditor.getState("isDisabled"));
        }
    }
    exports.Enable = Enable;
});
