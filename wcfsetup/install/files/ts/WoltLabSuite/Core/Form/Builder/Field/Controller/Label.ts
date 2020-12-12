/**
 * Handles the JavaScript part of the label form field.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Controller/Label
 * @since 5.2
 */

import * as Core from "../../../../Core";
import * as DomUtil from "../../../../Dom/Util";
import * as Language from "../../../../Language";
import UiDropdownSimple from "../../../../Ui/Dropdown/Simple";
import { LabelFormFieldOptions } from "../../Data";

class Label {
  protected readonly _formFieldContainer: HTMLElement;
  protected readonly _input: HTMLInputElement;
  protected readonly _labelChooser: HTMLElement;
  protected readonly _options: LabelFormFieldOptions;

  constructor(fieldId: string, labelId: string, options: LabelFormFieldOptions) {
    this._formFieldContainer = document.getElementById(fieldId + "Container")!;
    this._labelChooser = this._formFieldContainer.getElementsByClassName("labelChooser")[0] as HTMLElement;
    this._options = Core.extend(
      {
        forceSelection: false,
        showWithoutSelection: false,
      },
      options,
    ) as LabelFormFieldOptions;

    this._input = document.createElement("input");
    this._input.type = "hidden";
    this._input.id = fieldId;
    this._input.name = fieldId;
    this._input.value = labelId;
    this._formFieldContainer.appendChild(this._input);

    const labelChooserId = DomUtil.identify(this._labelChooser);

    // init dropdown
    let dropdownMenu = UiDropdownSimple.getDropdownMenu(labelChooserId)!;
    if (dropdownMenu === null) {
      UiDropdownSimple.init(this._labelChooser.getElementsByClassName("dropdownToggle")[0] as HTMLElement);
      dropdownMenu = UiDropdownSimple.getDropdownMenu(labelChooserId)!;
    }

    let additionalOptionList: HTMLUListElement | null = null;
    if (this._options.showWithoutSelection || !this._options.forceSelection) {
      additionalOptionList = document.createElement("ul");
      dropdownMenu.appendChild(additionalOptionList);

      const dropdownDivider = document.createElement("li");
      dropdownDivider.classList.add("dropdownDivider");
      additionalOptionList.appendChild(dropdownDivider);
    }

    if (this._options.showWithoutSelection) {
      const listItem = document.createElement("li");
      listItem.dataset.labelId = "-1";
      this._blockScroll(listItem);
      additionalOptionList!.appendChild(listItem);

      const span = document.createElement("span");
      listItem.appendChild(span);

      const label = document.createElement("span");
      label.classList.add("badge", "label");
      label.innerHTML = Language.get("wcf.label.withoutSelection");
      span.appendChild(label);
    }

    if (!this._options.forceSelection) {
      const listItem = document.createElement("li");
      listItem.dataset.labelId = "0";
      this._blockScroll(listItem);
      additionalOptionList!.appendChild(listItem);

      const span = document.createElement("span");
      listItem.appendChild(span);

      const label = document.createElement("span");
      label.classList.add("badge", "label");
      label.innerHTML = Language.get("wcf.label.none");
      span.appendChild(label);
    }

    dropdownMenu.querySelectorAll("li:not(.dropdownDivider)").forEach((listItem: HTMLElement) => {
      listItem.addEventListener("click", (ev) => this._click(ev));

      if (labelId) {
        if (listItem.dataset.labelId === labelId) {
          this._selectLabel(listItem);
        }
      }
    });
  }

  _blockScroll(element: HTMLElement): void {
    element.addEventListener("wheel", (ev) => ev.preventDefault(), {
      passive: false,
    });
  }

  _click(event: Event): void {
    event.preventDefault();

    this._selectLabel(event.currentTarget as HTMLElement);
  }

  _selectLabel(label: HTMLElement): void {
    // save label
    let labelId = label.dataset.labelId;
    if (!labelId) {
      labelId = "0";
    }

    // replace button with currently selected label
    const displayLabel = label.querySelector("span > span")!;
    const button = this._labelChooser.querySelector(".dropdownToggle > span")!;
    button.className = displayLabel.className;
    button.textContent = displayLabel.textContent;

    this._input.value = labelId;
  }
}

Core.enableLegacyInheritance(Label);

export = Label;
