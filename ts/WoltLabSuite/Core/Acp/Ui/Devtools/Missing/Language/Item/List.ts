/**
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.4
 */

import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../../../Ajax/Data";
import * as UiConfirmation from "../../../../../../Ui/Confirmation";
import * as Ajax from "../../../../../../Ajax";
import * as Language from "../../../../../../Language";
import { dialogFactory } from "../../../../../../Component/Dialog";

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

    const dialog = dialogFactory().fromHtml(target.dataset.stackTrace!).withoutControls();
    dialog.show(Language.get("wcf.acp.devtools.missingLanguageItem.stackTrace"));
  }
}

export default List;
