/**
 * Provides the program logic for node tree views.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { postObject } from "../Api/PostObject";
import { promiseMutex } from "../Helper/PromiseMutex";
import { wheneverFirstSeen } from "../Helper/Selector";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import Sortable from "sortablejs";

export class NodeTreeView {
  readonly #id: string;
  readonly #setPositionsEndpoint: string;
  readonly #sortables = new Map<number, Sortable>();

  constructor(id: string, setPositionsEndpoint: string = "") {
    this.#id = id;
    this.#setPositionsEndpoint = setPositionsEndpoint;

    this.#initInteractions();
    this.#initEventListeners();

    if (this.#setPositionsEndpoint) {
      this.#initializeSorting();
    }
  }

  #initInteractions(): void {
    wheneverFirstSeen(`#${this.#id} .nodeTreeView__item`, (node) => {
      const content = node.querySelector<HTMLElement>(":scope > .nodeTreeView__item__content")!;
      const containers = [content];

      content.querySelectorAll<HTMLElement>(".dropdownToggle").forEach((element) => {
        const dropdown = UiDropdownSimple.getDropdownMenu(element.dataset.target!);
        if (dropdown) {
          containers.push(dropdown);
        }
      });

      for (const container of containers) {
        container.querySelectorAll<HTMLButtonElement>("[data-interaction]").forEach((element) => {
          element.addEventListener("click", () => {
            node.dispatchEvent(
              new CustomEvent("interaction:execute", {
                detail: element.dataset,
                bubbles: true,
              }),
            );
          });
        });
      }
    });
  }

  #showFooter(): void {
    document.getElementById(`${this.#id}_footer`)!.hidden = false;
  }

  #hideFooter(): void {
    document.getElementById(`${this.#id}_footer`)!.hidden = true;
  }

  async #setPositions(): Promise<void> {
    const positions: Record<number, number[]> = {};
    for (const [objectId, sortables] of this.#sortables) {
      const objectIds = sortables.toArray();
      if (objectIds.length === 0) {
        continue;
      }

      positions[objectId] = objectIds.map((objectId) => parseInt(objectId));
    }

    await postObject(`${window.WSC_RPC_API_URL}${this.#setPositionsEndpoint}`, { positions });

    this.#hideFooter();
  }

  #initializeSorting(): void {
    const button = document.getElementById(`${this.#id}_submitButton`)!;
    button.addEventListener(
      "click",
      promiseMutex(() => this.#setPositions()),
    );

    wheneverFirstSeen(`#${this.#id} .nodeTreeView__list`, (list) => {
      this.#sortables.set(
        parseInt(list.dataset.parentObjectId!),
        new Sortable(list, {
          group: "nested",
          animation: 150,
          fallbackOnBody: true,
          draggable: "li",
          handle: ".nodeTreeView__item__handle",
          dataIdAttr: "data-object-id",
          onChange: () => {
            this.#showFooter();
          },
        }),
      );
    });
  }

  #initEventListeners(): void {
    const nodeTreeView = document.getElementById(this.#id)!;

    nodeTreeView.addEventListener("interaction:invalidate-all", () => {
      window.location.reload();
    });

    nodeTreeView.addEventListener("interaction:invalidate", () => {
      window.location.reload();
    });

    nodeTreeView.addEventListener("interaction:remove", (event) => {
      const item = event.target as HTMLElement;
      const childList = item.querySelector<HTMLElement>(":scope > .nodeTreeView__list");
      if (childList) {
        const objectId = parseInt(item.dataset.objectId!);
        this.#sortables.get(objectId)?.destroy();
        this.#sortables.delete(objectId);
        item.before(...childList.children);
      }
      item.remove();
    });
  }
}
