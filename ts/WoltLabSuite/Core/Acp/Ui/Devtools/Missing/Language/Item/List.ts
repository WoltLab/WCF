/**
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Missing/Language/Item/List
 * @since 5.4
 */

import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../../../Ajax/Data";
import * as UiConfirmation from "../../../../../../Ui/Confirmation";
import * as Ajax from "../../../../../../Ajax";
import * as Language from "../../../../../../Language";
import UiDialog from "../../../../../../Ui/Dialog";

export class List implements AjaxCallbackObject {
  protected readonly clearExistingLogButton: HTMLAnchorElement;
  protected readonly clearLogButton: HTMLAnchorElement;

  constructor() {
    this.clearLogButton = document.getElementById("clearMissingLanguageItemLog") as HTMLAnchorElement;
    this.clearLogButton.addEventListener("click", () => this.clearLog());

    this.clearExistingLogButton = document.getElementById("clearExisingMissingLanguageItemLog") as HTMLAnchorElement;
    this.clearExistingLogButton.addEventListener("click", () => this.clearExistingLog());

    document.querySelectorAll(".jsStackTraceButton").forEach((button) => {
      button.addEventListener("click", (ev) => this.showStackTrace(ev));
    });
  }

  public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\devtools\\missing\\language\\item\\DevtoolsMissingLanguageItemAction",
      },
    };
  }

  public _ajaxSuccess(): void {
    window.location.reload();
  }

  protected clearLog(): void {
    UiConfirmation.show({
      confirm: () => {
        Ajax.api(this, {
          actionName: "clearLog",
        });
      },
      message: Language.get("wcf.acp.devtools.missingLanguageItem.clearLog.confirmMessage"),
    });
  }

  protected clearExistingLog(): void {
    UiConfirmation.show({
      confirm: () => {
        Ajax.api(this, {
          actionName: "clearExistingLog",
        });
      },
      message: Language.get("wcf.acp.devtools.missingLanguageItem.clearExistingLog.confirmMessage"),
    });
  }

  protected showStackTrace(event: Event): void {
    const target = event.currentTarget as HTMLElement;

    const dialog = UiDialog.openStatic("logEntryStackTrace", target.dataset.stackTrace!, {
      title: Language.get("wcf.acp.devtools.missingLanguageItem.stackTrace"),
    });

    dialog.dialog
      .querySelector(".jsOutputFormatToggle")!
      .addEventListener("click", (ev) => this.toggleStacktraceFormat(ev));
  }

  protected toggleStacktraceFormat(event: Event): void {
    const target = event.currentTarget as HTMLElement;

    const pre = target.nextElementSibling! as HTMLPreElement;
    if (pre.style.whiteSpace) {
      pre.style.whiteSpace = "";
    } else {
      pre.style.whiteSpace = "pre-wrap";
    }
  }
}

export default List;
