/**
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../../../Language", "../../../../Notification", "./Abstract", "../../../../../Form/Builder/Dialog"], function (require, exports, tslib_1, Language, UiNotification, Abstract_1, Dialog_1) {
    "use strict";
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UiUserProfileMenuItemIgnore extends Abstract_1.default {
        dialog;
        constructor(userId, isActive) {
            super(userId, isActive);
            this.dialog = new Dialog_1.default("ignoreDialog", "wcf\\data\\user\\ignore\\UserIgnoreAction", "getDialog", {
                dialog: {
                    title: Language.get("wcf.user.button.ignore"),
                },
                actionParameters: {
                    userID: this._userId,
                },
                submitActionName: "submitDialog",
                successCallback: (r) => this._ajaxSuccess(r),
                destroyOnClose: true,
            });
        }
        _getLabel() {
            return Language.get("wcf.user.button." + (this._isActive ? "un" : "") + "ignore");
        }
        _ajaxSuccess(data) {
            this._isActive = !!data.isIgnoredUser;
            this._updateButton();
            UiNotification.show();
        }
        _toggle(event) {
            event.preventDefault();
            this.dialog.open();
        }
    }
    return UiUserProfileMenuItemIgnore;
});
