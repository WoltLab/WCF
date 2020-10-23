/**
 * Provides helper functions to traverse the DOM.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dom/Traverse (alias)
 * @module  WoltLabSuite/Core/Dom/Traverse
 * @module DomTraverse
 */

const enum Type {
  None,
  Selector,
  ClassName,
  TagName,
}

const _test = new Map<Type, (...args: any[]) => boolean>([
  [Type.None, () => true],
  [Type.Selector, (element: Element, selector: string) => element.matches(selector)],
  [Type.ClassName, (element: Element, className: string) => element.classList.contains(className)],
  [Type.TagName, (element: Element, tagName: string) => element.nodeName === tagName],
]);

function _getChildren(element: Element, type: Type, value: string): Element[] {
  if (!(element instanceof Element)) {
    throw new TypeError('Expected a valid element as first argument.');
  }

  const children: Element[] = [];
  for (let i = 0; i < element.childElementCount; i++) {
    if (_test.get(type)!(element.children[i], value)) {
      children.push(element.children[i]);
    }
  }

  return children;
}

function _getParent(element: Element, type: Type, value: string, untilElement?: Element): Element | null {
  if (!(element instanceof Element)) {
    throw new TypeError('Expected a valid element as first argument.');
  }

  let target = element.parentNode;
  while (target instanceof Element) {
    if (target === untilElement) {
      return null;
    }

    if (_test.get(type)!(target, value)) {
      return target;
    }

    target = target.parentNode;
  }

  return null;
}

function _getSibling(element: Element, siblingType: string, type: Type, value: string): Element | null {
  if (!(element instanceof Element)) {
    throw new TypeError('Expected a valid element as first argument.');
  }

  if (element instanceof Element) {
    if (element[siblingType] !== null && _test.get(type)!(element[siblingType], value)) {
      return element[siblingType];
    }
  }

  return null;
}

/**
 * Examines child elements and returns the first child matching the given selector.
 */
export function childBySel(element: Element, selector: string): Element | null {
  return _getChildren(element, Type.Selector, selector)[0] || null;
}

/**
 * Examines child elements and returns the first child that has the given CSS class set.
 */
export function childByClass(element: Element, className: string): Element | null {
  return _getChildren(element, Type.ClassName, className)[0] || null;
}

/**
 * Examines child elements and returns the first child which equals the given tag.
 */
export function childByTag(element: Element, tagName: string): Element | null {
  return _getChildren(element, Type.TagName, tagName)[0] || null;
}

/**
 * Examines child elements and returns all children matching the given selector.
 */
export function childrenBySel(element, selector: string): Element[] {
  return _getChildren(element, Type.Selector, selector);
}

/**
 * Examines child elements and returns all children that have the given CSS class set.
 */
export function childrenByClass(element: Element, className: string): Element[] {
  return _getChildren(element, Type.ClassName, className);
}

/**
 * Examines child elements and returns all children which equal the given tag.
 */
export function childrenByTag(element: Element, tagName: string): Element[] {
  return _getChildren(element, Type.TagName, tagName);
}

/**
 * Examines parent nodes and returns the first parent that matches the given selector.
 */
export function parentBySel(element: Element, selector: string, untilElement?: Element): Element | null {
  return _getParent(element, Type.Selector, selector, untilElement);
}

/**
 * Examines parent nodes and returns the first parent that has the given CSS class set.
 */
export function parentByClass(element: Element, className: string, untilElement?: Element): Element | null {
  return _getParent(element, Type.ClassName, className, untilElement);
}

/**
 * Examines parent nodes and returns the first parent which equals the given tag.
 */
export function parentByTag(element: Element, tagName: string, untilElement?: Element): Element | null {
  return _getParent(element, Type.TagName, tagName, untilElement);
}

/**
 * Returns the next element sibling.
 *
 * @deprecated 5.4 Use `element.nextElementSibling` instead.
 */
export function next(element: Element): Element | null {
  return _getSibling(element, 'nextElementSibling', Type.None, '');
}

/**
 * Returns the next element sibling that matches the given selector.
 */
export function nextBySel(element: Element, selector: string): Element | null {
  return _getSibling(element, 'nextElementSibling', Type.Selector, selector);
}

/**
 * Returns the next element sibling with given CSS class.
 */
export function nextByClass(element: Element, className: string): Element | null {
  return _getSibling(element, 'nextElementSibling', Type.ClassName, className);
}

/**
 * Returns the next element sibling with given CSS class.
 */
export function nextByTag(element: Element, tagName: string): Element | null {
  return _getSibling(element, 'nextElementSibling', Type.TagName, tagName);
}

/**
 * Returns the previous element sibling.
 *
 * @deprecated 5.4 Use `element.previousElementSibling` instead.
 */
export function prev(element: Element): Element | null {
  return _getSibling(element, 'previousElementSibling', Type.None, '');
}

/**
 * Returns the previous element sibling that matches the given selector.
 */
export function prevBySel(element: Element, selector: string): Element | null {
  return _getSibling(element, 'previousElementSibling', Type.Selector, selector);
}

/**
 * Returns the previous element sibling with given CSS class.
 */
export function prevByClass(element: Element, className: string): Element | null {
  return _getSibling(element, 'previousElementSibling', Type.ClassName, className);
}

/**
 * Returns the previous element sibling with given CSS class.
 */
export function prevByTag(element: Element, tagName: string): Element | null {
  return _getSibling(element, 'previousElementSibling', Type.TagName, tagName);
}
