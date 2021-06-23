define(["require", "exports", "tslib", "./AbstractUserAction", "../../../../Ajax", "../../../../Core", "../../../../Ui/Notification"], function (require, exports, tslib_1, AbstractUserAction_1, Ajax, Core, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ToggleConfirmEmailAction = void 0;
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    UiNotification = tslib_1.__importStar(UiNotification);
    /**
     * @author  Joshua Ruesweg
     * @copyright  2001-2021 WoltLab GmbH
     * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @module  WoltLabSuite/Core/Acp/Ui/User/Action
     * @since       5.5
     */
    class ToggleConfirmEmailAction extends AbstractUserAction_1.default {
        init() {
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                Ajax.api({
                    _ajaxSetup: () => {
                        const isEmailConfirmed = Core.stringToBool(this.userData.dataset.emailConfirmed);
                        return {
                            data: {
                                actionName: (isEmailConfirmed ? "un" : "") + "confirmEmail",
                                className: "wcf\\data\\user\\UserAction",
                                objectIDs: [this.userId],
                            },
                        };
                    },
                    _ajaxSuccess: (data) => {
                        if (data.objectIDs.includes(this.userId)) {
                            switch (data.actionName) {
                                case "confirmEmail":
                                    this.userData.dataset.emailConfirmed = "true";
                                    this.button.textContent = this.button.dataset.unconfirmEmailMessage;
                                    break;
                                case "unconfirmEmail":
                                    this.userData.dataset.emailConfirmed = "false";
                                    this.button.textContent = this.button.dataset.confirmEmailMessage;
                                    break;
                                default:
                                    throw new Error("Unreachable");
                            }
                        }
                        UiNotification.show();
                    },
                });
            });
        }
    }
    exports.ToggleConfirmEmailAction = ToggleConfirmEmailAction;
    exports.default = ToggleConfirmEmailAction;
});
