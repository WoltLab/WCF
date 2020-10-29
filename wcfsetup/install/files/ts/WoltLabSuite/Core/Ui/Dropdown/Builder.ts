/**
 * Simplified and consistent dropdown creation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Dropdown/Builder
 */

import * as Core from '../../Core';
import UiDropdownSimple from './Simple';

const _validIconSizes = [16, 24, 32, 48, 64, 96, 144];

function validateList(list: HTMLUListElement): void {
  if (!(list instanceof HTMLUListElement)) {
    throw new TypeError('Expected a reference to an <ul> element.');
  }

  if (!list.classList.contains('dropdownMenu')) {
    throw new Error('List does not appear to be a dropdown menu.');
  }
}

function buildItemFromData(data: DropdownBuilderItemData): HTMLLIElement {
  const item = document.createElement('li');

  // handle special `divider` type
  if (data === 'divider') {
    item.className = 'dropdownDivider';
    return item;
  }

  if (typeof data.identifier === 'string') {
    item.dataset.identifier = data.identifier;
  }

  const link = document.createElement('a');
  link.href = (typeof data.href === 'string') ? data.href : '#';
  if (typeof data.callback === 'function') {
    link.addEventListener('click', event => {
      event.preventDefault();

      data.callback!(link);
    });
  } else if (link.href === '#') {
    throw new Error('Expected either a `href` value or a `callback`.');
  }

  if (data.attributes && Core.isPlainObject(data.attributes)) {
    Object.keys(data.attributes).forEach(key => {
      const value = data.attributes![key];
      if (typeof (value as any) !== 'string') {
        throw new Error('Expected only string values.');
      }

      // Support the dash notation for backwards compatibility.
      if (key.indexOf('-') !== -1) {
        link.setAttribute(`data-${key}`, value);
      } else {
        link.dataset[key] = value;
      }
    });
  }

  item.appendChild(link);

  if (typeof data.icon !== 'undefined' && Core.isPlainObject(data.icon)) {
    if (typeof (data.icon.name as any) !== 'string') {
      throw new TypeError('Expected a valid icon name.');
    }

    let size = 16;
    if (typeof data.icon.size === 'number' && _validIconSizes.indexOf(~~data.icon.size) !== -1) {
      size = ~~data.icon.size;
    }

    const icon = document.createElement('span');
    icon.className = 'icon icon' + size + ' fa-' + data.icon.name;

    link.appendChild(icon);
  }

  const label = (typeof (data.label as any) === 'string') ? data.label!.trim() : '';
  const labelHtml = (typeof (data.labelHtml as any) === 'string') ? data.labelHtml!.trim() : '';
  if (label === '' && labelHtml === '') {
    throw new TypeError('Expected either a label or a `labelHtml`.');
  }

  const span = document.createElement('span');
  span[label ? 'textContent' : 'innerHTML'] = (label) ? label : labelHtml;
  link.appendChild(document.createTextNode(' '));
  link.appendChild(span);

  return item;
}

/**
 * Creates a new dropdown menu, optionally pre-populated with the supplied list of
 * dropdown items. The list element will be returned and must be manually injected
 * into the DOM by the callee.
 */
export function create(items: DropdownBuilderItemData[], identifier?: string): HTMLUListElement {
  const list = document.createElement('ul');
  list.className = 'dropdownMenu';
  if (typeof identifier === 'string') {
    list.dataset.identifier = identifier;
  }

  if (Array.isArray(items) && items.length > 0) {
    appendItems(list, items);
  }

  return list;
}

/**
 * Creates a new dropdown item that can be inserted into lists using regular DOM operations.
 */
export function buildItem(item: DropdownBuilderItemData): HTMLLIElement {
  return buildItemFromData(item);
}

/**
 * Appends a single item to the target list.
 */
export function appendItem(list: HTMLUListElement, item: DropdownBuilderItemData): void {
  validateList(list);

  list.appendChild(buildItemFromData(item));
}

/**
 * Appends a list of items to the target list.
 */
export function appendItems(list: HTMLUListElement, items: DropdownBuilderItemData[]): void {
  validateList(list);

  if (!Array.isArray(items)) {
    throw new TypeError('Expected an array of items.');
  }

  const length = items.length;
  if (length === 0) {
    throw new Error('Expected a non-empty list of items.');
  }

  if (length === 1) {
    appendItem(list, items[0]);
  } else {
    const fragment = document.createDocumentFragment();
    items.forEach(item => {
      fragment.appendChild(buildItemFromData(item));
    });
    list.appendChild(fragment);
  }
}

/**
 * Replaces the existing list items with the provided list of new items.
 */
export function setItems(list: HTMLUListElement, items: DropdownBuilderItemData[]): void {
  validateList(list);

  list.innerHTML = '';

  appendItems(list, items);
}

/**
 * Attaches the list to a button, visibility is from then on controlled through clicks
 * on the provided button element. Internally calls `Ui/SimpleDropdown.initFragment()`
 * to delegate the DOM management.
 */
export function attach(list: HTMLUListElement, button: HTMLElement): void {
  validateList(list);

  UiDropdownSimple.initFragment(button, list);

  button.addEventListener('click', event => {
    event.preventDefault();
    event.stopPropagation();

    UiDropdownSimple.toggleDropdown(button.id);
  });
}

/**
 * Helper method that returns the special string `"divider"` that causes a divider to
 * be created.
 */
export function divider(): string {
  return 'divider';
}

interface BaseItemData {
  attributes?: {
    [key: string]: string;
  };
  callback?: (link: HTMLAnchorElement) => void;
  href?: string;
  icon?: {
    name: string;
    size?: 16 | 24 | 32 | 48 | 64 | 96 | 144;
  }
  identifier?: string;
  label?: string;
  labelHtml?: string;
}

interface TextItemData extends BaseItemData {
  label: string;
  labelHtml?: never;
}

interface HtmlItemData extends BaseItemData {
  label?: never;
  labelHtml: string;
}

export type DropdownBuilderItemData = "divider" | HtmlItemData | TextItemData;
