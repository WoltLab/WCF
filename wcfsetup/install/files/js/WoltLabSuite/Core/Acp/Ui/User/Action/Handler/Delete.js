/**
 * Deletes a given user.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../../Ui/Confirmation", "../../../../../Ajax"], function (require, exports, tslib_1, UiConfirmation, Ajax) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Delete = void 0;
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Ajax = tslib_1.__importStar(Ajax);
    class Delete {
        constructor(userIDs, successCallback, deleteMessage) {
            this.userIDs = userIDs;
            this.successCallback = successCallback;
            this.deleteMessage = deleteMessage;
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
