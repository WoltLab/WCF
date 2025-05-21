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

interface ConditionAddResponse {
  field: string;
  conditionType: string;
}

export class ConditionFormField {
  readonly #containerId: string;
  readonly #container: HTMLElement;
  readonly #button: HTMLButtonElement;
  #index: number;

  constructor(containerId: string, endpoint: string, index: number) {
    this.#containerId = containerId;
    this.#index = index;
    this.#container = document.getElementById(`${containerId}Conditions`) as HTMLElement;

    this.#button = document.getElementById(`${containerId}AddCondition`) as HTMLButtonElement;
    this.#button?.addEventListener(
      "click",
      promiseMutex(async () => {
        await this.#showConditionAddDialog(endpoint);
      }),
    );

    wheneverFirstSeen(`#${containerId}Container .condition-remove`, (element: HTMLButtonElement) => {
      element.addEventListener("click", () => {
        element.parentElement?.remove();
      });
    });
  }

  async #showConditionAddDialog(endpoint: string) {
    const url = new URL(unescapeHTML(endpoint));
    url.searchParams.set("containerId", this.#containerId);
    url.searchParams.set("index", this.#index.toString());

    const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<ConditionAddResponse>(url.toString());

    if (ok) {
      this.#index++;

      insertHtml(result.field, this.#container, "append");
    }
  }
}
