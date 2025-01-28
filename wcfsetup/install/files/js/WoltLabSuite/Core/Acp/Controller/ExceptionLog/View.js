/**
 * Shows the dialog that shows exception details.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Api/Exceptions/RenderException", "WoltLabSuite/Core/Clipboard", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language"], function (require, exports, RenderException_1, Clipboard_1, Dialog_1, PromiseMutex_1, Selector_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function showDialog(button) {
        const response = await (0, RenderException_1.renderException)(button.closest("tr").dataset.objectId);
        if (!response.ok) {
            return;
        }
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.value.template).withoutControls();
        dialog.content.querySelector(".jsCopyButton")?.addEventListener("click", () => {
            void (0, Clipboard_1.copyTextToClipboard)(dialog.content.querySelector(".jsCopyException").value);
        });
        dialog.show((0, Language_1.getPhrase)("wcf.acp.exceptionLog.exception.message"));
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".jsExceptionLogEntry", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => showDialog(button)));
        });
    }
});
