/**
 * Handles a user delete button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports", "tslib", "./Abstract", "./Handler/Delete"], function (require, exports, tslib_1, Abstract_1, Delete_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DeleteAction = void 0;
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    Delete_1 = (0, tslib_1.__importDefault)(Delete_1);
    class DeleteAction extends Abstract_1.default {
        constructor(button, userId, userDataElement) {
            super(button, userId, userDataElement);
            if (typeof this.button.dataset.confirmMessage !== "string") {
                throw new Error("The button does not provide a confirmMessage.");
            }
            this.button.addEventListener("click", (event) => {
                event.preventDefault();
                const deleteHandler = new Delete_1.default([this.userId], () => {
                    this.userDataElement.remove();
                }, this.button.dataset.confirmMessage);
                deleteHandler.delete();
            });
        }
    }
    exports.DeleteAction = DeleteAction;
    exports.default = DeleteAction;
});
