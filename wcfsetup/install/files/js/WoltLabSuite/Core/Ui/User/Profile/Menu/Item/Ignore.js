define(['Core', 'Language', 'Ui/Notification', './Abstract'], function (Core, Language, UiNotification, UiUserProfileMenuItemAbstract) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            _getLabel: function () { },
            _getAjaxActionName: function () { },
            _ajaxSuccess: function () { },
            _ajaxSetup: function () { },
            init: function () { },
            _initButton: function () { },
            _toggle: function () { },
            _updateButton: function () { }
        };
        return Fake;
    }
    function UiUserProfileMenuItemIgnore(userId, isActive) { this.init(userId, isActive); }
    Core.inherit(UiUserProfileMenuItemIgnore, UiUserProfileMenuItemAbstract, {
        _getLabel: function () {
            return Language.get('wcf.user.button.' + (this._isActive ? 'un' : '') + 'ignore');
        },
        _getAjaxActionName: function () {
            return this._isActive ? 'unignore' : 'ignore';
        },
        _ajaxSuccess: function (data) {
            this._isActive = (data.returnValues.isIgnoredUser ? true : false);
            this._updateButton();
            UiNotification.show();
        },
        _ajaxSetup: function () {
            return {
                data: {
                    className: 'wcf\\data\\user\\ignore\\UserIgnoreAction'
                }
            };
        }
    });
    return UiUserProfileMenuItemIgnore;
});
