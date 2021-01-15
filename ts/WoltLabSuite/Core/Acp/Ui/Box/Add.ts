/**
 * Provides the dialog overlay to add a new box.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Box/Add
 */

import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";

class AcpUiBoxAdd implements DialogCallbackObject {
  private supportsI18n = false;
  private link = "";

  /**
   * Initializes the box add handler.
   */
  init(link: string, supportsI18n: boolean): void {
    this.link = link;
    this.supportsI18n = supportsI18n;

    document.querySelectorAll(".jsButtonBoxAdd").forEach((button: HTMLElement) => {
      button.addEventListener("click", (ev) => this.openDialog(ev));
    });
  }

  /**
   * Opens the 'Add Box' dialog.
   */
  openDialog(event?: MouseEvent): void {
    if (event instanceof Event) {
      event.preventDefault();
    }

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "boxAddDialog",
      options: {
        onSetup: (content) => {
          content.querySelector("button")!.addEventListener("click", (event) => {
            event.preventDefault();

            const boxTypeSelection = content.querySelector('input[name="boxType"]:checked') as HTMLInputElement;
            const boxType = boxTypeSelection.value;
            let isMultilingual = "0";
            if (boxType !== "system" && this.supportsI18n) {
              const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked') as HTMLInputElement;
              isMultilingual = i18nSelection.value;
            }

            window.location.href = this.link
              .replace("{$boxType}", boxType)
              .replace("{$isMultilingual}", isMultilingual);
          });

          content.querySelectorAll('input[type="radio"][name="boxType"]').forEach((boxType: HTMLInputElement) => {
            boxType.addEventListener("change", () => {
              content
                .querySelectorAll('input[type="radio"][name="isMultilingual"]')
                .forEach((i18nSelection: HTMLInputElement) => {
                  i18nSelection.disabled = boxType.value === "system";
                });
            });
          });
        },
        title: Language.get("wcf.acp.box.add"),
      },
    };
  }
}

let acpUiDialogAdd: AcpUiBoxAdd;

function getAcpUiDialogAdd(): AcpUiBoxAdd {
  if (!acpUiDialogAdd) {
    acpUiDialogAdd = new AcpUiBoxAdd();
  }

  return acpUiDialogAdd;
}

/**
 * Initializes the box add handler.
 */
export function init(link: string, availableLanguages: number): void {
  getAcpUiDialogAdd().init(link, availableLanguages > 1);
}

/**
 * Opens the 'Add Box' dialog.
 */
export function openDialog(event?: MouseEvent): void {
  getAcpUiDialogAdd().openDialog(event);
}
