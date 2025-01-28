/**
 * Inline editor action to disable an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Disable = void 0;
    class Disable {
        inlineEditor;
        endpoint;
        constructor(inlineEditor, endpoint) {
            this.inlineEditor = inlineEditor;
            this.endpoint = endpoint;
        }
        get item() {
            return {
                label: "wcf.global.button.disable",
                callback: async () => {
                    const response = await disable(this.endpoint);
                    if (response.ok) {
                        this.inlineEditor.update({
                            isDisabled: true,
                        });
                    }
                },
            };
        }
        isVisible() {
            return (this.inlineEditor.getPermissions()["canEnable"] &&
                !this.inlineEditor.getState("isDeleted") &&
                !this.inlineEditor.getState("isDisabled"));
        }
    }
    exports.Disable = Disable;
    async function disable(endpoint) {
        try {
            await (0, Backend_1.prepareRequest)(endpoint).post().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
