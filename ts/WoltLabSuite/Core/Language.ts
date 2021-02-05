/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */

import Template from "./Template";

import { add as addToStore, Phrase } from "./Language/Store";

export { get } from "./Language/Store";

/**
 * Adds all the language items in the given object to the store.
 */
export function addObject(object: LanguageItems): void {
  Object.keys(object).forEach((key) => {
    add(key, object[key]);
  });
}

/**
 * Adds a single language item to the store.
 */
export function add(key: string, value: string): void {
  addToStore(key, compile(value));
}

/**
 * Compiles the given value into a phrase.
 */
function compile(value: string): Phrase {
  try {
    const template = new Template(value);
    return template.fetch.bind(template);
  } catch (e) {
    return function () {
      return value;
    };
  }
}

interface LanguageItems {
  [key: string]: string;
}
