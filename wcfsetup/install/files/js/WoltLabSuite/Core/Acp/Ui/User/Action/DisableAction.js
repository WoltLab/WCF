define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Core", "../../../../Ui/Notification", "./AbstractUserAction", "../../../../Event/Handler"], function (require, exports, tslib_1, Ajax, Core, UiNotification, AbstractUserAction_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DisableAction = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    UiNotification = tslib_1.__importStar(UiNotification);
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    /**
     * @author  Joshua Ruesweg
     * @copyright  2001-2021 WoltLab GmbH
     * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @module  WoltLabSuite/Core/Acp/Ui/User/Action
     * @since       5.5
     */
    class DisableAction extends AbstractUserAction_1.default {
        init() {
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                Ajax.api({
                    _ajaxSetup: () => {
                        const isEnabled = Core.stringToBool(this.userData.dataset.enabled);
                        return {
                            data: {
                                actionName: (isEnabled ? "disable" : "enable"),
                                className: "wcf\\data\\user\\UserAction",
                                objectIDs: [this.userId],
                            },
                        };
                    },
                }, undefined, (data) => {
                    if (data.objectIDs.includes(this.userId)) {
                        switch (data.actionName) {
                            case "enable":
                                this.userData.dataset.enabled = "true";
                                this.button.textContent = this.button.dataset.disableMessage;
                                break;
                            case "disable":
                                this.userData.dataset.enabled = "false";
                                this.button.textContent = this.button.dataset.enableMessage;
                                break;
                            default:
                                throw new Error("Unreachable");
                        }
                    }
                    UiNotification.show();
                    EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                        userIds: [this.userId]
                    });
                });
            });
        }
    }
    exports.DisableAction = DisableAction;
    exports.default = DisableAction;
});
