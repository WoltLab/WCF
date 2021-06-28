/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../../Language", "../../../../../Ui/Confirmation", "../../../../../Ajax"], function (require, exports, tslib_1, Language, UiConfirmation, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Delete = void 0;
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Ajax = tslib_1.__importStar(Ajax);
    class Delete {
        constructor(userIDs, successCallback, deleteMessage) {
            this.userIDs = userIDs;
            this.successCallback = successCallback;
            if (deleteMessage) {
                this.deleteMessage = deleteMessage;
            }
            else {
                this.deleteMessage = Language.get("wcf.button.delete.confirmMessage"); // @todo find better variable for a generic message
            }
        }
        delete() {
            UiConfirmation.show({
                confirm: () => {
                    Ajax.apiOnce({
                        data: {
                            actionName: "delete",
                            className: "wcf\\data\\user\\UserAction",
                            objectIDs: this.userIDs,
                        },
                        success: this.successCallback,
                    });
                },
                message: this.deleteMessage,
                messageIsHtml: true,
            });
        }
    }
    exports.Delete = Delete;
    exports.default = Delete;
});
