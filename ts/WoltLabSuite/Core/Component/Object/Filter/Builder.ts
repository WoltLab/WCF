import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dialogFactory } from "../../Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

type Response = {
  identifier: string;
  summary: string;
  value: string;
};

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

  #fromSerializedData(values: SerializedData): void {
    for (const filter of values) {
      this.#createCondition(filter);
    }
  }

  async #addFilter(): Promise<void> {
    const response = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(this.#endpoint);
    if (response.ok) {
      this.#createCondition(response.result);
    }
  }

  #createCondition(data: Response): void {
    const item = document.createElement("div");
    item.textContent = data.summary;

    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("button", "small", "jsTooltip");
    deleteButton.title = getPhrase("wcf.global.button.delete");
    deleteButton.innerHTML = '<fa-icon name="times"></fa-icon>';
    deleteButton.addEventListener("click", () => {
      this.#deleteCondition(item);
    });

    item.append(deleteButton);

    this.#container.append(item);

    this.#conditions.set(item, data);
  }

  #deleteCondition(element: HTMLElement): void {
    element.remove();
    this.#conditions.delete(element);
  }

  #serializeConditions(): string {
    const values: [string, string][] = [];
    this.#conditions.forEach((condition) => {
      values.push([condition.identifier, condition.value]);
    });

    return JSON.stringify(values);
  }
}

export function setup(container: HTMLElement, endpoint: string, values: SerializedData): void {
  new ObjectFilterBuilder(container, endpoint, values);
}
