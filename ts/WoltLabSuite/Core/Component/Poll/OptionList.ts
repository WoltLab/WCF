/**
 * Manages the list of options of a poll, each option can either have a single
 * value or a value per language.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { identify } from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { getValues, init as initI18n, unregister as unregisterI18n } from "WoltLabSuite/Core/Language/Input";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import Sortable from "sortablejs";

export type PollOptionData = {
  optionID: number;
  optionValue: Record<string, string>;
};

type Languages = Record<string, string>;

class PollOptionList {
  readonly #languages: Languages;
  readonly #list: HTMLOListElement;
  readonly #addButton: HTMLButtonElement;
  readonly #maxOptions: number;
  readonly #template: HTMLTemplateElement;

  constructor(formField: HTMLInputElement, languages: Languages, maxOptions: number) {
    const template = document.getElementById(`${formField.id}_template`);
    if (!(template instanceof HTMLTemplateElement)) {
      throw new Error(`Cannot find the template '${formField.id}_template' for the poll options.`, {
        cause: {
          formField,
        },
      });
    }

    this.#languages = languages;
    this.#maxOptions = maxOptions;
    this.#template = template;

    this.#list = document.createElement("ol");
    this.#list.classList.add("pollOptionList");
    formField.insertAdjacentElement("afterend", this.#list);

    this.#addButton = document.createElement("button");
    this.#addButton.type = "button";
    this.#addButton.classList.add("button", "small", "pollOptionList__addItem");
    this.#addButton.textContent = getPhrase("wcf.poll.button.addOption");
    this.#addButton.addEventListener("click", () => {
      this.#createRow(undefined, true);
    });
    this.#list.insertAdjacentElement("afterend", this.#addButton);

    const options = formField.value ? (JSON.parse(formField.value) as PollOptionData[]) : [];
    options.forEach((option) => this.#createRow(option));
    if (options.length === 0) {
      this.#createRow();
      this.#createRow();
    }

    new Sortable(this.#list, {
      direction: "vertical",
      animation: 150,
      fallbackOnBody: true,
      draggable: "li",
      handle: ".pollOptionListItem__handle",
    });
  }

  #createRow(option?: PollOptionData, autoFocus: boolean = false, previousRow?: HTMLLIElement): void {
    if (this.#list.childElementCount >= this.#maxOptions) {
      return;
    }

    const li = document.createElement("li");
    li.classList.add("pollOptionListItem");
    li.dataset.optionId = option ? option.optionID.toString() : "0";
    li.append(this.#template.content.cloneNode(true));

    if (previousRow) {
      previousRow.after(li);
    } else {
      this.#list.append(li);
    }

    const valueInput = li.querySelector(".pollOptionListItem__value") as HTMLInputElement;
    identify(valueInput);

    li.querySelector(".pollOptionListItem__remove")!.addEventListener("click", () => {
      const siblingRow = li.nextElementSibling ?? li.previousElementSibling;

      if (this.#hasLanguageSelection()) {
        unregisterI18n(valueInput.id);
      }
      li.remove();

      if (siblingRow === null) {
        this.#createRow(undefined, true);
      } else {
        siblingRow.querySelector<HTMLInputElement>(".pollOptionListItem__value")!.focus();
      }

      this.#updateAddButton();
    });

    valueInput.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        this.#createRow(undefined, true, li);
      }
    });

    const hasI18nValues = option !== undefined && !Object.hasOwn(option.optionValue, 0);
    if (this.#hasLanguageSelection()) {
      // `initI18n()` expects HTML-escaped values, but the values are raw JSON data.
      const i18nValues = hasI18nValues
        ? Object.fromEntries(
            Object.entries(option.optionValue).map(([languageId, value]) => [languageId, escapeHTML(value)]),
          )
        : {};
      initI18n(valueInput.id, i18nValues, this.#languages, false);
    }

    if (!hasI18nValues) {
      valueInput.value = option?.optionValue[0] ?? "";
    }

    if (autoFocus) {
      valueInput.focus();
    }

    this.#updateAddButton();
  }

  #hasLanguageSelection(): boolean {
    return Object.keys(this.#languages).length > 1;
  }

  #updateAddButton(): void {
    this.#addButton.disabled = this.#list.childElementCount >= this.#maxOptions;
  }

  destroy(): void {
    if (!this.#hasLanguageSelection()) {
      return;
    }

    this.#list.querySelectorAll<HTMLInputElement>(".pollOptionListItem__value").forEach((valueInput) => {
      unregisterI18n(valueInput.id);
    });
  }

  getData(): PollOptionData[] {
    const data: PollOptionData[] = [];

    this.#list.querySelectorAll<HTMLLIElement>(".pollOptionListItem").forEach((li) => {
      const valueInput = li.querySelector<HTMLInputElement>(".pollOptionListItem__value")!;

      data.push({
        optionID: parseInt(li.dataset.optionId!, 10),
        optionValue: this.#hasLanguageSelection()
          ? Object.fromEntries(getValues(valueInput.id))
          : { 0: valueInput.value },
      });
    });

    return data;
  }
}

const optionLists = new Map<string, PollOptionList>();

export function setup(formField: HTMLInputElement, languages: Languages, maxOptions: number): void {
  optionLists.set(formField.id, new PollOptionList(formField, languages, maxOptions));
}

/**
 * Returns the serialized options of the poll option list with the given id.
 */
export function getOptionListValue(fieldId: string): string {
  const optionList = optionLists.get(fieldId);
  if (optionList === undefined) {
    throw new Error(`Unknown poll option list '${fieldId}'.`);
  }

  return JSON.stringify(optionList.getData());
}

/**
 * Removes the poll option list with the given id, e.g. after the dialog has
 * been closed.
 */
export function removeOptionList(fieldId: string): void {
  optionLists.get(fieldId)?.destroy();
  optionLists.delete(fieldId);
}
