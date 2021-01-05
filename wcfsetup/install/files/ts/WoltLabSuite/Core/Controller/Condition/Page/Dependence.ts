/**
 * Shows and hides an element that depends on certain selected pages when setting up conditions.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Condition/Page/Dependence
 */

import DomUtil from "../../../Dom/Util";
import * as EventHandler from "../../../Event/Handler";

const _pages: HTMLInputElement[] = Array.from(document.querySelectorAll('input[name="pageIDs[]"]'));
const _dependentElements: HTMLElement[] = [];
const _pageIds = new WeakMap<HTMLElement, number[]>();
const _hiddenElements = new WeakMap<HTMLElement, HTMLElement[]>();

let _didInit = false;

/**
 * Checks if only relevant pages are selected. If that is the case, the dependent
 * element is shown, otherwise it is hidden.
 */
function checkVisibility(): void {
  _dependentElements.forEach((dependentElement) => {
    const pageIds = _pageIds.get(dependentElement)!;

    const checkedPageIds: number[] = [];
    _pages.forEach((page) => {
      if (page.checked) {
        checkedPageIds.push(~~page.value);
      }
    });

    const irrelevantPageIds = checkedPageIds.filter((pageId) => pageIds.includes(pageId));

    if (!checkedPageIds.length || irrelevantPageIds.length) {
      hideDependentElement(dependentElement);
    } else {
      showDependentElement(dependentElement);
    }
  });

  EventHandler.fire("com.woltlab.wcf.pageConditionDependence", "checkVisivility");
}

/**
 * Hides all elements that depend on the given element.
 */
function hideDependentElement(dependentElement: HTMLElement): void {
  DomUtil.hide(dependentElement);

  const hiddenElements = _hiddenElements.get(dependentElement)!;
  hiddenElements.forEach((hiddenElement) => DomUtil.hide(hiddenElement));

  _hiddenElements.set(dependentElement, []);
}

/**
 * Shows all elements that depend on the given element.
 */
function showDependentElement(dependentElement: HTMLElement): void {
  DomUtil.show(dependentElement);

  // make sure that all parent elements are also visible
  let parentElement = dependentElement;
  while ((parentElement = parentElement.parentElement!) && parentElement) {
    if (DomUtil.isHidden(parentElement)) {
      _hiddenElements.get(dependentElement)!.push(parentElement);
    }

    DomUtil.show(parentElement);
  }
}

export function register(dependentElement: HTMLElement, pageIds: number[]): void {
  _dependentElements.push(dependentElement);
  _pageIds.set(dependentElement, pageIds);
  _hiddenElements.set(dependentElement, []);

  if (!_didInit) {
    _pages.forEach((page) => {
      page.addEventListener("change", () => checkVisibility());
    });

    _didInit = true;
  }

  // remove the dependent element before submit if it is hidden
  dependentElement.closest("form")!.addEventListener("submit", () => {
    if (DomUtil.isHidden(dependentElement)) {
      dependentElement.remove();
    }
  });

  checkVisibility();
}
