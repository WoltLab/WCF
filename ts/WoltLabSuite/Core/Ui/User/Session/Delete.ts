/**
 * Handles the deletion of a user session.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import * as UiNotification from "../../Notification";
import * as UiConfirmation from "../../Confirmation";
import * as Language from "../../../Language";
import { deleteSession } from "WoltLabSuite/Core/Api/Sessions/DeleteSession";

function onClick(button: HTMLElement): void {
  UiConfirmation.show({
    message: Language.get("wcf.user.security.deleteSession.confirmMessage"),
    confirm: async (_parameters) => {
      (await deleteSession(button.dataset.sessionId!)).unwrap();

      button.closest("li")?.remove();

      UiNotification.show();
    },
  });
}

export function setup() {
  document.querySelectorAll(".sessionDeleteButton").forEach((element: HTMLElement) => {
    element.addEventListener("click", () => onClick(element));
  });
}
