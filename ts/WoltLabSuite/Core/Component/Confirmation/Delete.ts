import { dialogFactory } from "../Dialog";
import * as Language from "../../Language";

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
    const dialog = dialogFactory()
      .fromHtml(`<p>${message}</p>`)
      .asConfirmation({ primary: Language.get("wcf.dialog.button.primary.delete") });

    dialog.show(this.#question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }
}
