/**
 * Represents a button that provides a context menu with interactions.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { getObject } from "WoltLabSuite/Core/Api/GetObject";
import { getContextMenuOptions } from "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";

interface HeaderContent {
  template: string;
}

type Payload = Record<string, string>;

export class StandaloneButton {
  #container: HTMLElement;
  #providerClassName: string;
  #objectId: string | number;
  #redirectUrl: string;
  #reloadHeaderEndpoint: string;

  constructor(
    container: HTMLElement,
    providerClassName: string,
    objectId: string | number,
    redirectUrl: string,
    reloadHeaderEndpoint: string,
  ) {
    this.#container = container;
    this.#providerClassName = providerClassName;
    this.#objectId = objectId;
    this.#redirectUrl = redirectUrl;
    this.#reloadHeaderEndpoint = reloadHeaderEndpoint;

    this.#initInteractions();
    this.#initEventListeners();
  }

  async #refreshContextMenu(): Promise<void> {
    const { template } = await getContextMenuOptions(this.#providerClassName, this.#objectId);

    const dropdown = this.#getDropdownMenu();
    if (!dropdown) {
      return;
    }

    dropdown.innerHTML = template;

    this.#initInteractions();
  }

  async #refreshHeader(): Promise<void> {
    if (!this.#reloadHeaderEndpoint) {
      return;
    }

    const header = document.querySelector(".contentHeaderTitle");
    if (!header) {
      return;
    }

    const result = await getObject<HeaderContent>(`${window.WSC_RPC_API_URL}${this.#reloadHeaderEndpoint}`);
    if (!result.ok) {
      return;
    }

    header.outerHTML = result.value.template;
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
            new CustomEvent("interaction:execute", {
              detail: element.dataset,
              bubbles: true,
            }),
          );
        });
      });
  }

  #initEventListeners(): void {
    this.#container.addEventListener("interaction:invalidate", (event: CustomEvent<Payload>) => {
      if (event.detail._reloadPage === "true") {
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        void this.#refreshContextMenu();
        void this.#refreshHeader();
      }
    });

    this.#container.addEventListener("interaction:invalidate-all", () => {
      void this.#refreshContextMenu();
      void this.#refreshHeader();
    });

    this.#container.addEventListener("interaction:remove", () => {
      setTimeout(() => {
        window.location.href = this.#redirectUrl;
      }, 2000);
    });
  }
}
