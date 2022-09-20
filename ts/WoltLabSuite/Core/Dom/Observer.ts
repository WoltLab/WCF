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

export function findUniqueElements(selector: string, callback: CallbackMatch) {
  const knownElements = new WeakSet();

  wheneverSeen(selector, (element) => {
    if (!knownElements.has(element)) {
      knownElements.add(element);

      callback(element);
    }
  });
}
