/**
 * Provides the interface logic to add and edit menu items.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as UiPageSearchHandler from "../../../../Ui/Page/Search/Handler";
import { getPhrase } from "WoltLabSuite/Core/Language";

export class AcpUiMenuItemHandler {
  readonly #handlers: Map<number, boolean>;
  readonly #identifiers: Map<number, string>;
  readonly #pageId: HTMLSelectElement;
  readonly #pageObjectId: HTMLInputElement;

  /**
   * Initializes the interface logic.
   */
  constructor(fieldPrefix: string, handlers: Map<number, boolean>, identifiers: Map<number, string>) {
    this.#handlers = handlers;
    this.#identifiers = identifiers;

    if (this.#handlers.size) {
      this.#pageId = document.getElementById("pageID") as HTMLSelectElement;
      this.#pageObjectId = document.getElementById(fieldPrefix) as HTMLInputElement;

      const searchButton = document.getElementById(fieldPrefix + "Search")!;
      searchButton.addEventListener("click", () => this.openSearch());
    }
  }

  /**
   * Opens the handler lookup dialog.
   */
  private openSearch(): void {
    const selectedOption = this.#pageId.options[this.#pageId.selectedIndex];
    const pageIdentifier = this.#identifiers.get(parseInt(selectedOption.value));
    const languageItem = `wcf.page.pageObjectID.search.${pageIdentifier}`;

    let labelLanguageItem;
    if (getPhrase(languageItem) !== languageItem) {
      labelLanguageItem = languageItem;
    }

    UiPageSearchHandler.open(
      parseInt(selectedOption.value),
      selectedOption.textContent.trim(),
      (objectId) => {
        this.#pageObjectId.value = objectId.toString();
      },
      labelLanguageItem,
    );
  }
}
