/**
 * Edits the profile of a user in a dialog and reloads the "About" tab afterwards.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Controller/User/Profile", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Ui/TabMenu"], function (require, exports, Dialog_1, Snackbar_1, Profile_1, PromiseMutex_1, TabMenu_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function editProfile(userId, endpoint) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
        // Reload the tab before selecting it, otherwise the select event would load
        // the content of a tab that has not been loaded yet a second time.
        await (0, Profile_1.reloadTabMenuContent)(userId, "about");
        (0, TabMenu_1.getTabMenu)("profileContent")?.select("about");
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(button, userId, editOnInit) {
        const endpoint = button.dataset.endpoint;
        const openDialog = (0, PromiseMutex_1.promiseMutex)(() => editProfile(userId, endpoint));
        button.addEventListener("click", () => openDialog());
        if (editOnInit) {
            openDialog();
        }
    }
});
