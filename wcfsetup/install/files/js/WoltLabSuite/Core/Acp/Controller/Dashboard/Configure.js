/**
 * Shows the dialog that allows the user to configure the dashboard boxes.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, Dialog_1, PromiseMutex_1, Notification_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function showDialog(url) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url);
        if (ok) {
            (0, Notification_1.show)(undefined, () => {
                window.location.reload();
            });
        }
    }
    function setup(button) {
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => showDialog(button.dataset.url)));
    }
    exports.setup = setup;
});
