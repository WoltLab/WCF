import { dialogFactory } from "../../Component/Dialog";
import { getPhrase } from "../../Language";

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
