import AbstractUserAction from "./AbstractUserAction";
import * as Language from "../../../../Language";
import Delete from "./Handler/Delete";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */
export class DeleteAction extends AbstractUserAction {
  protected init() {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      let deleteHandler = new Delete([this.userId], () => {
        this.userData.remove();
      }, this.button.dataset.confirmMessage);
      deleteHandler.delete();
    });
  }
}

export default DeleteAction;
