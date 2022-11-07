/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */

export function get(key: string, parameters: object = {}): string {
  return window.WoltLabLanguage.get(key, parameters);
}

export function add(key: string, value: string): void {
  window.WoltLabLanguage.add(key, value);
}

export function addObject(object: { [key: string]: string }): void {
  window.WoltLabLanguage.addObject(object);
}
