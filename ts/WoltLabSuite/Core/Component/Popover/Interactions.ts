/**
 * Handles the interactions of the context menu inside a popover. The dropdown
 * menu is moved into the global container, the clicks on the interactions are
 * therefore forwarded to the popover content that serves as the container.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { getContextMenuOptions } from "WoltLabSuite/Core/Api/Interactions/GetContextMenuOptions";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";
import { destroyDropdowns, resetCache } from "../Popover";

type Payload = Record<string, string>;

class PopoverInteractions {
  readonly #container: HTMLElement;
  readonly #objectId: number;
  readonly #popoverIdentifier: string;
  readonly #providerClassName: string;

  constructor(container: HTMLElement, popoverIdentifier: string, objectId: number, providerClassName: string) {
    this.#container = container;
    this.#popoverIdentifier = popoverIdentifier;
    this.#objectId = objectId;
    this.#providerClassName = providerClassName;

    this.#initInteractions();
    this.#initEventListeners();
  }

  #getToggle(): HTMLElement | null {
    return this.#container.querySelector<HTMLElement>(".dropdownToggle");
  }

  #getDropdownMenu(): HTMLElement | undefined {
    const toggle = this.#getToggle();
    if (toggle === null) {
      return undefined;
    }

    let dropdown = UiDropdownSimple.getDropdownMenu(toggle.dataset.target!);
    dropdown ??= toggle.closest(".dropdown")!.querySelector<HTMLElement>(".dropdownMenu")!;

    return dropdown;
  }

  #initInteractions(): void {
    this.#getDropdownMenu()
      ?.querySelectorAll<HTMLElement>("[data-interaction]")
      .forEach((button) => {
        button.addEventListener("click", () => {
          this.#container.dispatchEvent(
            new CustomEvent("interaction:execute", {
              detail: button.dataset,
            }),
          );
        });
      });
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

  #initEventListeners(): void {
    this.#container.addEventListener("interaction:invalidate", (event: CustomEvent<Payload>) => {
      event.stopPropagation();
      resetCache(this.#popoverIdentifier, this.#objectId);

      if (event.detail._reloadPage === "true") {
        window.location.reload();
      } else {
        void this.#refreshContextMenu();
      }
    });

    this.#container.addEventListener("interaction:invalidate-all", (event) => {
      event.stopPropagation();
      resetCache(this.#popoverIdentifier, this.#objectId);

      void this.#refreshContextMenu();
    });

    this.#container.addEventListener("interaction:remove", (event) => {
      event.stopPropagation();
      resetCache(this.#popoverIdentifier, this.#objectId);

      destroyDropdowns(this.#container);
      this.#container.remove();
    });
  }
}

export function setup(
  container: HTMLElement,
  popoverIdentifier: string,
  objectId: number,
  providerClassName: string,
): void {
  new PopoverInteractions(container, popoverIdentifier, objectId, providerClassName);
}
