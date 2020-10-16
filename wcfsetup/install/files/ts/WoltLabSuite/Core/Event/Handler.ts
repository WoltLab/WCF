/**
 * Versatile event system similar to the WCF-PHP counter part.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  EventHandler (alias)
 * @module  WoltLabSuite/Core/Event/Handler
 */

import * as Core from '../Core';
import Devtools from '../Devtools';

const _listeners = new Map<string, Map<string, Map<string, Callback>>>();

/**
 * Registers an event listener.
 */
export function add(identifier: string, action: string, callback: Callback): string {
  if (typeof callback !== 'function') {
    throw new TypeError(`Expected a valid callback for '${action}'@'${identifier}'.`);
  }

  let actions = _listeners.get(identifier);
  if (actions === undefined) {
    actions = new Map<string, Map<string, Callback>>();
    _listeners.set(identifier, actions);
  }

  let callbacks = actions.get(action);
  if (callbacks === undefined) {
    callbacks = new Map<string, Callback>();
    actions.set(action, callbacks);
  }

  const uuid = Core.getUuid();
  callbacks.set(uuid, callback);

  return uuid;
}

/**
 * Fires an event and notifies all listeners.
 */
export function fire(identifier: string, action: string, data?: object): void {
  Devtools._internal_.eventLog(identifier, action);

  data = data || {};

  _listeners.get(identifier)
    ?.get(action)
    ?.forEach(callback => callback(data));
}

/**
 * Removes an event listener, requires the uuid returned by add().
 */
export function remove(identifier: string, action: string, uuid: string): void {
  _listeners.get(identifier)
    ?.get(action)
    ?.delete(uuid);
}

/**
 * Removes all event listeners for given action. Omitting the second parameter will
 * remove all listeners for this identifier.
 */
export function removeAll(identifier: string, action?: string): void {
  if (typeof action !== 'string') action = undefined;

  const actions = _listeners.get(identifier);
  if (actions === undefined) {
    return;
  }

  if (action === undefined) {
    _listeners.delete(identifier);
  } else {
    actions.delete(action);
  }
}

/**
 * Removes all listeners registered for an identifier and ending with a special suffix.
 * This is commonly used to unbound event handlers for the editor.
 *
 * @param       {string}        identifier      event identifier
 * @param       {string}        suffix          action suffix
 */
export function removeAllBySuffix(identifier: string, suffix: string): void {
  const actions = _listeners.get(identifier);
  if (actions === undefined) {
    return;
  }

  suffix = '_' + suffix;
  const length = suffix.length * -1;
  actions.forEach((callbacks, action) => {
    if (action.substr(length) === suffix) {
      removeAll(identifier, action);
    }
  });
}

type Callback = (...args: any[]) => void;
