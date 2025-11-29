import { identify } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { getValues, init as initI18n } from "WoltLabSuite/Core/Language/Input";
import Sortable from "sortablejs";

type Data = {
  key: string;
  value: Record<string, string>;
};

type Languages = Record<string, string>;

class SelectOptions {
  readonly #languages: Languages;
  readonly #list: HTMLUListElement;
  readonly #template: HTMLTemplateElement;

  constructor(formField: HTMLInputElement, languages: Languages) {
    if (formField.form === null) {
      throw new Error("Cannot create select options for an form field that is not part of a form.", {
        cause: {
          formField,
        },
      });
    }

    this.#languages = languages;
    this.#template = document.getElementById(`${formField.id}_template`) as HTMLTemplateElement;

    this.#list = this.#createUi(formField);
    this.#createListItems(formField);
    this.#initializeSorting();

    formField.form.addEventListener("submit", () => {
      this.#setHiddenValue(formField);
    });
  }

  #createUi(formField: HTMLInputElement): HTMLUListElement {
    const ul = document.createElement("ul");
    ul.classList.add("selectOptionsList");
    formField.insertAdjacentElement("afterend", ul);

    const addButton = this.#createAddButton();
    addButton.addEventListener("click", () => {
      this.#createRow(undefined, true);
    });
    ul.insertAdjacentElement("afterend", addButton);

    return ul;
  }

  #createListItems(formField: HTMLInputElement): void {
    if (formField.value) {
      const data = JSON.parse(formField.value) as Data[];
      data.forEach((option) => {
        this.#createRow(option);
      });
    } else {
      this.#createRow();
    }
  }

  #createAddButton(): HTMLButtonElement {
    const button = document.createElement("button");
    button.type = "button";
    button.classList.add("button", "small", "selectOptionsListItem__addItem");
    button.textContent = getPhrase("wcf.form.selectOptions.addItem");

    return button;
  }

  #createRow(option?: Data, autoFocus: boolean = false): void {
    const li = document.createElement("li");
    li.classList.add("selectOptionsListItem");
    li.append(this.#template.content.cloneNode(true));

    this.#list.append(li);

    const removeButton = li.querySelector(".selectOptionsListItem__remove") as HTMLButtonElement;
    removeButton.addEventListener("click", () => {
      li.remove();

      if (!this.#list.childElementCount) {
        this.#createRow();
      }
    });

    const keyInput = li.querySelector(".selectOptionsListItem__key") as HTMLInputElement;
    keyInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        this.#createRow(undefined, true);
      }
    });
    keyInput.value = option ? option.key : "";

    const valueInput = li.querySelector(".selectOptionsListItem__value") as HTMLInputElement;
    valueInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        this.#createRow(undefined, true);
      }
    });

    const hasI18nValues = option && !Object.hasOwn(option.value, 0);

    initI18n(identify(valueInput), hasI18nValues ? option.value : {}, this.#languages, false);

    if (!hasI18nValues) {
      valueInput.value = option?.value[0] ?? "";
    }

    if (autoFocus) {
      keyInput.focus();
    }
  }

  #initializeSorting(): void {
    new Sortable(this.#list, {
      direction: "vertical",
      animation: 150,
      fallbackOnBody: true,
      draggable: "li",
      handle: ".selectOptionsListItem__handle",
    });
  }

  #setHiddenValue(formField: HTMLInputElement): void {
    const data: Data[] = [];

    this.#list.querySelectorAll(".selectOptionsListItem").forEach((li) => {
      const key = li.querySelector<HTMLInputElement>(".selectOptionsListItem__key")!.value;
      const valueInput = li.querySelector<HTMLInputElement>(".selectOptionsListItem__value")!;

      data.push({
        key,
        value: Object.fromEntries(getValues(valueInput.id)),
      });
    });

    formField.value = JSON.stringify(data);
  }
}

export function setup(formField: HTMLInputElement, languages: Languages): void {
  new SelectOptions(formField, languages);
}
