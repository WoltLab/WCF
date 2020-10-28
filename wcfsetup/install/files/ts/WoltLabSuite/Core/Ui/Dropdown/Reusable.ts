/**
 * Simple interface to work with reusable dropdowns that are not bound to a specific item.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/ReusableDropdown (alias)
 * @module  WoltLabSuite/Core/Ui/Dropdown/Reusable
 */

import UiDropdownSimple from './Simple';
import { NotificationCallback } from './Data';

const _dropdowns = new Map<string, string>();
let _ghostElementId = 0;

/**
 * Returns dropdown name by internal identifier.
 */
function getDropdownName(identifier: string): string {
  if (!_dropdowns.has(identifier)) {
    throw new Error("Unknown dropdown identifier '" + identifier + "'");
  }

  return _dropdowns.get(identifier)!;
}

/**
 * Initializes a new reusable dropdown.
 */
export function init(identifier: string, menu: HTMLElement): void {
  if (_dropdowns.has(identifier)) {
    return;
  }

  const ghostElement = document.createElement('div');
  ghostElement.id = 'reusableDropdownGhost' + _ghostElementId++;

  UiDropdownSimple.initFragment(ghostElement, menu);

  _dropdowns.set(identifier, ghostElement.id);
}

/**
 * Returns the dropdown menu element.
 */
export function getDropdownMenu(identifier: string): HTMLElement {
  return UiDropdownSimple.getDropdownMenu(getDropdownName(identifier))!;
}

/**
 * Registers a callback invoked upon open and close.
 */
export function registerCallback(identifier: string, callback: NotificationCallback): void {
  UiDropdownSimple.registerCallback(getDropdownName(identifier), callback);
}

/**
 * Toggles a dropdown.
 */
export function toggleDropdown(identifier: string, referenceElement: HTMLElement): void {
  UiDropdownSimple.toggleDropdown(getDropdownName(identifier), referenceElement);
}
