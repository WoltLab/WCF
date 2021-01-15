/**
 * Provides the dialog overlay to add a new page.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Page/Add
 */

import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";

class AcpUiPageAdd implements DialogCallbackObject {
  private readonly isI18n: boolean;
  private readonly link: string;

  constructor(link: string, isI18n: boolean) {
    this.link = link;
    this.isI18n = isI18n;

    document.querySelectorAll(".jsButtonPageAdd").forEach((button: HTMLAnchorElement) => {
      button.addEventListener("click", (ev) => this.openDialog(ev));
    });
  }

  /**
   * Opens the 'Add Page' dialog.
   */
  openDialog(event?: MouseEvent): void {
    if (event instanceof Event) {
      event.preventDefault();
    }

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "pageAddDialog",
      options: {
        onSetup: (content) => {
          const button = content.querySelector("button") as HTMLButtonElement;
          button.addEventListener("click", (event) => {
            event.preventDefault();

            const pageType = (content.querySelector('input[name="pageType"]:checked') as HTMLInputElement).value;
            let isMultilingual = "0";
            if (this.isI18n) {
              isMultilingual = (content.querySelector('input[name="isMultilingual"]:checked') as HTMLInputElement)
                .value;
            }

            window.location.href = this.link
              .replace("{$pageType}", pageType)
              .replace("{$isMultilingual}", isMultilingual);
          });
        },
        title: Language.get("wcf.acp.page.add"),
      },
    };
  }
}

let acpUiPageAdd: AcpUiPageAdd;

/**
 * Initializes the page add handler.
 */
export function init(link: string, languages: number): void {
  if (!acpUiPageAdd) {
    acpUiPageAdd = new AcpUiPageAdd(link, languages > 0);
  }
}

/**
 * Opens the 'Add Page' dialog.
 */
export function openDialog(): void {
  acpUiPageAdd.openDialog();
}
