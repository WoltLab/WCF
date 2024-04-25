/**
 * Handles the JavaScript part of a multiline item list form field.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 */

import * as UiItemList from "WoltLabSuite/Core/Ui/ItemList/LineBreakSeparatedText";
import { getPhrase } from "WoltLabSuite/Core/Language";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

const _data = new Map<string, MultilineItemListFormField>();

export class MultilineItemListFormField extends UiItemList.UiItemListLineBreakSeparatedText {
  /**
   @inheritDoc
   */
  constructor(itemList: HTMLUListElement, options: UiItemList.LineBreakSeparatedTextOptions) {
    super(itemList, options);
    if (options.submitFieldName != null) {
      _data.set(options.submitFieldName, this);
    } else {
      _data.set(this.submitField!.id, this);
    }
  }

  public getItems(): Set<string> {
    return this.items;
  }

  /**
   * @inheritDoc
   */
  protected initValues() {
    super.initValues();
    Array.from(this.itemList.children).forEach((el: HTMLElement) => {
      el.querySelector(".jsEditItem")!.addEventListener("click", (ev) => {
        this.editItem(ev);
      });
    });
  }

  /**
   * Begin the editing of an item.
   */
  protected editItem(event: Event): void {
    if (this.uiDisabled) {
      return;
    }
    const button = event.currentTarget as HTMLElement;
    const li = button.closest("li")!;
    const deleteButton = li.querySelector<HTMLButtonElement>(".jsDeleteItem")!;
    const valueSpan = li.querySelector<HTMLSpanElement>("span")!;
    const value = button.closest("li")!.dataset.value!;
    //hide old buttons
    DomUtil.hide(deleteButton);
    DomUtil.hide(button);
    DomUtil.hide(valueSpan);
    //insert temporary input field and buttons
    const input = document.createElement("input");
    input.type = "text";
    input.value = value;
    const saveButton = document.createElement("button");
    saveButton.classList.add("jsSaveItem", "jsTooltip");
    saveButton.title = getPhrase("wcf.global.button.save");
    let icon = document.createElement("fa-icon");
    icon.setIcon("save");
    saveButton.append(icon);

    const cancelButton = document.createElement("button");
    cancelButton.classList.add("jsCancelItem", "jsTooltip");
    cancelButton.title = getPhrase("wcf.global.button.cancel");
    icon = document.createElement("fa-icon");
    icon.setIcon("times");
    cancelButton.append(icon);

    const endCallback = () => {
      //remove temporary elements
      input.remove();
      saveButton.remove();
      cancelButton.remove();
      //display old elements
      DomUtil.show(deleteButton);
      DomUtil.show(button);
      DomUtil.show(valueSpan);
    };

    const saveCallback = (saveEvent: Event) => {
      saveEvent.preventDefault();

      if (this.uiDisabled) {
        return;
      }
      const newValue = input.value.trim();

      if (newValue === "") {
        DomUtil.innerError(input.parentElement!, getPhrase("wcf.global.form.error.empty"));
      } else if (!this.items.has(newValue) || newValue === value) {
        //remove error message
        DomUtil.innerError(input.parentElement!, "");

        //insert new value
        button.closest("li")!.dataset.value = newValue;
        valueSpan.textContent = newValue;
        this.items.delete(value);
        this.items.add(newValue);

        endCallback();
      } else {
        DomUtil.innerError(
          input.parentElement!,
          getPhrase("wcf.acp.option.type.lineBreakSeparatedText.error.duplicate", {
            item: newValue,
          }),
          true,
        );
      }

      input.focus();
    };

    input.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        saveCallback(event);
      }
    });

    saveButton.addEventListener("click", (ev) => {
      saveCallback(ev);
    });

    cancelButton.addEventListener("click", () => {
      endCallback();
    });

    li.append(cancelButton);
    li.append(saveButton);
    li.append(input);
    input.focus();
  }

  /**
   * @inheritDoc
   */
  protected insertItem(item: string): void {
    this.items.add(item);

    const itemElement = document.createElement("li");
    itemElement.dataset.value = item;

    const deleteButton = document.createElement("button");
    deleteButton.type = "button";
    deleteButton.classList.add("jsDeleteItem", "jsTooltip");
    deleteButton.title = getPhrase("wcf.global.button.delete");
    deleteButton.addEventListener("click", (ev) => {
      this.deleteItem(ev);
    });
    let icon = document.createElement("fa-icon");
    icon.setIcon("trash");
    deleteButton.append(icon);
    itemElement.append(deleteButton);

    const editButton = document.createElement("button");
    editButton.type = "button";
    editButton.classList.add("jsEditItem", "jsTooltip");
    editButton.title = getPhrase("wcf.global.button.edit");
    editButton.addEventListener("click", (ev) => {
      this.editItem(ev);
    });
    icon = document.createElement("fa-icon");
    icon.setIcon("edit");
    editButton.append(icon);
    itemElement.append(editButton);

    itemElement.append(" ");

    const label = document.createElement("span");
    label.innerText = item;
    itemElement.append(label);

    const nextElement = Array.from(this.itemList.children).find((el: HTMLElement) => el.dataset.value! > item);

    if (nextElement) {
      this.itemList.insertBefore(itemElement, nextElement);
    } else {
      this.itemList.append(itemElement);
    }

    this.showList();
  }

  /**
   * @inheritDoc
   */
  protected submit(): void {
    if (this.submitField) {
      this.submitField.value = Array.from(this.items).join("\n");
    } else {
      Array.from(this.items).forEach((value) => {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = this.options.submitFieldName!;
        input.value = value;
        this.itemList.parentElement!.append(input);
      });
    }
  }
}

export function getValues(elementId: string): Set<string> {
  if (!_data.has(elementId)) {
    throw new Error(`Element id '${elementId}' is unknown.`);
  }

  return _data.get(elementId)!.getItems();
}
