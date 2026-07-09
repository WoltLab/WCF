/**
 * Handles a send new password action.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.5
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../../../../../Ui/Confirmation", "../../../Worker"], function (require, exports, tslib_1, Language_1, UiConfirmation, Worker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SendNewPassword = void 0;
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Worker_1 = tslib_1.__importDefault(Worker_1);
    class SendNewPassword {
        userIDs;
        successCallback;
        constructor(userIDs, successCallback) {
            this.userIDs = userIDs;
            this.successCallback = successCallback;
        }
        send() {
            UiConfirmation.show({
                confirm: () => {
                    new Worker_1.default({
                        dialogId: "sendingNewPasswords",
                        dialogTitle: (0, Language_1.getPhrase)("wcf.acp.user.sendNewPassword.workerTitle"),
                        className: "wcf\\system\\worker\\SendNewPasswordWorker",
                        parameters: {
                            userIDs: this.userIDs,
                        },
                        callbackSuccess: this.successCallback,
                    });
                },
                message: (0, Language_1.getPhrase)("wcf.acp.user.action.sendNewPassword.confirmMessage"),
            });
        }
    }
    exports.SendNewPassword = SendNewPassword;
    exports.default = SendNewPassword;
});
