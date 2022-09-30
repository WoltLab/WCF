import { dialogFactory } from "../Dialog";
import * as Language from "../../Language";
import WoltlabCoreDialogElement from "../../Element/woltlab-core-dialog";

type CallbackWithFormElements = (dialog: WoltlabCoreDialogElement) => void;
type ResultWithFormElements = {
  result: boolean;
  dialog: WoltlabCoreDialogElement;
};

export class ConfirmationDelete {
  readonly #question: string;

  constructor(question: string) {
    this.#question = question;
  }

  async defaultMessage(title = ""): Promise<boolean> {
    const message = Language.get("wcf.dialog.confirmation.delete", { title });

    return this.message(message);
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
        primary: Language.get("wcf.dialog.button.primary.delete"),
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
        primary: Language.get("wcf.dialog.button.primary.delete"),
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
}
