/**
 * Simple API to store and invoke multiple callbacks per identifier.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  CallbackList (alias)
 * @module  WoltLabSuite/Core/CallbackList
 */

type Callback = () => void;

class CallbackList {
  private readonly _callbacks = new Map<string, Callback[]>();

  /**
   * Adds a callback for given identifier.
   */
  add(identifier: string, callback: Callback): void {
    if (typeof callback !== 'function') {
      throw new TypeError('Expected a valid callback as second argument for identifier \'' + identifier + '\'.');
    }

    if (!this._callbacks.has(identifier)) {
      this._callbacks.set(identifier, []);
    }

    this._callbacks.get(identifier)!.push(callback);
  }

  /**
   * Removes all callbacks registered for given identifier
   */
  remove(identifier: string): void {
    this._callbacks.delete(identifier);
  }

  /**
   * Invokes callback function on each registered callback.
   */
  forEach(identifier: string | null, callback: Callback): void {
    if (identifier === null) {
      this._callbacks.forEach(function (callbacks, identifier) {
        callbacks.forEach(callback);
      });
    } else {
      this._callbacks.get(identifier)?.forEach(callback);
    }
  }
}

export = CallbackList;
