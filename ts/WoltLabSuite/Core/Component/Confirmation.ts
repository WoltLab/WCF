import { dialogFactory } from "./Dialog";
import * as Language from "../Language";
import * as DomUtil from "../Dom/Util";
import { ConfirmationCustom } from "./Confirmation/Custom";

type ResultSoftDelete = {
  result: boolean;
  reason: string;
};

class ConfirmationPrefab {
  custom(question: string): ConfirmationCustom {
    return new ConfirmationCustom(question);
  }

  async delete(title: string): Promise<boolean> {
    const html = `<p>${Language.get("wcf.dialog.confirmation.cannotBeUndone")}</p>`;
    const dialog = dialogFactory()
      .fromHtml(html)
      .asConfirmation({
        primary: Language.get("wcf.dialog.button.primary.delete"),
      });

    const question = Language.get("wcf.dialog.confirmation.delete", { title });
    dialog.show(question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }

  async restore(title: string): Promise<boolean> {
    const dialog = dialogFactory().withoutContent().asConfirmation();

    const question = Language.get("wcf.dialog.confirmation.restore", { title });
    dialog.show(question);

    return new Promise<boolean>((resolve) => {
      dialog.addEventListener("primary", () => resolve(true));
      dialog.addEventListener("cancel", () => resolve(false));
    });
  }

  async softDelete(title: string, askForReason: boolean): Promise<ResultSoftDelete> {
    const dialog = dialogFactory().withoutContent().asConfirmation();

    let reason: HTMLTextAreaElement | undefined = undefined;
    if (askForReason) {
      const id = DomUtil.getUniqueId();
      const label = Language.get("wcf.dialog.confirmation.softDelete.reason");

      const dl = document.createElement("dl");
      dl.innerHTML = `
        <dt><label for="${id}">${label}</label></dt>
        <dd><textarea id="${id}" cols="40" rows="3"></textarea></dd>
      `;
      reason = dl.querySelector("textarea")!;

      dialog.append(reason);
    }

    const question = Language.get("wcf.dialog.confirmation.softDelete", { title });
    dialog.show(question);

    return new Promise<ResultSoftDelete>((resolve) => {
      dialog.addEventListener("primary", () => {
        resolve({
          result: true,
          reason: reason ? reason.value.trim() : "",
        });
      });

      dialog.addEventListener("cancel", () => {
        resolve({
          result: false,
          reason: "",
        });
      });
    });
  }
}

export function confirmationFactory(): ConfirmationPrefab {
  return new ConfirmationPrefab();
}
