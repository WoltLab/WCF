/**
 * Provides the dialog overlay to add a new box.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Box/Add
 */

import * as Language from "../../../Language";
import WoltlabCoreDialogElement from "../../../Element/woltlab-core-dialog";
import { dialogFactory } from "../../../Component/Dialog";

export class AcpUiBoxAdd {
  readonly #supportsI18n: boolean;
  readonly #link: string;
  #dialog?: WoltlabCoreDialogElement;

  /**
   * Initializes the box add handler.
   */
  constructor(link: string, supportsI18n: boolean) {
    this.#link = link;
    this.#supportsI18n = supportsI18n;

    document.querySelectorAll(".jsButtonBoxAdd").forEach((button: HTMLElement) => {
      button.addEventListener("click", () => this.show());
    });
  }

  /**
   * Opens the 'Add Box' dialog.
   */
  show(): void {
    if (!this.#dialog) {
      this.#dialog = this.#createDialog();
    }

    this.#dialog.show(Language.get("wcf.acp.box.add"));
  }

  #createDialog(): WoltlabCoreDialogElement {
    const dialog = dialogFactory().fromId("boxAddDialog").asPrompt();
    const content = dialog.content;

    content.querySelectorAll('input[type="radio"][name="boxType"]').forEach((boxType: HTMLInputElement) => {
      boxType.addEventListener("change", () => {
        content
          .querySelectorAll('input[type="radio"][name="isMultilingual"]')
          .forEach((i18nSelection: HTMLInputElement) => {
            i18nSelection.disabled = boxType.value === "system";
          });
      });
    });

    dialog.addEventListener("primary", () => {
      const boxTypeSelection = content.querySelector('input[name="boxType"]:checked') as HTMLInputElement;
      const boxType = boxTypeSelection.value;
      let isMultilingual = "0";
      if (boxType !== "system" && this.#supportsI18n) {
        const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked') as HTMLInputElement;
        isMultilingual = i18nSelection.value;
      }

      window.location.href = this.#link.replace("{$boxType}", boxType).replace("{$isMultilingual}", isMultilingual);
    });

    return dialog;
  }
}

export default AcpUiBoxAdd;
