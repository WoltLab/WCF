/**
 * Provides an inline editor for objects with a dropdown menu.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { DropdownBuilderItemData, create as createDropDownMenu } from "WoltLabSuite/Core/Ui/Dropdown/Builder";
import { stringToBool } from "WoltLabSuite/Core/Core";

export abstract class InlineEditor {
  protected readonly element: HTMLElement;
  protected readonly dropdownToggle: HTMLElement;
  protected readonly dropdownMenu: HTMLUListElement | null = null;

  protected constructor(element: HTMLElement, dropdownToggleSelector: string) {
    this.element = element;
    this.dropdownToggle = this.element.querySelector(dropdownToggleSelector) as HTMLElement;
    this.dropdownMenu = createDropDownMenu(this.getDropdownOptions());
    this.dropdownToggle.parentElement!.appendChild(this.dropdownMenu);
  }

  abstract getDropdownOptions(): DropdownBuilderItemData[];

  public getPermission(permission: string): boolean {
    if (!Object.prototype.hasOwnProperty.call(this.element.dataset, permission)) {
      return false;
    }

    return stringToBool(this.element.dataset[permission]!);
  }
}
