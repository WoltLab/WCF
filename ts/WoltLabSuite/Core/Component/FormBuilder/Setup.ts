import { prepareRequest } from "../../Ajax/Backend";
import * as DomUtil from "../../Dom/Util";
import * as FormBuilderManager from "../../Form/Builder/Manager";

type CallbackAndWhenCompleted = (result: unknown) => void;

type ResponseGetForm = {
  dialog: string;
  formId: string;
  title: string;
};

type ResponseSubmitForm =
  | ResponseGetForm
  | {
      result: unknown;
    };

export class FormBuilderSetup {
  readonly #endpoint: string;

  constructor(endpoint: string) {
    this.#endpoint = endpoint;
  }

  andWhenCompleted(callback: CallbackAndWhenCompleted) {
    void this.#dispatch(callback);
  }

  async #dispatch(callback: CallbackAndWhenCompleted): Promise<void> {
    const json = (await prepareRequest(this.#endpoint).get().fetchAsJson()) as ResponseGetForm;

    // Prevents a circular dependency.
    const { dialogFactory } = await import("../Dialog");

    const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();

    dialog.addEventListener("validate", (event) => {
      const validationCallback = FormBuilderManager.getData(json.formId).then(async (data) => {
        if (data instanceof Promise) {
          data = await data;
        }

        const response = (await prepareRequest(this.#endpoint).post(data).fetchAsJson()) as ResponseSubmitForm;
        if ("dialog" in response) {
          DomUtil.setInnerHtml(dialog.content, response.dialog);

          return false;
        } else {
          dialog.addEventListener("primary", () => {
            if (FormBuilderManager.hasForm(json.formId)) {
              FormBuilderManager.unregisterForm(json.formId);
            }

            callback(response.result);

            //updateAssignee(response.assignee);
          });

          return true;
        }
      });

      event.detail.push(validationCallback);
    });

    dialog.addEventListener("cancel", () => {
      if (FormBuilderManager.hasForm(json.formId)) {
        FormBuilderManager.unregisterForm(json.formId);
      }
    });

    dialog.show(json.title);
  }
}

export default FormBuilderSetup;
