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
    function getCoverPhotoElement() {
        return (document.querySelector(".userProfileHeader__coverPhotoImage") ??
            document.getElementById("coverPhotoPreview"));
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-edit-cover-photo]", (button) => {
            const defaultCoverPhoto = button.dataset.defaultCoverPhoto;
            (0, FileProcessor_1.registerCallback)("wcf\\action\\UserCoverPhotoAction_coverPhotoFileID", (fileId) => {
                const coverPhotoElement = getCoverPhotoElement();
                if (coverPhotoElement && parseInt(coverPhotoElement.dataset.objectId) === fileId) {
                    // nothing changed
                    return;
                }
                const file = document.querySelector(`#wcf\\\\action\\\\UserCoverPhotoAction_coverPhotoFileIDContainer woltlab-core-file[file-id="${fileId}"]`);
                const coverPhotoNotice = document.getElementById("coverPhotoNotice");
                const coverPhotoUrl = file?.link ?? defaultCoverPhoto ?? "";
                const coverPhotoStyle = `url("${coverPhotoUrl}")`;
                if (coverPhotoElement instanceof HTMLImageElement && coverPhotoUrl) {
                    coverPhotoElement.src = coverPhotoUrl;
                    coverPhotoElement.dataset.objectId = fileId?.toString() || "";
                    document
                        .getElementById("wcf\\action\\UserCoverPhotoAction_coverPhotoFileIDContainer")
                        ?.closest("woltlab-core-dialog")
                        ?.close();
                }
                else {
                    // ACP cover photo management
                    if (!coverPhotoElement && coverPhotoUrl) {
                        coverPhotoNotice.parentElement.appendChild(Util_1.default.createFragmentFromHtml(`<div id="coverPhotoPreview" data-object-id="${fileId}" style="background-image: ${(0, StringUtil_1.escapeHTML)(coverPhotoStyle)};"></div>`));
                        coverPhotoNotice.remove();
                    }
                    else if (coverPhotoElement && !coverPhotoUrl) {
                        coverPhotoElement.parentElement.appendChild(Util_1.default.createFragmentFromHtml(`<woltlab-core-notice id="coverPhotoNotice" type="info">${(0, Language_1.getPhrase)("wcf.user.coverPhoto.noImage")}</woltlab-core-notice>`));
                        coverPhotoElement.remove();
                    }
                    else if (coverPhotoElement && coverPhotoUrl) {
                        coverPhotoElement.style.backgroundImage = coverPhotoStyle;
                        coverPhotoElement.dataset.objectId = fileId?.toString() || "";
                    }
                }
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
                (0, Handler_1.fire)("com.woltlab.wcf.user", "coverPhoto", {
                    url: coverPhotoUrl,
                });
            });
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => editCoverPhoto(button)));
        });
    }
});
