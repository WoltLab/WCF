/**
 * Provides the confirmation dialog overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Confirmation (alias)
 * @module  WoltLabSuite/Core/Ui/Confirmation
 */

import * as Core from "../Core";
import * as Language from "../Language";
import UiDialog from "./Dialog";
import { DialogCallbackObject } from "./Dialog/Data";

class UiConfirmation implements DialogCallbackObject {
  private _active = false;
  private parameters: CallbackParameters;

  private readonly confirmButton: HTMLElement;
  private readonly _content: HTMLElement;
  private readonly dialog: HTMLElement;
  private readonly text: HTMLElement;

  private callbackCancel: CallbackCancel;
  private callbackConfirm: CallbackConfirm;

  constructor() {
    this.dialog = document.createElement("div");
    this.dialog.id = "wcfSystemConfirmation";
    this.dialog.classList.add("systemConfirmation");

    this.text = document.createElement("p");
    this.dialog.appendChild(this.text);

    this._content = document.createElement("div");
    this._content.id = "wcfSystemConfirmationContent";
    this.dialog.appendChild(this._content);

    const formSubmit = document.createElement("div");
    formSubmit.classList.add("formSubmit");
    this.dialog.appendChild(formSubmit);

    this.confirmButton = document.createElement("button");
    this.confirmButton.classList.add("buttonPrimary");
    this.confirmButton.textContent = Language.get("wcf.global.confirmation.confirm");
    this.confirmButton.addEventListener("click", (ev) => this._confirm());
    formSubmit.appendChild(this.confirmButton);

    const cancelButton = document.createElement("button");
    cancelButton.textContent = Language.get("wcf.global.confirmation.cancel");
    cancelButton.addEventListener("click", () => {
      UiDialog.close(this);
    });
    formSubmit.appendChild(cancelButton);

    document.body.appendChild(this.dialog);
  }

  public open(options: ConfirmationOptions): void {
    this.parameters = options.parameters || {};

    this._content.innerHTML = typeof options.template === "string" ? options.template.trim() : "";
    this.text[options.messageIsHtml ? "innerHTML" : "textContent"] = options.message;

    if (typeof options.legacyCallback === "function") {
      this.callbackCancel = (parameters) => {
        options.legacyCallback!("cancel", parameters, this.content);
      };
      this.callbackConfirm = (parameters) => {
        options.legacyCallback!("confirm", parameters, this.content);
      };
    } else {
      if (typeof options.cancel !== "function") {
        options.cancel = () => {};
      }

      this.callbackCancel = options.cancel;
      this.callbackConfirm = options.confirm!;
    }

    this._active = true;

    UiDialog.open(this);
  }

  get active(): boolean {
    return this._active;
  }

  get content(): HTMLElement {
    return this._content;
  }

  /**
   * Invoked if the user confirms the dialog.
   */
  _confirm(): void {
    this.callbackConfirm(this.parameters, this.content);

    this._active = false;

    UiDialog.close("wcfSystemConfirmation");
  }

  /**
   * Invoked on dialog close or if user cancels the dialog.
   */
  _onClose(): void {
    if (this.active) {
      this.confirmButton.blur();

      this._active = false;

      this.callbackCancel(this.parameters);
    }
  }

  /**
   * Sets the focus on the confirm button on dialog open for proper keyboard support.
   */
  _onShow(): void {
    this.confirmButton.blur();
    this.confirmButton.focus();
  }

  _dialogSetup() {
    return {
      id: "wcfSystemConfirmation",
      options: {
        onClose: this._onClose.bind(this),
        onShow: this._onShow.bind(this),
        title: Language.get("wcf.global.confirmation.title"),
      },
    };
  }
}

let confirmation: UiConfirmation;

function getConfirmation(): UiConfirmation {
  if (!confirmation) {
    confirmation = new UiConfirmation();
  }
  return confirmation;
}

type LegacyResult = "cancel" | "confirm";

type CallbackParameters = {
  [key: string]: any;
};

interface BasicConfirmationOptions {
  message: string;
  messageIsHtml?: boolean;
  parameters?: CallbackParameters;
  template?: string;
}

interface LegacyConfirmationOptions extends BasicConfirmationOptions {
  cancel?: never;
  confirm?: never;
  legacyCallback: (result: LegacyResult, parameters: CallbackParameters, element: HTMLElement) => void;
}

type CallbackCancel = (parameters: CallbackParameters) => void;
type CallbackConfirm = (parameters: CallbackParameters, content: HTMLElement) => void;

interface NewConfirmationOptions extends BasicConfirmationOptions {
  cancel?: CallbackCancel;
  confirm: CallbackConfirm;
  legacyCallback?: never;
}

export type ConfirmationOptions = LegacyConfirmationOptions | NewConfirmationOptions;

/**
 * Shows the confirmation dialog.
 */
export function show(options: ConfirmationOptions): void {
  if (getConfirmation().active) {
    return;
  }

  options = Core.extend(
    {
      cancel: null,
      confirm: null,
      legacyCallback: null,
      message: "",
      messageIsHtml: false,
      parameters: {},
      template: "",
    },
    options
  ) as ConfirmationOptions;
  options.message = typeof (options.message as any) === "string" ? options.message.trim() : "";
  if (!options.message) {
    throw new Error("Expected a non-empty string for option 'message'.");
  }
  if (typeof options.confirm !== "function" && typeof options.legacyCallback !== "function") {
    throw new TypeError("Expected a valid callback for option 'confirm'.");
  }

  getConfirmation().open(options);
}

/**
 * Returns content container element.
 */
export function getContentElement(): HTMLElement {
  return getConfirmation().content;
}
