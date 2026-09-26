/**
 * Manages the list of configured object filters of an `ObjectFilterFormField`.
 * New filters are configured through a dialog, the resulting list is written
 * into a hidden input field when the form is submitted.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { confirmationFactory } from "../../Confirmation";
import { dialogFactory } from "../../Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

/**
 * The filter returned by the dialog after it has been submitted.
 */
type Response = {
  identifier: string;
  summary: string;
  value: string;
};

/**
 * A filter that has already been configured.
 */
type Filter = {
  identifier: string;
  summary: string;
  value: string;
};

type SerializedData = Filter[];

class ObjectFilterBuilder {
  readonly #conditions: Map<HTMLElement, Response> = new Map();
  readonly #container: HTMLElement;
  readonly #endpoint: string;

  constructor(container: HTMLElement, endpoint: string, values: SerializedData) {
    this.#container = container;
    this.#endpoint = endpoint;

    const button = document.createElement("button");
    button.type = "button";
    button.classList.add("button");
    button.textContent = "TODO: add object filter";
    button.addEventListener(
      "click",
      promiseMutex(() => this.#addFilter()),
    );

    this.#container.insertAdjacentElement("beforebegin", button);

    const form = this.#container.closest("form");
    let shadow: HTMLInputElement | undefined = undefined;
    form?.addEventListener("submit", () => {
      if (shadow === undefined) {
        shadow = document.createElement("input");
        shadow.type = "hidden";
        shadow.name = this.#container.id;

        this.#container.insertAdjacentElement("afterend", shadow);
      }

      shadow.value = this.#serializeConditions();
    });

    this.#fromSerializedData(values);
  }

  /**
   * Restores the filters that were configured before.
   */
  #fromSerializedData(values: SerializedData): void {
    for (const filter of values) {
      this.#createCondition(filter);
    }
  }

  /**
   * Opens the dialog to configure a new filter and adds it to the list.
   */
  async #addFilter(): Promise<void> {
    const response = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(this.#endpoint);
    if (response.ok) {
      this.#createCondition(response.result);
    }
  }

  /**
   * Adds the given filter to the list, showing its summary and a button to remove it
   * after a confirmation.
   */
  #createCondition(data: Response): void {
    const item = document.createElement("div");
    item.innerHTML = data.summary;
    const title = item.textContent;

    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small", "jsTooltip");
    deleteButton.title = getPhrase("wcf.global.button.delete");
    deleteButton.innerHTML = '<fa-icon name="times"></fa-icon>';
    deleteButton.addEventListener(
      "click",
      promiseMutex(async () => {
        if (await confirmationFactory().delete(title)) {
          this.#deleteCondition(item);
        }
      }),
    );

    item.append(deleteButton);

    this.#container.append(item);

    this.#conditions.set(item, data);
  }

  /**
   * Removes the given filter from the list.
   */
  #deleteCondition(element: HTMLElement): void {
    element.remove();
    this.#conditions.delete(element);
  }

  /**
   * Serializes the configured filters as a JSON-encoded list of
   * `[filterIdentifier, serializedValue]` pairs.
   */
  #serializeConditions(): string {
    const values: [string, string][] = [];
    this.#conditions.forEach((condition) => {
      values.push([condition.identifier, condition.value]);
    });

    return JSON.stringify(values);
  }
}

/**
 * Initializes the filter builder for the given container.
 *
 * @param container element that holds the list of configured filters, its id is used as the name of the submitted value
 * @param endpoint URL of the dialog to configure a new filter
 * @param values filters that were configured before
 */
export function setup(container: HTMLElement, endpoint: string, values: SerializedData): void {
  new ObjectFilterBuilder(container, endpoint, values);
}
