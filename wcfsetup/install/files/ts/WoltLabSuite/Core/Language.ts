/**
 * Manages language items.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Language (alias)
 * @module  WoltLabSuite/Core/Language
 */
import Dictionary from './Dictionary';
import Template from './Template';

const _languageItems = new Dictionary();

/**
 * Adds all the language items in the given object to the store.
 */
export function addObject(object: object): void {
  _languageItems.merge(Dictionary.fromObject(object));
}

/**
 * Adds a single language item to the store.
 */
export function add(key: string, value: string): void {
  _languageItems.set(key, value);
}

/**
 * Fetches the language item specified by the given key.
 * If the language item is a string it will be evaluated as
 * WoltLabSuite/Core/Template with the given parameters.
 *
 * @param  {string}  key    Language item to return.
 * @param  {Object=}  parameters  Parameters to provide to WoltLabSuite/Core/Template.
 * @return  {string}
 */
export function get(key: string, parameters?: object): string {
  let value = _languageItems.get(key);
  if (value === undefined) {
    return key;
  }

  // fetch Template, as it cannot be provided because of a circular dependency
  if (Template === undefined) { //@ts-ignore
    Template = require('./Template');
  }

  if (typeof value === 'string') {
    // lazily convert to WCF.Template
    try {
      _languageItems.set(key, new Template(value));
    } catch (e) {
      _languageItems.set(key, new Template('{literal}' + value.replace(/{\/literal}/g, '{/literal}{ldelim}/literal}{literal}') + '{/literal}'));
    }
    value = _languageItems.get(key);
  }

  if (value instanceof Template) {
    value = (value as Template).fetch(parameters || {});
  }

  return value as string;
}
