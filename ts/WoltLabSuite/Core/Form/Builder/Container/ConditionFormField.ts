/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { insertHtml } from "WoltLabSuite/Core/Dom/Util";
import { unescapeHTML } from "WoltLabSuite/Core/StringUtil";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";

interface ConditionAddResponse {
  field: string;
  conditionType: string;
}

export class ConditionFormField {
  readonly #containerId: string;
  readonly #container: HTMLElement;
  readonly #button: HTMLButtonElement;
  #index: number = 0;

  constructor(containerId: string, endpoint: string) {
    this.#containerId = containerId;
    this.#container = document.getElementById(`${containerId}Conditions`) as HTMLElement;

    this.#button = document.getElementById(`${containerId}AddCondition`) as HTMLButtonElement;
    this.#button?.addEventListener(
      "click",
      promiseMutex(async () => {
        await this.#showConditionAddDialog(endpoint);
      }),
    );

    wheneverFirstSeen(`#${containerId}Container .condition__container`, (container: HTMLElement) => {
      const deleteButton = document.createElement("button");
      deleteButton.type = "button";
      deleteButton.classList.add("button", "small", "jsTooltip", "condition__remove");
      deleteButton.title = getPhrase("wcf.global.button.delete");
      const icon = document.createElement("fa-icon");
      icon.setIcon("times");
      deleteButton.appendChild(icon);
      container.prepend(deleteButton);
      deleteButton.addEventListener("click", () => {
        container.remove();
      });

      const index = parseInt(container.dataset.conditionIndex!);
      this.#index = Math.max(this.#index, index);
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = `${containerId}[${index}]`;
      hidden.value = container.dataset.conditionType!;
      container.appendChild(hidden);
    });
  }

  async #showConditionAddDialog(endpoint: string) {
    const url = new URL(unescapeHTML(endpoint));
    url.searchParams.set("containerId", this.#containerId);
    url.searchParams.set("index", (this.#index + 1).toString());

    const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<ConditionAddResponse>(url.toString());

    if (ok) {
      insertHtml(result.field, this.#container, "append");
    }
  }
}
