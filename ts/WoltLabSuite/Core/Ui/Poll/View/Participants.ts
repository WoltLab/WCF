/**
 * Abstract implementation for participants views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */

import { Poll } from "../Poll";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";

export class Participants {
  protected readonly pollManager: Poll;
  private button: HTMLButtonElement;

  public constructor(manager: Poll) {
    this.pollManager = manager;

    const button = this.pollManager.getElement().querySelector<HTMLButtonElement>(".showPollParticipantsButton");
    if (!button) {
      throw new Error(
        `Could not find button with selector "showPollParticipantsButton" for poll "${this.pollManager.pollId}"`,
      );
    }
    this.button = button;
    this.button.addEventListener(
      "click",
      promiseMutex(() => {
        return dialogFactory()
          .usingListView()
          .fromPreset(
            this.pollManager.question,
            "wcf\\system\\listView\\user\\PollParticipantListView",
            new Map([["pollID", this.pollManager.pollId.toString()]]),
          );
      }),
    );
  }

  public showButton(): void {
    this.button.hidden = false;
  }

  public hideButton(): void {
    this.button.hidden = true;
  }
}

export default Participants;
