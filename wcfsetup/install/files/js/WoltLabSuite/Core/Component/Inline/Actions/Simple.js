/**
 * Inline editor action to restore an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Result", "WoltLabSuite/Core/Language"], function (require, exports, Backend_1, Result_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Simple = void 0;
    class Simple {
        inlineEditor;
        endpoint;
        label;
        constructor(inlineEditor, label, endpoint) {
            this.inlineEditor = inlineEditor;
            this.endpoint = endpoint;
            this.label = label;
        }
        get item() {
            return {
                label: (0, Language_1.getPhrase)(this.label),
                callback: async () => {
                    const response = await request(this.endpoint);
                    if (response.ok) {
                        this.responseOk();
                    }
                },
            };
        }
    }
    exports.Simple = Simple;
    async function request(endpoint) {
        try {
            await (0, Backend_1.prepareRequest)(endpoint).post().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
