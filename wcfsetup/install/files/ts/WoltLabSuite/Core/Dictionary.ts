/**
 * Dictionary implementation relying on an object or if supported on a Map to hold key => value data.
 *
 * If you're looking for a dictionary with object keys, please see `WoltLabSuite/Core/ObjectMap`.
 *
 * This is a legacy implementation, that does not implement all methods of `Map`, furthermore it has
 * the side effect of converting all numeric keys to string values, treating 1 === "1".
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dictionary (alias)
 * @module  WoltLabSuite/Core/Dictionary
 * @deprecated 5.4
 */
/**
 * @constructor
 */
class Dictionary {
  private readonly _dictionary = new Map<number | string, any>();

  /**
   * Sets a new key with given value, will overwrite an existing key.
   */
  set(key: number | string, value: any): void {
    this._dictionary.set(key.toString(), value);
  }

  /**
   * Removes a key from the dictionary.
   */
  delete(key: number | string): boolean {
    return this._dictionary.delete(key.toString());
  }

  /**
   * Returns true if dictionary contains a value for given key and is not undefined.
   */
  has(key: number | string): boolean {
    return this._dictionary.has(key.toString());
  }

  /**
   * Retrieves a value by key, returns undefined if there is no match.
   */
  get(key: number | string): unknown {
    return this._dictionary.get(key.toString());
  }

  /**
   * Iterates over the dictionary keys and values, callback function should expect the
   * value as first parameter and the key name second.
   */
  forEach(callback: (value: any, key: string) => void): void {
    if (typeof callback !== 'function') {
      throw new TypeError('forEach() expects a callback as first parameter.');
    }

    this._dictionary.forEach(callback);
  }

  /**
   * Merges one or more Dictionary instances into this one.
   */
  merge(...dictionaries: Dictionary[]): void {
    for (let i = 0, length = dictionaries.length; i < length; i++) {
      const dictionary = dictionaries[i];

      dictionary.forEach((value, key) => this.set(key, value));
    }
  }

  /**
   * Returns the object representation of the dictionary.
   */
  toObject(): object {
    const object = {};
    this._dictionary.forEach((value, key) => object[key] = value);

    return object;
  }

  /**
   * Creates a new Dictionary based on the given object.
   * All properties that are owned by the object will be added
   * as keys to the resulting Dictionary.
   */
  static fromObject(object: object): Dictionary {
    const result = new Dictionary();

    for (const key in object) {
      if (object.hasOwnProperty(key)) {
        result.set(key, object[key]);
      }
    }

    return result;
  }

  get size(): number {
    return this._dictionary.size;
  }
}

export = Dictionary;
