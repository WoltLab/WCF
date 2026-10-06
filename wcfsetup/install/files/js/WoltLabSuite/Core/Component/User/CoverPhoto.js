/**
 * Handles the user cover photo edit buttons.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Form/Builder/Manager", "WoltLabSuite/Core/Event/Handler", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor", "../Snackbar"], function (require, exports, tslib_1, PromiseMutex_1, Selector_1, Dialog_1, Backend_1, FormBuilderManager, Handler_1, Language_1, Util_1, StringUtil_1, FileProcessor_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    Util_1 = tslib_1.__importDefault(Util_1);
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
    function updateAcpCoverPhotoPreview(fileId, fileUrl) {
        const coverPhotoPreview = document.getElementById("coverPhotoPreview");
        const coverPhotoNotice = document.getElementById("coverPhotoNotice");
        if (fileUrl) {
            const coverPhotoStyle = `url("${fileUrl}")`;
            if (coverPhotoPreview) {
                coverPhotoPreview.style.backgroundImage = coverPhotoStyle;
                coverPhotoPreview.dataset.objectId = fileId?.toString() || "";
            }
            else if (coverPhotoNotice) {
                coverPhotoNotice.parentElement.appendChild(Util_1.default.createFragmentFromHtml(`<div id="coverPhotoPreview" data-object-id="${fileId}" style="background-image: ${(0, StringUtil_1.escapeHTML)(coverPhotoStyle)};"></div>`));
                coverPhotoNotice.remove();
            }
        }
        else if (coverPhotoPreview) {
            coverPhotoPreview.parentElement.appendChild(Util_1.default.createFragmentFromHtml(`<woltlab-core-notice id="coverPhotoNotice" type="info">${(0, Language_1.getPhrase)("wcf.user.coverPhoto.noImage")}</woltlab-core-notice>`));
            coverPhotoPreview.remove();
        }
    }
    function setup() {
        (0, FileProcessor_1.registerCallback)("wcf\\action\\UserCoverPhotoAction_coverPhotoFileID", (fileId) => {
            const headerImage = document.querySelector(".userProfileHeader__coverPhotoImage");
            const coverPhotoElement = headerImage ?? document.getElementById("coverPhotoPreview");
            if (coverPhotoElement && parseInt(coverPhotoElement.dataset.objectId) === fileId) {
                // nothing changed
                return;
            }
            const file = document.querySelector(`#wcf\\\\action\\\\UserCoverPhotoAction_coverPhotoFileIDContainer woltlab-core-file[file-id="${fileId}"]`);
            const fileUrl = file?.link ?? "";
            const coverPhotoUrl = fileUrl || getDefaultCoverPhoto();
            if (headerImage && coverPhotoUrl) {
                headerImage.src = coverPhotoUrl;
                headerImage.dataset.objectId = fileId?.toString() || "";
                document
                    .getElementById("wcf\\action\\UserCoverPhotoAction_coverPhotoFileIDContainer")
                    ?.closest("woltlab-core-dialog")
                    ?.close();
            }
            // ACP cover photo management
            updateAcpCoverPhotoPreview(fileId, fileUrl);
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
