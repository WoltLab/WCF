import { dialogFactory } from "../Dialog";
import * as Language from "../../Language";

export class ConfirmationPrefab {
  readonly #title: string;

  constructor(title: string) {
    this.#title = title;
  }

  async delete(): Promise<boolean> {
    const html = `<p>${Language.get("wcf.dialog.confirmation.cannotBeUndone")}</p>`;
    const dialog = dialogFactory()
      .fromHtml(html)
      .asConfirmation({
        primary: Language.get("wcf.dialog.button.primary.delete"),
      });

    const question = Language.get("wcf.dialog.confirmation.delete", { title: this.#title });
    dialog.show(question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }

  async restore(): Promise<boolean> {
    const question = Language.get("wcf.dialog.confirmation.restore", { title: this.#title });

    return this.#withoutFormElements(question);
  }

  async softDelete(): Promise<boolean> {
    const question = Language.get("wcf.dialog.confirmation.softDelete", { title: this.#title });

    return this.#withoutFormElements(question);
  }

  #withoutFormElements(question: string): Promise<boolean> {
    const dialog = dialogFactory().withoutContent().asConfirmation();

    dialog.show(question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }
}
