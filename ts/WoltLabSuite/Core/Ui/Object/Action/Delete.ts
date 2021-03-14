/**
 * Reacts to objects being deleted.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Delete
 */

import UiObjectActionHandler from "./Handler";
import { DatabaseObjectActionResponse } from "../../../Ajax/Data";

function deleteObject(data: DatabaseObjectActionResponse, objectElement: HTMLElement): void {
  objectElement.remove();
}

export function setup(): void {
  new UiObjectActionHandler("delete", ["delete"], deleteObject);
}
