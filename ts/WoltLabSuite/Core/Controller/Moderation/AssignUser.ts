/**
 * Assign a user to a moderation queue.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Dialog/Setup
 * @since 6.0
 */

import { dialogFactory } from "../../Component/Dialog";
import { getPhrase } from "../../Language";
import { show as showNotification } from "../../Ui/Notification";

type Assignee = {
  username: string;
  userID: number;
  link: string;
};

type Response = {
  assignee: Assignee | null;
};

async function showDialog(url: string): Promise<void> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(url);

  if (ok) {
    updateAssignee(result.assignee);
    showNotification();
  }
}

function updateAssignee(assignee: Assignee | null): void {
  const span = document.getElementById("moderationAssignedUser")!;
  if (assignee === null) {
    span.textContent = getPhrase("wcf.moderation.assignedUser.nobody");
  } else {
    const link = document.createElement("a");
    link.href = assignee.link;
    link.dataset.objectId = assignee.userID.toString();
    link.classList.add("userLink");
    link.innerHTML = assignee.username;

    span.innerHTML = "";
    span.append(link);
  }
}

export function setup(button: HTMLElement): void {
  button.addEventListener("click", () => {
    void showDialog(button.dataset.url!);
  });
}
