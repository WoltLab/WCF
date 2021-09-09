/**
 * Handles a send new password button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports", "tslib", "./Abstract", "./Handler/SendNewPassword"], function (require, exports, tslib_1, Abstract_1, SendNewPassword_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SendNewPasswordAction = void 0;
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    SendNewPassword_1 = (0, tslib_1.__importDefault)(SendNewPassword_1);
    class SendNewPasswordAction extends Abstract_1.default {
        constructor(button, userId, userDataElement) {
            super(button, userId, userDataElement);
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                const sendNewPasswordHandler = new SendNewPassword_1.default([this.userId], () => {
                    location.reload();
                });
                sendNewPasswordHandler.send();
            });
        }
    }
    exports.SendNewPasswordAction = SendNewPasswordAction;
    exports.default = SendNewPasswordAction;
});
