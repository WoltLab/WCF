/**
 * Efficient lazy loader that executes a callback once a selector matches
 * for the first time and the document has finished loading.
 *
 * Designed for actions that do not require eager execution, such as
 * binding specific event listeners on runtime. It should not be used for
 * components that alter the visible UI to prevent layout shifts.
 *
 * Based on the work of GitHub‘s Catalyst library (MIT license).
 * See https://github.com/github/catalyst/blob/c7983581adffd88f059e3c70674350b4fea4ee47/src/lazy-define.ts
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/LazyLoader
 */

type CallbackWhenSeen = () => void;

let observer: MutationObserver;
const selectors = new Map<string, CallbackWhenSeen[]>();
const timers = new Map<HTMLElement, number>();

const documentReady = new Promise<void>((resolve) => {
  if (document.readyState === "loading") {
    document.addEventListener("readystatechange", () => resolve(), { once: true });
  } else {
    resolve();
  }
});

function testElement(element: HTMLElement): void {
  if (timers.get(element) !== undefined) {
    window.cancelAnimationFrame(timers.get(element)!);
  }

  timers.set(
    element,
    window.requestAnimationFrame(() => {
      for (const selector of selectors.keys()) {
        if (element.matches(selector) || element.querySelector(selector) !== null) {
          for (const callback of selectors.get(selector)!) {
            void documentReady.then(() => callback());
          }

          selectors.delete(selector);
        }
      }

      timers.delete(element);
    }),
  );
}

export function whenFirstSeen(selector: string, callback: CallbackWhenSeen): void {
  if (!selectors.has(selector)) {
    selectors.set(selector, []);
  }
  selectors.get(selector)!.push(callback);

  testElement(document.body);

  if (observer === undefined) {
    observer = new MutationObserver((mutations) => {
      if (selectors.size === 0) {
        return;
      }

      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (node instanceof HTMLElement) {
            testElement(node);
          }
        }
      }
    });
  }
  observer.observe(document, { subtree: true, childList: true });
}
