/**
 * Handles a user delete button.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action
 * @since       5.5
 */

import AbstractUserAction from "./Abstract";
import Delete from "./Handler/Delete";

export class DeleteAction extends AbstractUserAction {
  public constructor(button: HTMLElement, userId: number, userDataElement: HTMLElement) {
    super(button, userId, userDataElement);

    if (typeof this.button.dataset.confirmMessage !== "string") {
      throw new Error("The button does not provide a confirmMessage.");
    }

    this.button.addEventListener("click", (event) => {
      event.preventDefault();

      const deleteHandler = new Delete(
        [this.userId],
        () => {
          this.userDataElement.remove();
        },
        this.button.dataset.confirmMessage!,
      );
      deleteHandler.delete();
    });
  }
}

export default DeleteAction;
