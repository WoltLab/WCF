define(["require", "exports", "tslib", "./AbstractUserAction", "./Handler/SendNewPassword"], function (require, exports, tslib_1, AbstractUserAction_1, SendNewPassword_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SendNewPasswordAction = void 0;
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
    SendNewPassword_1 = tslib_1.__importDefault(SendNewPassword_1);
    /**
     * @author  Joshua Ruesweg
     * @copyright  2001-2021 WoltLab GmbH
     * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @module  WoltLabSuite/Core/Acp/Ui/User/Action
     * @since       5.5
     */
    class SendNewPasswordAction extends AbstractUserAction_1.default {
        init() {
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
