/**
 * Handles the dropdowns of form fields with a suffix.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import UiSimpleDropdown from "../../../Ui/Dropdown/Simple";
import * as EventHandler from "../../../Event/Handler";

type DestroyDropdownData = {
  formId: string;
};

class SuffixFormField {
  protected readonly _formId: string;
  protected readonly _suffixField: HTMLInputElement;
  protected readonly _suffixDropdownMenu: HTMLElement;
  protected readonly _suffixDropdownToggle: HTMLElement;

  constructor(formId: string, suffixFieldId: string) {
    this._formId = formId;

    this._suffixField = document.getElementById(suffixFieldId)! as HTMLInputElement;
    this._suffixDropdownMenu = UiSimpleDropdown.getDropdownMenu(suffixFieldId + "_dropdown")!;
    this._suffixDropdownToggle = UiSimpleDropdown.getDropdown(suffixFieldId + "_dropdown")!.getElementsByClassName(
      "dropdownToggle",
    )[0] as HTMLInputElement;
    Array.from(this._suffixDropdownMenu.children).forEach((listItem: HTMLLIElement) => {
      listItem.addEventListener("click", (ev) => this._changeSuffixSelection(ev));
    });

    EventHandler.add("WoltLabSuite/Core/Form/Builder/Manager", "afterUnregisterForm", (data) =>
      this._destroyDropdown(data),
    );
  }

  /**
   * Handles changing the suffix selection.
   */
  protected _changeSuffixSelection(event: MouseEvent): void {
    const target = event.currentTarget! as HTMLElement;
    if (target.classList.contains("disabled")) {
      return;
    }

    Array.from(this._suffixDropdownMenu.children).forEach((listItem: HTMLLIElement) => {
      if (listItem === target) {
        listItem.classList.add("active");
      } else {
        listItem.classList.remove("active");
      }
    });

    this._suffixField.value = target.dataset.value!;
    this._suffixDropdownToggle.innerHTML = target.dataset.label! + ' <fa-icon name="caret-down" solid></fa-icon>';
  }

  /**
   * Destroys the suffix dropdown if the parent form is unregistered.
   */
  protected _destroyDropdown(data: DestroyDropdownData): void {
    if (data.formId === this._formId) {
      UiSimpleDropdown.destroy(this._suffixDropdownMenu.id);
    }
  }
}

export = SuffixFormField;
