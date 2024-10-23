/**
 * Deletes the current user cover photo.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Dom/Util", "../../../Event/Handler", "../../../Language", "../../Confirmation", "../../Notification"], function (require, exports, tslib_1, Ajax, Util_1, EventHandler, Language, UiConfirmation, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    UiNotification = tslib_1.__importStar(UiNotification);
    class UiUserCoverPhotoDelete {
        button;
        userId;
        /**
         * Initializes the delete handler and enables the delete button on upload.
         */
        constructor(userId) {
            const button = document.querySelector(".jsButtonDeleteCoverPhoto");
            if (button === null) {
                return;
            }
            this.button = button;
            this.button.addEventListener("click", (ev) => this._click(ev));
            this.userId = userId;
            EventHandler.add("com.woltlab.wcf.user", "coverPhoto", (data) => {
                if (typeof data.url === "string" && data.url.length > 0) {
                    Util_1.default.show(this.button.parentElement);
                }
            });
        }
        /**
         * Handles clicks on the delete button.
         */
        _click(event) {
            event.preventDefault();
            UiConfirmation.show({
                confirm: () => Ajax.api(this),
                message: Language.get("wcf.user.coverPhoto.delete.confirmMessage"),
            });
        }
        _ajaxSuccess(data) {
            const photo = document.querySelector(".userProfileCoverPhoto");
            photo.style.setProperty("background-image", `url(${data.returnValues.url})`, "");
            Util_1.default.hide(this.button.parentElement);
            UiNotification.show();
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "deleteCoverPhoto",
                    className: "wcf\\data\\user\\UserProfileAction",
                    parameters: {
                        userID: this.userId,
                    },
                },
            };
        }
    }
    let uiUserCoverPhotoDelete;
    /**
     * Initializes the delete handler and enables the delete button on upload.
     */
    function init(userId) {
        if (!uiUserCoverPhotoDelete) {
            uiUserCoverPhotoDelete = new UiUserCoverPhotoDelete(userId);
        }
    }
});
