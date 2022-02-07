/**
 * Abstract implementation for participants views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/View/Participants
 * @since   5.5
 */

import Manager from "../Manager";
import UiUserList from "../../../User/List";

export class Participants {
  protected readonly pollManager: Manager;
  private button: HTMLButtonElement;
  private userList?: UiUserList;

  public constructor(manager: Manager) {
    this.pollManager = manager;

    this.initButton();
  }

  protected initButton(): void {
    const button =
      (this.pollManager.getPollContainer().querySelector(".showPollParticipantsButton") as HTMLButtonElement) || null;

    if (!button) {
      throw new Error(
        `Could not find button with selector "showPollParticipantsButton" for poll "${this.pollManager.pollID}"`,
      );
    }

    this.button = button;

    this.button.addEventListener("click", (event) => {
      if (event) {
        event.preventDefault();
      }

      this.open();
    });
  }

  private open(): void {
    if (!this.userList) {
      this.userList = new UiUserList({
        className: "wcf\\data\\poll\\PollAction",
        dialogTitle: this.pollManager.question,
        parameters: {
          pollID: this.pollManager.pollID,
        },
      });
    }

    this.userList.open();
  }

  public showButton(): void {
      this.button.hidden = false;
  }
}

export default Participants;
