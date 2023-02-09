/**
 * Provides the dialog overlay to add a new page.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Language from "../../../Language";
import WoltlabCoreDialogElement from "../../../Element/woltlab-core-dialog";
import { dialogFactory } from "../../../Component/Dialog";

export class AcpUiPageAdd {
  readonly #supportsI18n: boolean;
  readonly #link: string;
  #dialog?: WoltlabCoreDialogElement;

  constructor(link: string, supportsI18n: boolean) {
    this.#link = link;
    this.#supportsI18n = supportsI18n;

    document.querySelectorAll(".jsButtonPageAdd").forEach((button: HTMLElement) => {
      button.addEventListener("click", () => this.show());
    });
  }

  /**
   * Opens the 'Add Page' dialog.
   */
  show(): void {
    if (!this.#dialog) {
      this.#dialog = this.#createDialog();
    }

    this.#dialog.show(Language.get("wcf.acp.page.add"));
  }

  #createDialog(): WoltlabCoreDialogElement {
    const dialog = dialogFactory().fromId("pageAddDialog").asPrompt();
    const content = dialog.content;

    dialog.addEventListener("primary", () => {
      const pageTypeSelection = content.querySelector('input[name="pageType"]:checked') as HTMLInputElement;
      const pageType = pageTypeSelection.value;
      let isMultilingual = "0";
      if (this.#supportsI18n) {
        const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked') as HTMLInputElement;
        isMultilingual = i18nSelection.value;
      }

      window.location.href = this.#link.replace("{$pageType}", pageType).replace("{$isMultilingual}", isMultilingual);
    });

    return dialog;
  }
}

export default AcpUiPageAdd;
