/**
 * Handles a user ban button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../Core", "./Abstract", "./Handler/Ban", "../../../../Ui/Notification", "../../../../Event/Handler"], function (require, exports, tslib_1, Core, Abstract_1, Ban_1, UiNotification, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BanAction = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    Ban_1 = (0, tslib_1.__importDefault)(Ban_1);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    class BanAction extends Abstract_1.default {
        constructor(button, userId, userDataElement) {
            super(button, userId, userDataElement);
            this.banHandler = new Ban_1.default([this.userId]);
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                const isBanned = Core.stringToBool(this.userDataElement.dataset.banned);
                if (isBanned) {
                    this.banHandler.unban(() => {
                        this.userDataElement.dataset.banned = "false";
                        this.button.textContent = this.button.dataset.banMessage;
                        UiNotification.show();
                        EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                            userIds: [this.userId],
                        });
                    });
                }
                else {
                    this.banHandler.ban(() => {
                        this.userDataElement.dataset.banned = "true";
                        this.button.textContent = this.button.dataset.unbanMessage;
                        UiNotification.show();
                        EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                            userIds: [this.userId],
                        });
                    });
                }
            });
        }
    }
    exports.BanAction = BanAction;
    exports.default = BanAction;
});
