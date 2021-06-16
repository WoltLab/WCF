define(["require", "exports", "tslib", "../../../../Event/Handler", "../../../../Language", "./AbstractUserAction"], function (require, exports, tslib_1, EventHandler, Language, AbstractUserAction_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SendNewPasswordAction = void 0;
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
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
                // emulate clipboard selection
                EventHandler.fire("com.woltlab.wcf.clipboard", "com.woltlab.wcf.user", {
                    data: {
                        actionName: "com.woltlab.wcf.user.sendNewPassword",
                        parameters: {
                            confirmMessage: Language.get("wcf.acp.user.action.sendNewPassword.confirmMessage"),
                            objectIDs: [this.userId],
                        },
                    },
                    responseData: {
                        actionName: "com.woltlab.wcf.user.sendNewPassword",
                        objectIDs: [this.userId],
                    },
                });
            });
        }
    }
    exports.SendNewPasswordAction = SendNewPasswordAction;
    exports.default = SendNewPasswordAction;
});
