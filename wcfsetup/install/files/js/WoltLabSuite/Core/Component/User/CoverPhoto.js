/**
 * Handles the user cover photo edit buttons.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Form/Builder/Manager", "WoltLabSuite/Core/Event/Handler", "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor", "../Snackbar"], function (require, exports, tslib_1, PromiseMutex_1, Selector_1, Dialog_1, Backend_1, FormBuilderManager, Handler_1, FileProcessor_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    async function editCoverPhoto(button) {
        const json = (await (0, Backend_1.prepareRequest)(button.dataset.editCoverPhoto).get().fetchAsJson());
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(json.dialog).withoutControls();
        dialog.addEventListener("afterClose", () => {
            if (FormBuilderManager.hasForm(json.formId)) {
                FormBuilderManager.unregisterForm(json.formId);
            }
        });
        dialog.show(json.title);
    }
    function getDefaultCoverPhoto() {
        return document.querySelector("[data-default-cover-photo]")?.dataset.defaultCoverPhoto ?? "";
    }
    function setup() {
        (0, FileProcessor_1.registerCallback)("wcf\\action\\UserCoverPhotoAction_coverPhotoFileID", (fileId) => {
            const headerImage = document.querySelector(".userProfileHeader__coverPhotoImage");
            if (headerImage && parseInt(headerImage.dataset.objectId) === fileId) {
                // nothing changed
                return;
            }
            const file = document.querySelector(`#wcf\\\\action\\\\UserCoverPhotoAction_coverPhotoFileIDContainer woltlab-core-file[file-id="${fileId}"]`);
            const coverPhotoUrl = file?.link || getDefaultCoverPhoto();
            if (headerImage && coverPhotoUrl) {
                headerImage.src = coverPhotoUrl;
                headerImage.dataset.objectId = fileId?.toString() || "";
                document
                    .getElementById("wcf\\action\\UserCoverPhotoAction_coverPhotoFileIDContainer")
                    ?.closest("woltlab-core-dialog")
                    ?.close();
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
            (0, Handler_1.fire)("com.woltlab.wcf.user", "coverPhoto", {
                url: coverPhotoUrl,
            });
        });
        (0, Selector_1.wheneverFirstSeen)("[data-edit-cover-photo]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => editCoverPhoto(button)));
        });
    }
});
