define(["require", "exports", "tslib", "../../../../../Language", "../../../../Notification", "./Abstract"], function (require, exports, tslib_1, Language, UiNotification, Abstract_1) {
    "use strict";
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    class UiUserProfileMenuItemIgnore extends Abstract_1.default {
        constructor(userId, isActive) {
            super(userId, isActive);
        }
        _getLabel() {
            return Language.get("wcf.user.button." + (this._isActive ? "un" : "") + "ignore");
        }
        _getAjaxActionName() {
            return this._isActive ? "unignore" : "ignore";
        }
        _ajaxSuccess(data) {
            this._isActive = !!data.returnValues.isIgnoredUser;
            this._updateButton();
            UiNotification.show();
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\user\\ignore\\UserIgnoreAction",
                },
            };
        }
    }
    return UiUserProfileMenuItemIgnore;
});
