/**
 * Handles the low level management of language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Language/Store
 */

const languageItems = new Map<string, Phrase>();

/**
 * Fetches the language item specified by the given key.
 *
 * The given parameters are passed to the compiled Phrase.
 */
export function get(key: string, parameters: object = {}): string {
  const value = languageItems.get(key);
  if (value === undefined) {
    return key;
  }

  return value(parameters);
}

/**
 * Adds a single language item to the store.
 */
export function add(key: string, value: Phrase): void {
  languageItems.set(key, value);
}

/**
 * Represents a compiled phrase.
 */
export type Phrase = (parameters: object) => string;
