/**
 * Reacts to objects being toggled.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Toggle
 */

import * as Language from "../../../Language";
import UiObjectActionHandler from "./Handler";
import { DatabaseObjectActionResponse } from "../../../Ajax/Data";

function toggleObject(data: DatabaseObjectActionResponse, objectElement: HTMLElement): void {
  const toggleButton = objectElement.querySelector('.jsObjectAction[data-object-action="toggle"]') as HTMLElement;

  if (toggleButton.classList.contains("fa-square-o")) {
    toggleButton.classList.replace("fa-square-o", "fa-check-square-o");

    const newTitle = toggleButton.dataset.disableTitle || Language.get("wcf.global.button.disable");
    toggleButton.title = newTitle;
  } else {
    toggleButton.classList.replace("fa-check-square-o", "fa-square-o");

    const newTitle = toggleButton.dataset.enableTitle || Language.get("wcf.global.button.enable");
    toggleButton.title = newTitle;
  }
}

export function setup(): void {
  new UiObjectActionHandler("toggle", ["enable", "disable"], toggleObject);
}
