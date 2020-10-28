/**
 * List implementation relying on an array or if supported on a Set to hold values.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  List (alias)
 * @module  WoltLabSuite/Core/List
 */

/** @deprecated 5.4 Use a `Set` instead. */
class List {
  private _set = new Set<any>();

  /**
   * Appends an element to the list, silently rejects adding an already existing value.
   */
  add(value: any): void {
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
  delete(value: any): boolean {
    return this._set.delete(value);
  }

  /**
   * Invokes the `callback` for each element in the list.
   */
  forEach(callback: (value: any) => void): void {
    this._set.forEach(callback);
  }

  /**
   * Returns true if the list contains the element.
   */
  has(value: any): boolean {
    return this._set.has(value);
  }

  get size(): number {
    return this._set.size;
  }
}

export = List;
