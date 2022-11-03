import { prepareRequest } from "../../Ajax/Backend";
import { dialogFactory } from "../../Component/Dialog";
import * as FormBuilderManager from "../../Form/Builder/Manager";

type Response = {
  dialog: string;
  formId: string;
};

export function setup(button: HTMLElement): void {
  button.addEventListener("click", async () => {
    const json = (await prepareRequest(button.dataset.url!).get().fetchAsJson()) as Response;

    const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();
    dialog.addEventListener("primary", async () => {
      const data = await FormBuilderManager.getData(json.formId);

      const _response = await prepareRequest(button.dataset.url!).post(data).fetchAsJson();

      // TODO: Show success / update UI
      // TODO: Handle incorrect form inputs
    });
    dialog.addEventListener("cancel", () => {
      if (FormBuilderManager.hasForm(json.formId)) {
        FormBuilderManager.unregisterForm(json.formId);
      }
    });
    dialog.show("yadayada");
  });
}
