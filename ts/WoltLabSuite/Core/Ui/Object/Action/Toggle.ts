/**
 * Reacts to objects being toggled.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Language from "../../../Language";
import UiObjectActionHandler from "./Handler";
import { ObjectActionData } from "../Data";

function toggleObject(data: ObjectActionData): void {
  const actionElement = data.objectElement.querySelector('.jsObjectAction[data-object-action="toggle"]') as HTMLElement;
  if (!actionElement || actionElement.dataset.objectActionHandler) {
    return;
  }

  const icon: FaIcon | null =
    actionElement.nodeName === "FA-ICON" ? (actionElement as FaIcon) : actionElement.querySelector("fa-icon");
  if (icon) {
    if (icon.name === "square") {
      icon.setIcon("square-check");

      const newTitle = actionElement.dataset.disableTitle || Language.get("wcf.global.button.disable");
      actionElement.title = newTitle;
    } else {
      icon.setIcon("square");

      const newTitle = actionElement.dataset.enableTitle || Language.get("wcf.global.button.enable");
      actionElement.title = newTitle;
    }
  }
}

export function setup(): void {
  new UiObjectActionHandler("toggle", ["enable", "disable"], toggleObject);
}
