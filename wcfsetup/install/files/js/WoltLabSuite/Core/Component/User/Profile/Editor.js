/**
 * Edits the profile of a user in a dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, Dialog_1, Snackbar_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function editProfile(endpoint, onSuccess) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
        await onSuccess();
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(button, editOnInit, onSuccess) {
        const endpoint = button.dataset.endpoint;
        const openDialog = (0, PromiseMutex_1.promiseMutex)(() => editProfile(endpoint, onSuccess));
        button.addEventListener("click", () => openDialog());
        if (editOnInit) {
            openDialog();
        }
    }
});
