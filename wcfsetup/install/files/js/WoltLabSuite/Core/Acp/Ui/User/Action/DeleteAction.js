/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
define(["require", "exports", "tslib", "./AbstractUserAction", "./Handler/Delete"], function (require, exports, tslib_1, AbstractUserAction_1, Delete_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DeleteAction = void 0;
    AbstractUserAction_1 = tslib_1.__importDefault(AbstractUserAction_1);
    Delete_1 = tslib_1.__importDefault(Delete_1);
    class DeleteAction extends AbstractUserAction_1.default {
        init() {
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
