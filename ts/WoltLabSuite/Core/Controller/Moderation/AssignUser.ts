import { prepareRequest } from "../../Ajax/Backend";
import { dialogFactory } from "../../Component/Dialog";
import DomUtil from "../../Dom/Util";
import * as FormBuilderManager from "../../Form/Builder/Manager";

type ResponseGetForm = {
  dialog: string;
  formId: string;
};

type Assignee = {
  username: string;
  userID: number;
  link: string;
};

type ResponseSubmitForm =
  | ResponseGetForm
  | {
      assignee: Assignee | null;
    };

async function showDialog(url: string): Promise<void> {
  const json = (await prepareRequest(url).get().fetchAsJson()) as ResponseGetForm;

  const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();

  dialog.addEventListener("validate", (event) => {
    const callback = FormBuilderManager.getData(json.formId).then(async (data) => {
      if (data instanceof Promise) {
        data = await data;
      }

      const response = (await prepareRequest(url).post(data).fetchAsJson()) as ResponseSubmitForm;
      if ("dialog" in response) {
        DomUtil.setInnerHtml(dialog.content, response.dialog);

        return false;
      } else {
        dialog.addEventListener(
          "primary",
          () => {
            if (FormBuilderManager.hasForm(json.formId)) {
              FormBuilderManager.unregisterForm(json.formId);
            }

            updateAssignee(response.assignee);
          },
          { once: true },
        );

        return true;
      }
    });

    event.detail.push(callback);
  });

  dialog.addEventListener("cancel", () => {
    if (FormBuilderManager.hasForm(json.formId)) {
      FormBuilderManager.unregisterForm(json.formId);
    }
  });
  dialog.show("TODO: title");
}

function updateAssignee(assignee: Assignee | null): void {
  const span = document.getElementById("moderationAssignedUser")!;
  if (assignee === null) {
    span.textContent = "TODO: nobody";
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
