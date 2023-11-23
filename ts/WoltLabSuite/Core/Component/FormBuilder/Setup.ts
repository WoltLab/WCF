/**
 * Promise-based API to use the Form Builder API with PSR-15 controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { prepareRequest } from "../../Ajax/Backend";
import * as DomUtil from "../../Dom/Util";
import * as FormBuilderManager from "../../Form/Builder/Manager";

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

type Result<T> =
  | {
      ok: true;
      result: T;
    }
  | {
      ok: false;
      result: undefined;
    };

export class FormBuilderSetup {
  async fromEndpoint<T = unknown>(url: string): Promise<Result<T>> {
    const json = (await prepareRequest(url).get().fetchAsJson()) as ResponseGetForm;

    // Prevents a circular dependency.
    const { dialogFactory } = await import("../Dialog");

    const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();

    return new Promise<Result<T>>((resolve) => {
      dialog.addEventListener("validate", (event) => {
        const validationCallback = FormBuilderManager.getData(json.formId).then(async (data) => {
          if (data instanceof Promise) {
            data = await data;
          }

          const response = (await prepareRequest(url).post(data).fetchAsJson()) as ResponseSubmitForm;
          if ("dialog" in response) {
            FormBuilderManager.unregisterForm(json.formId);

            DomUtil.setInnerHtml(dialog.content, response.dialog);

            return false;
          } else {
            dialog.addEventListener("primary", () => {
              if (FormBuilderManager.hasForm(json.formId)) {
                FormBuilderManager.unregisterForm(json.formId);
              }

              resolve({
                ok: true,
                result: response.result as T,
              });
            });

            return true;
          }
        });

        event.detail.push(validationCallback);
      });

      dialog.addEventListener("afterClose", () => {
        if (FormBuilderManager.hasForm(json.formId)) {
          FormBuilderManager.unregisterForm(json.formId);
        }

        resolve({
          ok: false,
          result: undefined,
        });
      });

      dialog.show(json.title);
    });
  }
}

export default FormBuilderSetup;
