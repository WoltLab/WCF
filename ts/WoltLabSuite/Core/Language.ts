/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */

export function getPhrase(key: string, parameters: object = {}): string {
  return window.WoltLabLanguage.getPhrase(key, parameters);
}

export function registerPhrase(key: string, value: string): void {
  window.WoltLabLanguage.registerPhrase(key, value);
}

/**
 * @deprecated 6.0 Use `getPhrase()` instead
 */
export function get(key: string, parameters: object = {}): string {
  return getPhrase(key, parameters);
}

/**
 * @deprecated 6.0 Use `registerPhrase()` instead
 */
export function add(key: string, value: string): void {
  registerPhrase(key, value);
}

/**
 * @deprecated 6.0 Use `registerPhrase()` instead
 */
export function addObject(object: { [key: string]: string }): void {
  Object.entries(object).forEach(([key, value]) => {
    registerPhrase(key, value);
  });
}
