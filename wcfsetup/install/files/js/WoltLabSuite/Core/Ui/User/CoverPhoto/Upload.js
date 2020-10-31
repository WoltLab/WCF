/**
 * Uploads the user cover photo via AJAX.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/CoverPhoto/Upload
 */
define(["require", "exports", "tslib", "../../../Core", "../../../Dom/Util", "../../../Event/Handler", "../../Dialog", "../../Notification", "../../../Upload"], function (require, exports, tslib_1, Core, Util_1, EventHandler, UiDialog, UiNotification, Upload_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiDialog = tslib_1.__importStar(UiDialog);
    UiNotification = tslib_1.__importStar(UiNotification);
    Upload_1 = tslib_1.__importDefault(Upload_1);
    /**
     * @constructor
     */
    class UiUserCoverPhotoUpload extends Upload_1.default {
        constructor(userId) {
            super("coverPhotoUploadButtonContainer", "coverPhotoUploadPreview", {
                action: "uploadCoverPhoto",
                className: "wcf\\data\\user\\UserProfileAction",
            });
            this.userId = userId;
        }
        _getParameters() {
            return {
                userID: this.userId,
            };
        }
        _success(uploadId, data) {
            // remove or display the error message
            Util_1.default.innerError(this._button, data.returnValues.errorMessage);
            // remove the upload progress
            this._target.innerHTML = "";
            if (data.returnValues.url) {
                const photo = document.querySelector(".userProfileCoverPhoto");
                photo.style.setProperty("background-image", `url(${data.returnValues.url})`, "");
                UiDialog.close("userProfileCoverPhotoUpload");
                UiNotification.show();
                EventHandler.fire("com.woltlab.wcf.user", "coverPhoto", {
                    url: data.returnValues.url,
                });
            }
        }
    }
    Core.enableLegacyInheritance(UiUserCoverPhotoUpload);
    return UiUserCoverPhotoUpload;
});
