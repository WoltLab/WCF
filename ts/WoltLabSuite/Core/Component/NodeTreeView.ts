/**
 * Provides the program logic for node tree views.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { wheneverFirstSeen } from "../Helper/Selector";
import UiDropdownSimple from "../Ui/Dropdown/Simple";

export class NodeTreeView {
  readonly #id: string;

  constructor(id: string) {
    this.#id = id;

    this.#initInteractions();
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
}
