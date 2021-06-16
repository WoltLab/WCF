define(["require", "exports", "tslib", "../../../../Core", "./AbstractUserAction", "./Handler/Ban", "../../../../Ui/Notification", "../../../../Event/Handler"], function (require, exports, tslib_1, Core, AbstractUserAction_1, Ban_1, UiNotification, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BanAction = void 0;
    Core = tslib_1.__importStar(Core);
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
    Ban_1 = tslib_1.__importDefault(Ban_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    EventHandler = tslib_1.__importStar(EventHandler);
    /**
     * @author  Joshua Ruesweg
     * @copyright  2001-2021 WoltLab GmbH
     * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @module  WoltLabSuite/Core/Acp/Ui/User/Action
     * @since       5.5
     */
    class BanAction extends AbstractUserAction_1.default {
        init() {
            this.banHandler = new Ban_1.default([this.userId]);
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                const isBanned = Core.stringToBool(this.userData.dataset.banned);
                if (isBanned) {
                    this.banHandler.unban(() => {
                        this.userData.dataset.banned = "false";
                        this.button.textContent = this.button.dataset.banMessage;
                        UiNotification.show();
                        EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                            userIds: [this.userId]
                        });
                    });
                }
                else {
                    this.banHandler.ban(() => {
                        this.userData.dataset.banned = "true";
                        this.button.textContent = this.button.dataset.unbanMessage;
                        UiNotification.show();
                        EventHandler.fire("com.woltlab.wcf.acp.user", "refresh", {
                            userIds: [this.userId]
                        });
                    });
                }
            });
        }
    }
    exports.BanAction = BanAction;
    exports.default = BanAction;
});
