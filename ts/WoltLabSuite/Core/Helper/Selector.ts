/**
 * Provides functions to watch for elements being added to the document.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

let observer: MutationObserver;

type CallbackMatch = (element: HTMLElement) => void;

const selectors = new Map<string, CallbackMatch[]>();

function findElements(node: HTMLElement): void {
  for (const [selector, callbacks] of selectors.entries()) {
    if (node.matches(selector)) {
      notifyCallbacks(node, callbacks);
    }

    const matches = node.querySelectorAll<HTMLElement>(selector);
    for (const element of matches) {
      notifyCallbacks(element, callbacks);
    }
  }
}

function notifyCallbacks(element: HTMLElement, callbacks: CallbackMatch[]): void {
  for (const callback of callbacks) {
    callback(element);
  }
}

/**
 * Invokes a callback whenever a selector matches an element added
 * to the DOM. Elements being removed and then added again will be
 * reported again.
 */
export function wheneverSeen(selector: string, callback: CallbackMatch): void {
  if (!selectors.has(selector)) {
    selectors.set(selector, []);
  }
  selectors.get(selector)!.push(callback);

  findElements(document.body);

  if (observer === undefined) {
    observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (node instanceof HTMLElement) {
            findElements(node);
          }
        }
      }
    });
    observer.observe(document, { subtree: true, childList: true });
  }
}

/**
 * Works identical to `wheneverSeen` with the difference that all
 * previously matched elements are tracked and will not be reported
 * again. Useful for applying event listeners or transformations
 * that should be applied just once.
 */
export function wheneverFirstSeen(selector: string, callback: CallbackMatch) {
  const knownElements = new WeakSet();

  wheneverSeen(selector, (element) => {
    if (!knownElements.has(element)) {
      knownElements.add(element);

      callback(element);
    }
  });
}
