import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import * as UiDialog from "../../../Ui/Dialog";

class AcpUiBoxCopy implements DialogCallbackObject {
  constructor() {
    document.querySelectorAll(".jsButtonCopyBox").forEach((button: HTMLElement) => {
      button.addEventListener("click", (ev) => this.click(ev));
    });
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "acpBoxCopyDialog",
      options: {
        title: Language.get("wcf.acp.box.copy"),
      },
    };
  }
}

let acpUiBoxCopy: AcpUiBoxCopy;

export function init(): void {
  if (!acpUiBoxCopy) {
    acpUiBoxCopy = new AcpUiBoxCopy();
  }
}
