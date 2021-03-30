/**
 * Reacts to objects being deleted.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Delete
 */

import UiObjectActionHandler from "./Handler";
import { ObjectActionData } from "../Data";

function deleteObject(data: ObjectActionData): void {
  const actionElement = data.objectElement.querySelector('.jsObjectAction[data-object-action="delete"]') as HTMLElement;
  if (!actionElement || actionElement.dataset.objectActionHandler) {
    return;
  }

  const childContainer = data.objectElement.querySelector(".jsObjectActionObjectChildren");
  if (childContainer) {
    Array.from(childContainer.children).forEach((child: HTMLElement) => {
      data.objectElement.insertAdjacentElement("beforebegin", child);
    });
  }

  data.objectElement.remove();
}

export function setup(): void {
  new UiObjectActionHandler("delete", ["delete"], deleteObject);
}
