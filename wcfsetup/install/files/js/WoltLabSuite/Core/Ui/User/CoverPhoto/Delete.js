/**
 * Deletes the current user cover photo.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/CoverPhoto/Delete
 */
define(['Ajax', 'EventHandler', 'Language', 'Ui/Confirmation', 'Ui/Notification'], function (Ajax, EventHandler, Language, UiConfirmation, UiNotification) {
    "use strict";
    var _button;
    var _userId = 0;
    /**
     * @exports     WoltLabSuite/Core/Ui/User/CoverPhoto/Delete
     */
    return {
        /**
         * Initializes the delete handler and enables the delete button on upload.
         */
        init: function (userId) {
            _button = elBySel('.jsButtonDeleteCoverPhoto');
            _button.addEventListener('click', this._click.bind(this));
            _userId = userId;
            EventHandler.add('com.woltlab.wcf.user', 'coverPhoto', function (data) {
                if (typeof data.url === 'string' && data.url.length > 0) {
                    elShow(_button.parentNode);
                }
            });
        },
        /**
         * Handles clicks on the delete button.
         *
         * @param {Event} event
         * @protected
         */
        _click: function (event) {
            event.preventDefault();
            UiConfirmation.show({
                confirm: Ajax.api.bind(Ajax, this),
                message: Language.get('wcf.user.coverPhoto.delete.confirmMessage')
            });
        },
        _ajaxSuccess: function (data) {
            elBySel('.userProfileCoverPhoto').style.setProperty('background-image', 'url(' + data.returnValues.url + ')', '');
            elHide(_button.parentNode);
            UiNotification.show();
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'deleteCoverPhoto',
                    className: 'wcf\\data\\user\\UserProfileAction',
                    parameters: {
                        userID: _userId
                    }
                }
            };
        }
    };
});
