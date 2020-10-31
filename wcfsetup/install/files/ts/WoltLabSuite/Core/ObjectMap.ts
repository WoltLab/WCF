/**
 * Simple `object` to `object` map using a WeakMap.
 *
 * If you're looking for a dictionary with string keys, please see `WoltLabSuite/Core/Dictionary`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  ObjectMap (alias)
 * @module  WoltLabSuite/Core/ObjectMap
 */

import * as Core from "./Core";

/** @deprecated 5.4 Use a `WeakMap` instead. */
class ObjectMap {
  private _map = new WeakMap<object, object>();

  /**
   * Sets a new key with given value, will overwrite an existing key.
   */
  set(key: object, value: object): void {
    if (typeof key !== "object" || key === null) {
      throw new TypeError("Only objects can be used as key");
    }

    if (typeof value !== "object" || value === null) {
      throw new TypeError("Only objects can be used as value");
    }

    this._map.set(key, value);
  }

  /**
   * Removes a key from the map.
   */
  delete(key: object): void {
    this._map.delete(key);
  }

  /**
   * Returns true if dictionary contains a value for given key.
   */
  has(key: object): boolean {
    return this._map.has(key);
  }

  /**
   * Retrieves a value by key, returns undefined if there is no match.
   */
  get(key: object): object | undefined {
    return this._map.get(key);
  }
}

Core.enableLegacyInheritance(ObjectMap);

export = ObjectMap;
