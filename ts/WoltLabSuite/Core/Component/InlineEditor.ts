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
  attach as attachDropdownMenu,
  setItems as setDropdownItems,
} from "WoltLabSuite/Core/Ui/Dropdown/Builder";
import { stringToBool } from "WoltLabSuite/Core/Core";

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
    attachDropdownMenu(this.dropdownMenu, this.dropdownToggle);

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
    Object.entries(data).forEach(([key, value]) => {
      this.element.dataset[key] = value ? "1" : "0";
    });

    this.rebuildDropdownMenu();
  }

  /**
   * Sets the permissions for the inline editor.
   */
  public setPermissions(permissions: Record<string, boolean>): void {
    this.permissions = permissions;

    this.rebuildDropdownMenu();
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

    this.rebuildDropdownMenu();
  }

  /**
   * Adds multiple menu items to the dropdown menu and rebuilds the menu.
   */
  public addMenuItems(menuItems: DropdownMenuItem[]): void {
    this.menuItems.push(...menuItems);

    this.rebuildDropdownMenu();
  }

  /**
   * Rebuilds the dropdown menu based on the current menu items and their visibility.
   */
  public rebuildDropdownMenu(): void {
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
