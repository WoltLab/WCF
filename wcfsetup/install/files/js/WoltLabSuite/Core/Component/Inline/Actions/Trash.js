/**
 * Inline editor action to trash an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result", "WoltLabSuite/Core/Component/Confirmation", "WoltLabSuite/Core/Language"], function (require, exports, Backend_1, Result_1, Confirmation_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Trash = void 0;
    class Trash {
        inlineEditor;
        endpoint;
        title;
        constructor(inlineEditor, title, endpoint) {
            this.inlineEditor = inlineEditor;
            this.endpoint = endpoint;
            this.title = title;
        }
        get item() {
            return {
                label: (0, Language_1.getPhrase)("wcf.global.button.trash"),
                callback: async () => {
                    const result = await (0, Confirmation_1.confirmationFactory)().softDelete(this.title, true);
                    if (!result.result) {
                        return;
                    }
                    const response = await trash(this.endpoint, result.reason);
                    if (response.ok) {
                        this.inlineEditor.update({
                            isDeleted: 1,
                            deleteNote: response.value,
                        });
                    }
                },
            };
        }
        isVisible() {
            return this.inlineEditor.getPermissions()["canTrash"] && !this.inlineEditor.getState("isDeleted");
        }
    }
    exports.Trash = Trash;
    async function trash(endpoint, reason) {
        let response;
        try {
            response = (await (0, Backend_1.prepareRequest)(endpoint)
                .post({
                reason,
            })
                .fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)(response.deleteNote);
    }
});
