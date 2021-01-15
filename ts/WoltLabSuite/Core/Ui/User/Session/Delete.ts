/**
 * Handles the deletion of a user session.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Session/Delete
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, DatabaseObjectActionResponse } from "../../../Ajax/Data";
import * as UiNotification from "../../Notification";
import * as UiConfirmation from "../../Confirmation";
import * as Language from "../../../Language";

export class UiUserSessionDelete implements AjaxCallbackObject {
  private readonly knownElements = new Map<string, HTMLElement>();

  /**
   * Initializes the session delete buttons.
   */
  constructor() {
    document.querySelectorAll(".sessionDeleteButton").forEach((element: HTMLElement) => {
      if (!element.dataset.sessionId) {
        throw new Error(`No sessionId for session delete button given.`);
      }

      if (!this.knownElements.has(element.dataset.sessionId)) {
        element.addEventListener("click", (ev) => this.delete(element, ev));

        this.knownElements.set(element.dataset.sessionId, element);
      }
    });
  }

  /**
   * Opens the user trophy list for a specific user.
   */
  private delete(element: HTMLElement, event: MouseEvent): void {
    event.preventDefault();

    UiConfirmation.show({
      message: Language.get("wcf.user.security.deleteSession.confirmMessage"),
      confirm: (_parameters) => {
        Ajax.api(this, {
          sessionID: element.dataset.sessionId,
        });
      },
    });
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const element = this.knownElements.get(data.sessionID);

    if (element !== undefined) {
      const sessionItem = element.closest("li");

      if (sessionItem !== null) {
        sessionItem.remove();
      }
    }

    UiNotification.show();
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      url: "index.php?delete-session/&t=" + window.SECURITY_TOKEN,
    };
  }
}

export default UiUserSessionDelete;

interface AjaxResponse extends DatabaseObjectActionResponse {
  sessionID: string;
}
