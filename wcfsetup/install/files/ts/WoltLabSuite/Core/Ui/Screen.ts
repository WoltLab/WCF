/**
 * Provides consistent support for media queries and body scrolling.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Screen (alias)
 * @module  WoltLabSuite/Core/Ui/Screen
 */

import * as Core from "../Core";
import * as Environment from "../Environment";

const _mql = new Map<string, MediaQueryData>();

let _scrollDisableCounter = 0;
let _scrollOffsetFrom: string;
let _scrollTop = 0;
let _pageOverlayCounter = 0;

const _mqMap = new Map<string, string>(
  Object.entries({
    "screen-xs": "(max-width: 544px)" /* smartphone */,
    "screen-sm": "(min-width: 545px) and (max-width: 768px)" /* tablet (portrait) */,
    "screen-sm-down": "(max-width: 768px)" /* smartphone + tablet (portrait) */,
    "screen-sm-up": "(min-width: 545px)" /* tablet (portrait) + tablet (landscape) + desktop */,
    "screen-sm-md": "(min-width: 545px) and (max-width: 1024px)" /* tablet (portrait) + tablet (landscape) */,
    "screen-md": "(min-width: 769px) and (max-width: 1024px)" /* tablet (landscape) */,
    "screen-md-down": "(max-width: 1024px)" /* smartphone + tablet (portrait) + tablet (landscape) */,
    "screen-md-up": "(min-width: 769px)" /* tablet (landscape) + desktop */,
    "screen-lg": "(min-width: 1025px)" /* desktop */,
    "screen-lg-only": "(min-width: 1025px) and (max-width: 1280px)",
    "screen-lg-down": "(max-width: 1280px)",
    "screen-xl": "(min-width: 1281px)",
  })
);

// Microsoft Edge rewrites the media queries to whatever it
// pleases, causing the input and output query to mismatch
const _mqMapEdge = new Map<string, string>();

/**
 * Registers event listeners for media query match/unmatch.
 *
 * The `callbacks` object may contain the following keys:
 *  - `match`, triggered when media query matches
 *  - `unmatch`, triggered when media query no longer matches
 *  - `setup`, invoked when media query first matches
 *
 * Returns a UUID that is used to internal identify the callbacks, can be used
 * to remove binding by calling the `remove` method.
 */
export function on(query: string, callbacks: Callbacks): string {
  const uuid = Core.getUuid(),
    queryObject = _getQueryObject(query);

  if (typeof callbacks.match === "function") {
    queryObject.callbacksMatch.set(uuid, callbacks.match);
  }

  if (typeof callbacks.unmatch === "function") {
    queryObject.callbacksUnmatch.set(uuid, callbacks.unmatch);
  }

  if (typeof callbacks.setup === "function") {
    if (queryObject.mql.matches) {
      callbacks.setup();
    } else {
      queryObject.callbacksSetup.set(uuid, callbacks.setup);
    }
  }

  return uuid;
}

/**
 * Removes all listeners identified by their common UUID.
 */
export function remove(query: string, uuid: string): void {
  const queryObject = _getQueryObject(query);

  queryObject.callbacksMatch.delete(uuid);
  queryObject.callbacksUnmatch.delete(uuid);
  queryObject.callbacksSetup.delete(uuid);
}

/**
 * Returns a boolean value if a media query expression currently matches.
 */
export function is(query: string): boolean {
  return _getQueryObject(query).mql.matches;
}

/**
 * Disables scrolling of body element.
 */
export function scrollDisable(): void {
  if (_scrollDisableCounter === 0) {
    _scrollTop = document.body.scrollTop;
    _scrollOffsetFrom = "body";
    if (!_scrollTop) {
      _scrollTop = document.documentElement.scrollTop;
      _scrollOffsetFrom = "documentElement";
    }

    const pageContainer = document.getElementById("pageContainer")!;

    // setting translateY causes Mobile Safari to snap
    if (Environment.platform() === "ios") {
      pageContainer.style.setProperty("position", "relative", "");
      pageContainer.style.setProperty("top", "-" + _scrollTop + "px", "");
    } else {
      pageContainer.style.setProperty("margin-top", "-" + _scrollTop + "px", "");
    }

    document.documentElement.classList.add("disableScrolling");
  }

  _scrollDisableCounter++;
}

