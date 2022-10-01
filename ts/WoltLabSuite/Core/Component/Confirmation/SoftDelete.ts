import { dialogFactory } from "../Dialog";
import * as Language from "../../Language";
import WoltlabCoreDialogElement from "../../Element/woltlab-core-dialog";
import * as DomUtil from "../../Dom/Util";

type CallbackWithFormElements = (dialog: WoltlabCoreDialogElement) => void;
type ResultWithFormElements = {
  result: boolean;
  dialog: WoltlabCoreDialogElement;
};
type ResultAskForReason = {
  result: boolean;
  reason: string;
};

export class ConfirmationSoftDelete {
  #reasonInput?: HTMLTextAreaElement;
  readonly #question: string;

  constructor(question: string) {
    this.#question = question;
  }

  async askForReason(): Promise<ResultAskForReason> {
    return new Promise<ResultAskForReason>((resolve) => {
      void this.withFormElements((dialog) => {
        this.#addReasonInput(dialog);
      }).then(({ result }) => {
        if (result) {
          resolve({
            result: true,
            reason: this.#reasonInput!.value.trim(),
          });
        } else {
          resolve({
            result: false,
            reason: "",
          });
        }
      });
    });
  }

  #addReasonInput(dialog: WoltlabCoreDialogElement): void {
    const id = DomUtil.getUniqueId();
    const label = Language.get("wcf.dialog.confirmation.softDelete.reason");

    const dl = document.createElement("dl");
    dl.innerHTML = `
      <dt><label for="${id}">${label}</label></dt>
      <dd><textarea id="${id}" cols="40" rows="3"></textarea></dd>
    `;

    this.#reasonInput = dl.querySelector("textarea")!;

    dialog.content.append(dl);
  }

  async message(message: string): Promise<boolean> {
    if (message.trim() === "") {
      throw new Error(
        "An empty message for the delete confirmation was provided. Please use `defaultMessage()` if you do not want to provide a  custom message.",
      );
    }

    const dialog = dialogFactory()
      .fromHtml(`<p>${message}</p>`)
      .asConfirmation({
        primary: Language.get("wcf.dialog.button.primary.confirm"),
      });

    dialog.show(this.#question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }

  async withFormElements(callback: CallbackWithFormElements): Promise<ResultWithFormElements> {
    const dialog = dialogFactory()
      .withoutContent()
      .asConfirmation({
        primary: Language.get("wcf.dialog.button.primary.confirm"),
      });

    callback(dialog);

    dialog.show(this.#question);

    return new Promise<ResultWithFormElements>((resolve) => {
      dialog.addEventListener("primary", () => {
        resolve({
          result: true,
          dialog,
        });
      });

      dialog.addEventListener("cancel", () => {
        resolve({
          result: false,
          dialog,
        });
      });
    });
  }

  async withoutMessage(): Promise<boolean> {
    const dialog = dialogFactory()
      .withoutContent()
      .asConfirmation({
        primary: Language.get("wcf.dialog.button.primary.confirm"),
      });

    dialog.show(this.#question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }
}
