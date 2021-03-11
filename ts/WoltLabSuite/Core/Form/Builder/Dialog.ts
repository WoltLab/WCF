/**
 * Provides API to create a dialog form created by form builder.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Dialog
 * @since 5.2
 */

import * as Core from "../../Core";
import UiDialog from "../../Ui/Dialog";
import { DialogCallbackObject, DialogCallbackSetup, DialogData } from "../../Ui/Dialog/Data";
import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, DatabaseObjectActionResponse, RequestOptions } from "../../Ajax/Data";
import * as FormBuilderManager from "./Manager";
import { FormBuilderData, FormBuilderDialogOptions } from "./Data";

interface DialogResponse {
  dialog: string;
  formId: string;
}

function isDialogResponse(val: any): val is DialogResponse {
  return val.dialog !== undefined && val.formId !== undefined;
}

function assertDialogResponse(val: any): asserts val is DialogResponse {
  if (val.dialog === undefined) {
    throw new Error("Missing dialog template in return data.");
  } else if (val.formId === undefined) {
    throw new Error("Missing form id in return data.");
  }
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: DialogResponse | DatabaseObjectActionResponse["returnValues"];
}

class FormBuilderDialog implements AjaxCallbackObject, DialogCallbackObject {
  protected _actionName: string;
  protected _className: string;
  protected _dialogContent: string;
  protected _dialogId: string;
  protected _formId: string;
  protected _options: FormBuilderDialogOptions;
  protected _additionalSubmitButtons: HTMLButtonElement[];

  constructor(dialogId: string, className: string, actionName: string, options: Partial<FormBuilderDialogOptions>) {
    this.init(dialogId, className, actionName, options);
  }

  protected init(
    dialogId: string,
    className: string,
    actionName: string,
    options: Partial<FormBuilderDialogOptions>,
  ): void {
    this._dialogId = dialogId;
    this._className = className;
    this._actionName = actionName;
    this._options = Core.extend(
      {
        actionParameters: {},
        destroyOnClose: false,
        usesDboAction: /\w+\\data\\/.test(this._className),
      },
      options,
    ) as FormBuilderDialogOptions;
    this._options.dialog = Core.extend(this._options.dialog || {}, {
      onClose: () => this._dialogOnClose(),
    });

    this._formId = "";
    this._dialogContent = "";
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    const options = {
      data: {
        actionName: this._actionName,
        className: this._className,
        parameters: this._options.actionParameters,
      },
    } as RequestOptions;

    // By default, `AJAXProxyAction` is used which relies on an `IDatabaseObjectAction` object; if
    // no such object is used but an `IAJAXInvokeAction` object, `AJAXInvokeAction` has to be used.
    if (!this._options.usesDboAction) {
      options.url = "index.php?ajax-invoke/&t=" + window.SECURITY_TOKEN;
      options.withCredentials = true;
    }

    return options;
  }

  _ajaxSuccess(data: AjaxResponse): void {
    switch (data.actionName) {
      case this._actionName:
        if (data.returnValues === undefined) {
          throw new Error("Missing return data.");
        }

        assertDialogResponse(data.returnValues);

        this._openDialogContent(data.returnValues.formId, data.returnValues.dialog);

        break;

      case this._options.submitActionName:
        // If the validation failed, the dialog is shown again.
        if (data.returnValues && isDialogResponse(data.returnValues)) {
          if (data.returnValues.formId !== this._formId) {
            throw new Error(
              "Mismatch between form ids: expected '" + this._formId + "' but got '" + data.returnValues.formId + "'.",
            );
          }

          this._openDialogContent(data.returnValues.formId, data.returnValues.dialog);
        } else {
          this.destroy();

          if (typeof this._options.successCallback === "function") {
            this._options.successCallback(data.returnValues || {});
          }
        }

        break;

      default:
        throw new Error("Cannot handle action '" + data.actionName + "'.");
    }
  }

  protected _closeDialog(): void {
    UiDialog.close(this);

    if (typeof this._options.closeCallback === "function") {
      this._options.closeCallback();
    }
  }

  protected _dialogOnClose(): void {
    if (this._options.destroyOnClose) {
      this.destroy();
    }
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: this._dialogId,
      options: this._options.dialog,
      source: this._dialogContent,
    };
  }

  _dialogSubmit(): void {
    void this.getData().then((formData: FormBuilderData) => this._submitForm(formData));
  }

  /**
   * Opens the form dialog with the given form content.
   */
  protected _openDialogContent(formId: string, dialogContent: string): void {
    this.destroy(true);

    this._formId = formId;
    this._dialogContent = dialogContent;

    const dialogData = UiDialog.open(this, this._dialogContent) as DialogData;

    const cancelButton = dialogData.content.querySelector("button[data-type=cancel]") as HTMLButtonElement;
    if (cancelButton !== null && !Core.stringToBool(cancelButton.dataset.hasEventListener || "")) {
      cancelButton.addEventListener("click", () => this._closeDialog());
      cancelButton.dataset.hasEventListener = "1";
    }

    this._additionalSubmitButtons = Array.from(
      dialogData.content.querySelectorAll(':not(.formSubmit) button[type="submit"]'),
    );
    this._additionalSubmitButtons.forEach((submit) => {
      submit.addEventListener("click", () => {
        // Mark the button that was clicked so that the button data handlers know
        // which data needs to be submitted.
        this._additionalSubmitButtons.forEach((button) => {
          button.dataset.isClicked = button === submit ? "1" : "0";
        });

        // Enable other `click` event listeners to be executed first before the form
        // is submitted.
        setTimeout(() => UiDialog.submit(this._dialogId), 0);
      });
    });
  }

  /**
   * Submits the form with the given form data.
   */
  protected _submitForm(formData: FormBuilderData): void {
    const dialogData = UiDialog.getDialog(this)!;

    const submitButton = dialogData.content.querySelector("button[data-type=submit]") as HTMLButtonElement;

    if (typeof this._options.onSubmit === "function") {
      this._options.onSubmit(formData, submitButton);
    } else if (typeof this._options.submitActionName === "string") {
      submitButton.disabled = true;
      this._additionalSubmitButtons.forEach((submit) => (submit.disabled = true));

      Ajax.api(this, {
        actionName: this._options.submitActionName,
        parameters: {
          data: formData,
          formId: this._formId,
        },
      });
    }
  }

  /**
   * Destroys the dialog form.
   */
  public destroy(ignoreDialog = false): void {
    if (this._formId !== "") {
      if (FormBuilderManager.hasForm(this._formId)) {
        FormBuilderManager.unregisterForm(this._formId);
      }

      if (ignoreDialog !== true) {
        UiDialog.destroy(this);
      }
    }
  }

  /**
   * Returns a promise that provides all of the dialog form's data.
   */
  public getData(): Promise<FormBuilderData> {
    if (this._formId === "") {
      throw new Error("Form has not been requested yet.");
    }

    return FormBuilderManager.getData(this._formId);
  }

  /**
   * Opens the dialog form.
   */
  public open(): void {
    if (UiDialog.getDialog(this._dialogId)) {
      UiDialog.open(this);
    } else {
      Ajax.api(this);
    }
  }
}

Core.enableLegacyInheritance(FormBuilderDialog);

export = FormBuilderDialog;
