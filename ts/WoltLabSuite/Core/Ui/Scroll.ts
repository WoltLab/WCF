/**
 * Smoothly scrolls to an element while accounting for potential sticky headers.
 * The offset is taken from the `scroll-padding-top` of the document element.
 *
 * @author  Alexander Ebert, Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
import DomUtil from "../Dom/Util";

type Callback = () => void;

let _callbacks: Callback[] = [];
let _targetElement: HTMLElement | undefined = undefined;
let _timeoutScroll: number | null = null;

/**
 * Monitors scroll event to only execute the callback once scrolling has ended.
 */
function onScroll(): void {
  if (_timeoutScroll !== null) {
    window.clearTimeout(_timeoutScroll);
  }

  _timeoutScroll = window.setTimeout(() => {
    for (const callback of _callbacks) {
      callback();
    }

    window.removeEventListener("scroll", onScroll);
    _callbacks = [];
    _targetElement = undefined;
    _timeoutScroll = null;
  }, 100);
}

/**
 * Returns the `scroll-padding-top` of the document element that keeps
 * scroll targets clear of the sticky page header.
 */
function getScrollPaddingTop(): number {
  const value = parseFloat(window.getComputedStyle(document.documentElement).scrollPaddingTop);

  return Number.isNaN(value) ? 0 : value;
}

/**
 * Scrolls to target element, optionally invoking the provided callback once scrolling has ended.
 *
 * @param       {Element}       element         target element
 * @param       {function=}     callback        callback invoked once scrolling has ended
 */
export function element(element: HTMLElement, callback?: Callback, behavior: ScrollBehavior = "smooth"): void {
  if (!(element instanceof HTMLElement)) {
    throw new TypeError("Expected a valid DOM element.");
  } else if (callback !== undefined && typeof callback !== "function") {
    throw new TypeError("Expected a valid callback function.");
  } else if (!document.body.contains(element)) {
    throw new Error("Element must be part of the visible DOM.");
  } else if (_callbacks.length > 0) {
    if (element !== _targetElement) {
      throw new Error("Cannot scroll to element, a concurrent request is running.");
    }
  }

  if (callback) {
    _callbacks.push(callback);
  }

  if (_targetElement !== undefined) {
    return;
  }
  _targetElement = element;
  window.addEventListener("scroll", onScroll);

  let y = DomUtil.offset(element).top;
  const offset = getScrollPaddingTop();
  if (offset > 0) {
    if (y <= offset) {
      y = 0;
    } else {
      // add an offset to account for a sticky header
      y -= offset;
    }
  }

  const previousPosition = window.pageYOffset;
  window.scrollTo({
    left: 0,
    top: y,
    behavior,
  });

  window.setTimeout(() => {
    // no scrolling took place
    if (previousPosition === window.pageYOffset) {
      onScroll();
    }
  }, 100);
}

export function isCurrentlyScrolling(): boolean {
  return _targetElement !== undefined;
}
