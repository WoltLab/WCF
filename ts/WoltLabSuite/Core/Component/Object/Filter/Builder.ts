import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dialogFactory } from "../../Dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

type Response = {
  identifier: string;
  summary: string;
  value: string;
};

class ObjectFilterBuilder {
  readonly #conditions: Map<HTMLElement, Response> = new Map();
  readonly #container: HTMLElement;
  readonly #endpoint: string;

  constructor(container: HTMLElement, button: HTMLElement) {
    this.#container = container;
    this.#endpoint = button.dataset.endpoint!;

    button.addEventListener(
      "click",
      promiseMutex(() => this.#addFilter()),
    );
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
}

export function setup(container: HTMLElement, button: HTMLButtonElement): void {
  new ObjectFilterBuilder(container, button);
}
