/**
 * Handles the user avatar edit buttons.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor", "../Snackbar", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Form/Builder/Manager"], function (require, exports, tslib_1, PromiseMutex_1, Selector_1, Dialog_1, FileProcessor_1, Snackbar_1, Backend_1, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    let defaultAvatar = "";
    async function editAvatar(button) {
        defaultAvatar = button.dataset.defaultAvatar || "";
        const json = (await (0, Backend_1.prepareRequest)(button.dataset.editAvatar).get().fetchAsJson());
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(json.dialog).withoutControls();
        dialog.addEventListener("afterClose", () => {
            if (FormBuilderManager.hasForm(json.formId)) {
                FormBuilderManager.unregisterForm(json.formId);
            }
        });
        dialog.show(json.title);
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(`#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file img`, (img) => {
            img.classList.add("userAvatarImage");
            img.parentElement.classList.add("userAvatar");
        });
        const avatarForm = document.getElementById("avatarForm");
        if (avatarForm) {
            (0, FileProcessor_1.registerCallback)("wcf\\action\\UserAvatarAction_avatarFileID", (fileId) => {
                let link = defaultAvatar;
                if (fileId !== undefined) {
                    const file = document.querySelector(`#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file[file-id="${fileId}"]`);
                    link = file.link;
                }
                avatarForm.querySelector("img.userAvatarImage").src = link;
                document
                    .getElementById("wcf\\action\\UserAvatarAction_avatarFileIDContainer")
                    ?.closest("woltlab-core-dialog")
                    ?.close();
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
            });
        }
        else {
            (0, FileProcessor_1.registerCallback)("wcf\\action\\UserAvatarAction_avatarFileID", () => {
                document
                    .getElementById("wcf\\action\\UserAvatarAction_avatarFileIDContainer")
                    ?.closest("woltlab-core-dialog")
                    ?.close();
                window.location.reload();
            });
        }
        (0, Selector_1.wheneverFirstSeen)("[data-edit-avatar]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => editAvatar(button)));
        });
    }
});
