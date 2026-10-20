import { identify } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { getValues, init as initI18n, unregister as unregisterI18n } from "WoltLabSuite/Core/Language/Input";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import Sortable from "sortablejs";

type Data = {
  key: string;
  value: Record<string, string>;
};

type Languages = Record<string, string>;

class SelectOptions {
  readonly #immutable: boolean;
  readonly #languages: Languages;
  readonly #list: HTMLUListElement;
  readonly #template: HTMLTemplateElement;

  constructor(formField: HTMLInputElement, languages: Languages) {
    if (formField.form === null) {
      throw new Error("Cannot create select options for a form field that is not part of a form.", {
        cause: {
          formField,
        },
      });
    }

    const template = document.getElementById(`${formField.id}_template`);
    if (!(template instanceof HTMLTemplateElement)) {
      throw new Error(`Cannot find the template '${formField.id}_template' for the select options.`, {
        cause: {
          formField,
        },
      });
    }

    this.#immutable = formField.disabled;
    this.#languages = languages;
    this.#template = template;

    this.#list = this.#createUi(formField);
    this.#createListItems(formField);

    if (this.#immutable) {
      return;
    }

    this.#initializeSorting();

    formField.form.addEventListener("submit", () => {
      this.#setHiddenValue(formField);
    });
  }

  #createUi(formField: HTMLInputElement): HTMLUListElement {
    const ul = document.createElement("ul");
    ul.classList.add("selectOptionsList");
    formField.insertAdjacentElement("afterend", ul);

    if (this.#immutable) {
      return ul;
    }

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
    } else if (!this.#immutable) {
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

  #createRow(option?: Data, autoFocus: boolean = false, previousRow?: HTMLLIElement): void {
    const li = document.createElement("li");
    li.classList.add("selectOptionsListItem");
    li.append(this.#template.content.cloneNode(true));

    if (previousRow) {
      previousRow.after(li);
    } else {
      this.#list.append(li);
    }

    const removeButton = li.querySelector(".selectOptionsListItem__remove") as HTMLButtonElement;
    const keyInput = li.querySelector(".selectOptionsListItem__key") as HTMLInputElement;
    keyInput.value = option ? option.key : "";
    const valueInput = li.querySelector(".selectOptionsListItem__value") as HTMLInputElement;

    if (this.#immutable) {
      li.querySelector(".selectOptionsListItem__handle")!.remove();
      removeButton.remove();
      keyInput.disabled = true;
      valueInput.disabled = true;
    } else {
      removeButton.addEventListener("click", () => {
        const siblingRow = li.nextElementSibling ?? li.previousElementSibling;

        unregisterI18n(valueInput.id);
        li.remove();

        if (siblingRow === null) {
          this.#createRow(undefined, true);
        } else {
          siblingRow.querySelector<HTMLInputElement>(".selectOptionsListItem__key")!.focus();
        }
      });

      // The selected values of multiple selections are stored comma-separated.
      const validateKey = () => {
        keyInput.setCustomValidity(
          keyInput.value.includes(",")
            ? getPhrase("wcf.form.selectOptions.error.invalidKey", { key: keyInput.value })
            : "",
        );
      };
      validateKey();
      keyInput.addEventListener("input", () => validateKey());

      keyInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
          event.preventDefault();
          this.#createRow(undefined, true, li);
        }
      });

      valueInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
          event.preventDefault();
          this.#createRow(undefined, true, li);
        }
      });
    }

    const hasI18nValues = option && !Object.hasOwn(option.value, 0);

    // Immutable rows only offer the language selection to view existing
    // localized values, it must not be possible to toggle i18n.
    if (!this.#immutable || hasI18nValues) {
      // `initI18n()` expects HTML-escaped values, but the values are raw JSON data.
      const i18nValues = hasI18nValues
        ? Object.fromEntries(Object.entries(option.value).map(([languageId, value]) => [languageId, escapeHTML(value)]))
        : {};
      initI18n(identify(valueInput), i18nValues, this.#languages, this.#immutable);
    }

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
