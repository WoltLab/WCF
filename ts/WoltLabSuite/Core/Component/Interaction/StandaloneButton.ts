/**
 * Represents a button that provides a context menu with interactions.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getContextMenuOptions } from "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";

export class StandaloneButton {
  #container: HTMLElement;
  #providerClassName: string;
  #objectId: string | number;
  #redirectUrl: string;

  constructor(container: HTMLElement, providerClassName: string, objectId: string | number, redirectUrl: string) {
    this.#container = container;
    this.#providerClassName = providerClassName;
    this.#objectId = objectId;
    this.#redirectUrl = redirectUrl;

    this.#initInteractions();
    this.#initEventListeners();
  }

  async #refreshContextMenu(): Promise<void> {
    const response = (await getContextMenuOptions(this.#providerClassName, this.#objectId)).unwrap();

    const dropdown = this.#getDropdownMenu();
    if (!dropdown) {
      return;
    }

    dropdown.innerHTML = response.template;

    this.#initInteractions();
  }

  #getDropdownMenu(): HTMLElement | undefined {
    const button = this.#container.querySelector<HTMLButtonElement>(".dropdownToggle");
    if (!button) {
      return undefined;
    }

    let dropdown = UiDropdownSimple.getDropdownMenu(button.dataset.target!);
    if (!dropdown) {
      dropdown = button.closest(".dropdown")!.querySelector<HTMLElement>(".dropdownMenu")!;
    }

    return dropdown;
  }

  #initInteractions(): void {
    this.#getDropdownMenu()
      ?.querySelectorAll<HTMLButtonElement>("[data-interaction]")
      .forEach((element) => {
        element.addEventListener("click", () => {
          this.#container.dispatchEvent(
            new CustomEvent("interaction", {
              detail: element.dataset,
              bubbles: true,
            }),
          );
        });
      });
  }

  #initEventListeners(): void {
    this.#container.addEventListener("refresh", () => {
      void this.#refreshContextMenu();
    });

    this.#container.addEventListener("remove", () => {
      window.location.href = this.#redirectUrl;
    });
  }
}
