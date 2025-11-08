/**
 * Handles the user avatar edit buttons.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor", "../Snackbar"], function (require, exports, PromiseMutex_1, Selector_1, Dialog_1, FileProcessor_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function editAvatar(button) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(button.dataset.editAvatar);
        if (ok) {
            const avatarForm = document.getElementById("avatarForm");
            if (avatarForm) {
                const img = avatarForm.querySelector("img.userAvatarImage");
                if (img.src === result.avatar) {
                    return;
                }
                // In the ACP, the form should not be reloaded after changing the avatar.
                img.src = result.avatar;
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
            }
            else {
                window.location.reload();
            }
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(`#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file img`, (img) => {
            img.classList.add("userAvatarImage");
            img.parentElement.classList.add("userAvatar");
        });
        const avatarForm = document.getElementById("avatarForm");
        if (avatarForm) {
            (0, FileProcessor_1.registerCallback)("wcf\\action\\UserAvatarAction_avatarFileID", (fileId) => {
                if (!fileId) {
                    return;
                }
                const file = document.querySelector(`#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file[file-id="${fileId}"]`);
                avatarForm.querySelector("img.userAvatarImage").src = file.link;
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
            });
        }
        else {
            (0, FileProcessor_1.registerCallback)("wcf\\action\\UserAvatarAction_avatarFileID", (fileId) => {
                if (fileId === undefined) {
                    return;
                }
                document
                    .getElementById("wcf\\action\\UserAvatarAction_avatarFileIDContainer")
                    ?.closest("woltlab-core-dialog")
                    ?.querySelector(".dialog__control__button--primary")
                    ?.click();
            });
        }
        (0, Selector_1.wheneverFirstSeen)("[data-edit-avatar]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => editAvatar(button)));
        });
    }
});
