/**
 * Inline editor action to enable an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Result", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, Result_1, Backend_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Enable = void 0;
    class Enable {
        inlineEditor;
        endpoint;
        useFormBuilder;
        constructor(inlineEditor, endpoint, useFormBuilder = false) {
            this.inlineEditor = inlineEditor;
            this.endpoint = endpoint;
            this.useFormBuilder = useFormBuilder;
        }
        get item() {
            return {
                label: "wcf.global.button.enable",
                callback: async () => {
                    if (this.useFormBuilder) {
                        const response = await (0, Dialog_1.dialogFactory)()
                            .usingFormBuilder()
                            .fromEndpoint(this.endpoint.toString());
                        if (response.ok) {
                            this.inlineEditor.update({
                                isDisabled: response.result.isDisabled,
                            });
                        }
                    }
                    else {
                        if ((await enable(this.endpoint)).ok) {
                            this.inlineEditor.update({
                                isDisabled: false,
                            });
                        }
                    }
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
    async function enable(endpoint) {
        try {
            await (0, Backend_1.prepareRequest)(endpoint).post().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
