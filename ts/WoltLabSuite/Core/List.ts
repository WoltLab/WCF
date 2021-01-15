/**
 * List implementation relying on an array or if supported on a Set to hold values.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  List (alias)
 * @module  WoltLabSuite/Core/List
 */

import * as Core from "./Core";

/** @deprecated 5.4 Use a `Set` instead. */
class List<T = any> {
  private _set = new Set<T>();

  /**
   * Appends an element to the list, silently rejects adding an already existing value.
   */
  add(value: T): void {
    this._set.add(value);
  }

  /**
   * Removes all elements from the list.
   */
  clear(): void {
    this._set.clear();
  }

  /**
   * Removes an element from the list, returns true if the element was in the list.
   */
  delete(value: T): boolean {
    return this._set.delete(value);
  }

  /**
   * Invokes the `callback` for each element in the list.
   */
  forEach(callback: (value: T) => void): void {
    this._set.forEach(callback);
  }

  /**
   * Returns true if the list contains the element.
   */
  has(value: T): boolean {
    return this._set.has(value);
  }

  get size(): number {
    return this._set.size;
  }
}

Core.enableLegacyInheritance(List);

export = List;
