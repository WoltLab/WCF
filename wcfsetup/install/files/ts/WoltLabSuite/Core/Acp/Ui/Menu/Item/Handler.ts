/**
 * Provides the interface logic to add and edit menu items.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Menu/Item/Handler
 */

import Dictionary from "../../../../Dictionary";
import DomUtil from "../../../../Dom/Util";
import * as Language from "../../../../Language";
import * as UiPageSearchHandler from "../../../../Ui/Page/Search/Handler";

class AcpUiMenuItemHandler {
  private activePageId = 0;
  private readonly cache = new Map<number, number>();
  private readonly containerExternalLink: HTMLElement;
  private readonly containerInternalLink: HTMLElement;
  private readonly containerPageObjectId: HTMLElement;
  private readonly handlers: Map<number, string>;
  private readonly pageId: HTMLSelectElement;
  private readonly pageObjectId: HTMLInputElement;

  /**
   * Initializes the interface logic.
   */
  constructor(handlers: Map<number, string>) {
    this.handlers = handlers;

    this.containerInternalLink = document.getElementById("pageIDContainer")!;
    this.containerExternalLink = document.getElementById("externalURLContainer")!;
    this.containerPageObjectId = document.getElementById("pageObjectIDContainer")!;

    if (this.handlers.size) {
      this.pageId = document.getElementById("pageID") as HTMLSelectElement;
      this.pageId.addEventListener("change", this.togglePageId.bind(this));

      this.pageObjectId = document.getElementById("pageObjectID") as HTMLInputElement;

      this.activePageId = ~~this.pageId.value;
      if (this.activePageId && this.handlers.has(this.activePageId)) {
        this.cache.set(this.activePageId, ~~this.pageObjectId.value);
      }

      const searchButton = document.getElementById("searchPageObjectID")!;
      searchButton.addEventListener("click", (ev) => this.openSearch(ev));

      // toggle page object id container on init
      if (this.handlers.has(~~this.pageId.value)) {
        DomUtil.show(this.containerPageObjectId);
      }
    }

    document.querySelectorAll('input[name="isInternalLink"]').forEach((input: HTMLInputElement) => {
      input.addEventListener("change", () => this.toggleIsInternalLink(input.value));

      if (input.checked) {
        this.toggleIsInternalLink(input.value);
      }
    });
  }

  /**
   * Toggles between the interface for internal and external links.
   */
  private toggleIsInternalLink(value: string): void {
    if (~~value) {
      DomUtil.show(this.containerInternalLink);
      DomUtil.hide(this.containerExternalLink);
      if (this.handlers.size) {
        this.togglePageId();
      }
    } else {
      DomUtil.hide(this.containerInternalLink);
      DomUtil.hide(this.containerPageObjectId);
      DomUtil.show(this.containerExternalLink);
    }
  }

  /**
   * Handles the changed page selection.
   */
  private togglePageId(): void {
    if (this.handlers.has(this.activePageId)) {
      this.cache.set(this.activePageId, ~~this.pageObjectId.value);
    }

    this.activePageId = ~~this.pageId.value;

    // page w/o pageObjectID support, discard value
    if (!this.handlers.has(this.activePageId)) {
      this.pageObjectId.value = "";

      DomUtil.hide(this.containerPageObjectId);

      return;
    }

    const newValue = this.cache.get(this.activePageId);
    this.pageObjectId.value = newValue ? newValue.toString() : "";

    const selectedOption = this.pageId.options[this.pageId.selectedIndex];
    const pageIdentifier = selectedOption.dataset.identifier!;
    let languageItem = `wcf.page.pageObjectID.${pageIdentifier}`;
    if (Language.get(languageItem) === languageItem) {
      languageItem = "wcf.page.pageObjectID";
    }

    this.containerPageObjectId.querySelector("label")!.textContent = Language.get(languageItem);

    DomUtil.show(this.containerPageObjectId);
  }

  /**
   * Opens the handler lookup dialog.
   */
  private openSearch(event: MouseEvent): void {
    event.preventDefault();

    const selectedOption = this.pageId.options[this.pageId.selectedIndex];
    const pageIdentifier = selectedOption.dataset.identifier!;
    const languageItem = `wcf.page.pageObjectID.search.${pageIdentifier}`;

    let labelLanguageItem;
    if (Language.get(languageItem) !== languageItem) {
      labelLanguageItem = languageItem;
    }

    UiPageSearchHandler.open(
      this.activePageId,
      selectedOption.textContent!.trim(),
      (objectId) => {
        this.pageObjectId.value = objectId.toString();
        this.cache.set(this.activePageId, objectId);
      },
      labelLanguageItem,
    );
  }
}

let acpUiMenuItemHandler: AcpUiMenuItemHandler;

export function init(handlers: Dictionary<string> | Map<number, string>): void {
  if (!acpUiMenuItemHandler) {
    let map: Map<number, string>;
    if (!(handlers instanceof Map)) {
      map = new Map();
      handlers.forEach((value, key) => {
        map.set(~~~key, value);
      });
    } else {
      map = handlers;
    }

    acpUiMenuItemHandler = new AcpUiMenuItemHandler(map);
  }
}
