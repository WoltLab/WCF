/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import AbstractUserAction from "./AbstractUserAction";
import Delete from "./Handler/Delete";

export class DeleteAction extends AbstractUserAction {
  protected init(): void {
    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      const deleteHandler = new Delete(
        [this.userId],
        () => {
          this.userDataElement.remove();
        },
        this.button.dataset.confirmMessage,
      );
      deleteHandler.delete();
    });
  }
}

export default DeleteAction;
