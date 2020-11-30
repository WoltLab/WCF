/**
 * Provides the interface logic to add and edit boxes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Box/Handler
 */

import Dictionary from "../../../Dictionary";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";
import * as UiPageSearchHandler from "../../../Ui/Page/Search/Handler";

class AcpUiBoxHandler {
  private activePageId = 0;
  private readonly boxController: HTMLSelectElement | null;
  private readonly boxType: string;
  private readonly cache = new Map<number, number>();
  private readonly containerExternalLink: HTMLElement;
  private readonly containerPageId: HTMLElement;
  private readonly containerPageObjectId: HTMLElement;
  private readonly handlers: Map<number, string>;
  private readonly pageId: HTMLSelectElement;
  private readonly pageObjectId: HTMLInputElement;
  private readonly position: HTMLSelectElement;

  /**
   * Initializes the interface logic.
   */
  constructor(handlers: Map<number, string>, boxType: string) {
    this.boxType = boxType;
    this.handlers = handlers;

    this.boxController = document.getElementById("boxControllerID") as HTMLSelectElement;

    if (boxType !== "system") {
      this.containerPageId = document.getElementById("linkPageIDContainer")!;
      this.containerExternalLink = document.getElementById("externalURLContainer")!;
      this.containerPageObjectId = document.getElementById("linkPageObjectIDContainer")!;

      if (this.handlers.size) {
        this.pageId = document.getElementById("linkPageID") as HTMLSelectElement;
        this.pageId.addEventListener("change", () => this.togglePageId());

        this.pageObjectId = document.getElementById("linkPageObjectID") as HTMLInputElement;

        this.cache = new Map();
        this.activePageId = ~~this.pageId.value;
        if (this.activePageId && this.handlers.has(this.activePageId)) {
          this.cache.set(this.activePageId, ~~this.pageObjectId.value);
        }

        const searchButton = document.getElementById("searchLinkPageObjectID")!;
        searchButton.addEventListener("click", (ev) => this.openSearch(ev));

        // toggle page object id container on init
        if (this.handlers.has(~~this.pageId.value)) {
          DomUtil.show(this.containerPageObjectId);
        }
      }

      document.querySelectorAll('input[name="linkType"]').forEach((input: HTMLInputElement) => {
        input.addEventListener("change", () => this.toggleLinkType(input.value));

        if (input.checked) {
          this.toggleLinkType(input.value);
        }
      });
    }

    if (this.boxController) {
      this.position = document.getElementById("position") as HTMLSelectElement;
      this.boxController.addEventListener("change", () => this.setAvailableBoxPositions());

      // update positions on init
      this.setAvailableBoxPositions();
    }
  }

  /**
   * Toggles between the interface for internal and external links.
   */
  private toggleLinkType(value: string): void {
    switch (value) {
      case "none":
        DomUtil.hide(this.containerPageId);
        DomUtil.hide(this.containerPageObjectId);
        DomUtil.hide(this.containerExternalLink);
        break;

      case "internal":
        DomUtil.show(this.containerPageId);
        DomUtil.hide(this.containerExternalLink);
        if (this.handlers.size) {
          this.togglePageId();
        }
        break;

      case "external":
        DomUtil.hide(this.containerPageId);
        DomUtil.hide(this.containerPageObjectId);
        DomUtil.show(this.containerExternalLink);
        break;
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

  /**
   * Updates the available box positions per box controller.
   */
  private setAvailableBoxPositions(): void {
    const selectedOption = this.boxController!.options[this.boxController!.selectedIndex];
    const supportedPositions: string[] = JSON.parse(selectedOption.dataset.supportedPositions!);

    Array.from(this.position).forEach((option: HTMLOptionElement) => {
      option.disabled = !supportedPositions.includes(option.value);
    });
  }
}

let acpUiBoxHandler: AcpUiBoxHandler;

/**
 * Initializes the interface logic.
 */
export function init(handlers: Dictionary<string> | Map<number, string>, boxType: string): void {
  if (!acpUiBoxHandler) {
    let map: Map<number, string>;
    if (!(handlers instanceof Map)) {
      map = new Map();
      handlers.forEach((value, key) => {
        map.set(~~key, value);
      });
    } else {
      map = handlers;
    }

    acpUiBoxHandler = new AcpUiBoxHandler(map, boxType);
  }
}
