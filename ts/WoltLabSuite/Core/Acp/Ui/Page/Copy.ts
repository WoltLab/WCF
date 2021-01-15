import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";

class AcpUiPageCopy implements DialogCallbackObject {
  constructor() {
    document.querySelectorAll(".jsButtonCopyPage").forEach((button: HTMLAnchorElement) => {
      button.addEventListener("click", (ev) => this.click(ev));
    });
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "acpPageCopyDialog",
      options: {
        title: Language.get("wcf.acp.page.copy"),
      },
    };
  }
}

let acpUiPageCopy: AcpUiPageCopy;

export function init(): void {
  if (!acpUiPageCopy) {
    acpUiPageCopy = new AcpUiPageCopy();
  }
}
