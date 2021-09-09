/**
 * Handles a user disable/enable button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Core", "../../../../Ui/Notification", "./Abstract", "../../../../Event/Handler"], function (require, exports, tslib_1, Ajax, Core, UiNotification, Abstract_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DisableAction = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    Core = (0, tslib_1.__importStar)(Core);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    class DisableAction extends Abstract_1.default {
        constructor(button, userId, userDataElement) {
            super(button, userId, userDataElement);
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                const isEnabled = Core.stringToBool(this.userDataElement.dataset.enabled);
                Ajax.api(this, {
                    actionName: isEnabled ? "disable" : "enable",
                });
            });
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\user\\UserAction",
                    objectIDs: [this.userId],
                },
            };
        }
        _ajaxSuccess(data) {
            data.objectIDs.forEach((objectId) => {
                if (~~objectId == this.userId) {
                    switch (data.actionName) {
                        case "enable":
                            this.userDataElement.dataset.enabled = "true";
                            this.button.textContent = this.button.dataset.disableMessage;
                            break;
                        case "disable":
                            this.userDataElement.dataset.enabled = "false";
                            this.button.textContent = this.button.dataset.enableMessage;
                            break;
                        default:
                            throw new Error("Unreachable");
                    }
                }
            });
            UiNotification.show();
            EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                userIds: [this.userId],
            });
        }
    }
    exports.DisableAction = DisableAction;
    exports.default = DisableAction;
});
