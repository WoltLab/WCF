/**
 * Provides an inline editor for objects using a drop-down menu.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import {
  DropdownBuilderItemData,
  create as createDropdownMenu,
  setItems as setDropdownItems,
} from "WoltLabSuite/Core/Ui/Dropdown/Builder";
import { show as showNotification } from "WoltLabSuite/Core/Ui/Notification";
import { stringToBool } from "WoltLabSuite/Core/Core";
import UiDropdownSimple from "WoltLabSuite/Core/Ui/Dropdown/Simple";

export interface DropdownMenuItem {
  visible?: () => boolean;
  item: DropdownBuilderItemData;
}

const inlineEditors = new Map<HTMLElement, InlineEditor>();

export class InlineEditor {
  protected readonly element: HTMLElement;
  protected readonly dropdownToggle: HTMLElement;
  protected permissions: Record<string, boolean> = {};
  protected readonly dropdownMenu: HTMLUListElement;
  protected readonly menuItems: DropdownMenuItem[] = [];

  public constructor(element: HTMLElement, dropdownToggleSelector: string) {
    this.element = element;
    this.dropdownToggle = this.element.querySelector(dropdownToggleSelector) as HTMLElement;
    this.dropdownMenu = createDropdownMenu([]);

    // @see WoltLabSuite/Core/Ui/Dropdown/Builder::attach()
    UiDropdownSimple.initFragment(this.dropdownToggle, this.dropdownMenu);

    this.dropdownToggle.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      // Rebuild the menu to ensure the menu items are displayed correctly,
      // as states may change externally and cannot be detected automatically
      this.#rebuildDropdownMenu();

      UiDropdownSimple.toggleDropdown(this.dropdownToggle.id);
    });

    inlineEditors.set(this.element, this);
  }

  /**
   * Gets the state of a property from the element's dataset as a boolean.
   */
  public getState(propertyName: string): boolean {
    if (!Object.prototype.hasOwnProperty.call(this.element.dataset, propertyName)) {
      return false;
    }

    return stringToBool(this.element.dataset[propertyName]!);
  }

  /**
   * Updates the state of the element's dataset with the provided data.
   */
  public updateState(data: Record<string, boolean>): void {
    showNotification();

    Object.entries(data).forEach(([key, value]) => {
      this.element.dataset[key] = value ? "1" : "0";
    });
  }

  /**
   * Sets the permissions for the inline editor.
   */
  public setPermissions(permissions: Record<string, boolean>): void {
    this.permissions = permissions;
  }

  /**
   * Gets the permissions for the inline editor.
   */
  public getPermissions(): Record<string, boolean> {
    return this.permissions;
  }

  /**
   * Adds a menu item to the dropdown menu and rebuilds the menu.
   */
  public addMenuItem(menuItem: DropdownMenuItem): void {
    this.menuItems.push(menuItem);
  }

  /**
   * Adds multiple menu items to the dropdown menu and rebuilds the menu.
   */
  public addMenuItems(menuItems: DropdownMenuItem[]): void {
    this.menuItems.push(...menuItems);
  }

  /**
   * Rebuilds the dropdown menu based on the current menu items and their visibility.
   */
  #rebuildDropdownMenu(): void {
    const dropdownMenuItems = this.menuItems
      .filter((item) => {
        return item.visible === undefined || item.visible();
      })
      .map((item) => item.item);

    if (dropdownMenuItems.length === 0) {
      this.dropdownMenu.innerHTML = "";
    } else {
      setDropdownItems(this.dropdownMenu, dropdownMenuItems);
    }
  }
}

/**
 * Gets the inline editor instance associated with the given element.
 */
export function getInlineEditor(element: HTMLElement): InlineEditor | undefined {
  return inlineEditors.get(element);
}