/**
 * Re-enables scrolling of body element.
 */
export function scrollEnable(): void {
  if (_scrollDisableCounter) {
    _scrollDisableCounter--;

    if (_scrollDisableCounter === 0) {
      document.documentElement.classList.remove("disableScrolling");

      const pageContainer = document.getElementById("pageContainer")!;
      if (Environment.platform() === "ios") {
        pageContainer.style.removeProperty("position");
        pageContainer.style.removeProperty("top");
      } else {
        pageContainer.style.removeProperty("margin-top");
      }

      if (_scrollTop) {
        document[_scrollOffsetFrom].scrollTop = ~~_scrollTop;
      }
    }
  }
}

/**
 * Indicates that at least one page overlay is currently open.
 */
export function pageOverlayOpen(): void {
  if (_pageOverlayCounter === 0) {
    document.documentElement.classList.add("pageOverlayActive");
  }

  _pageOverlayCounter++;
}

/**
 * Marks one page overlay as closed.
 */
export function pageOverlayClose(): void {
  if (_pageOverlayCounter) {
    _pageOverlayCounter--;

    if (_pageOverlayCounter === 0) {
      document.documentElement.classList.remove("pageOverlayActive");
    }
  }
}

/**
 * Returns true if at least one page overlay is currently open.
 *
 * @returns {boolean}
 */
export function pageOverlayIsActive(): boolean {
  return _pageOverlayCounter > 0;
}

/**
 * @deprecated 5.4 - This method is a noop.
 */
export function setDialogContainer(_container: Element): void {
  // Do nothing.
}

function _getQueryObject(query: string): MediaQueryData {
  if (typeof (query as any) !== "string" || query.trim() === "") {
    throw new TypeError("Expected a non-empty string for parameter 'query'.");
  }

  // Microsoft Edge rewrites the media queries to whatever it
  // pleases, causing the input and output query to mismatch
  if (_mqMapEdge.has(query)) query = _mqMapEdge.get(query)!;

  if (_mqMap.has(query)) query = _mqMap.get(query) as string;

  let queryObject = _mql.get(query);
  if (!queryObject) {
    queryObject = {
      callbacksMatch: new Map<string, Callback>(),
      callbacksUnmatch: new Map<string, Callback>(),
      callbacksSetup: new Map<string, Callback>(),
      mql: window.matchMedia(query),
    };
    //noinspection JSDeprecatedSymbols
    queryObject.mql.addListener(_mqlChange);

    _mql.set(query, queryObject);

    if (query !== queryObject.mql.media) {
      _mqMapEdge.set(queryObject.mql.media, query);
    }
  }

  return queryObject;
}

/**
 * Triggered whenever a registered media query now matches or no longer matches.
 */
function _mqlChange(event: MediaQueryListEvent): void {
  const queryObject = _getQueryObject(event.media);
  if (event.matches) {
    if (queryObject.callbacksSetup.size) {
      queryObject.callbacksSetup.forEach((callback) => {
        callback();
      });

      // discard all setup callbacks after execution
      queryObject.callbacksSetup = new Map<string, Callback>();
    } else {
      queryObject.callbacksMatch.forEach((callback) => {
        callback();
      });
    }
  } else {
    queryObject.callbacksUnmatch.forEach((callback) => {
      callback();
    });
  }
}

type Callback = () => void;

interface Callbacks {
  match: Callback;
  setup: Callback;
  unmatch: Callback;
}

interface MediaQueryData {
  callbacksMatch: Map<string, Callback>;
  callbacksSetup: Map<string, Callback>;
  callbacksUnmatch: Map<string, Callback>;
  mql: MediaQueryList;
}
