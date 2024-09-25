/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { Template } from "./Template.js";

import { add as addToStore, Phrase } from "./LanguageStore.js";

export { get as getPhrase } from "./LanguageStore.js";

/**
 * Adds a single language item to the store.
 */
export function registerPhrase(key: string, value: string): void {
  if (typeof value === "string") {
    addToStore(key, compile(value));
  } else {
    // Historically a few items that are added to the language store do not represent actual phrases, but
    // instead contain a collection (i.e. Array) of items. Most notably these are entries related to date
    // processing, containg lists of localized month / weekday names.
    //
    // Despite this method technically only taking `string`s as the `value` we need to correctly handle
    // them which we do by simply storing a function that returns the value as-is.
    addToStore(key, function () {
      return value;
    });
  }
}

/**
 * Compiles the given value into a phrase.
 */
function compile(value: string): Phrase {
  if (!value.includes("{")) {
    return function () {
      return value;
    };
  }

  try {
    const template = new Template(value);
    return template.fetch.bind(template);
  } catch {
    return function () {
      return value;
    };
  }
}
