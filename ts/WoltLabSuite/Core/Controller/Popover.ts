/**
 * Versatile popover manager.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 Use `WoltLabSuite/Core/Component/Popover` instead
 */

import * as Ajax from "../Ajax";
import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import * as Environment from "../Environment";
import * as UiAlignment from "../Ui/Alignment";
import { AjaxCallbackObject, AjaxCallbackSetup, CallbackFailure, CallbackSuccess, RequestPayload } from "../Ajax/Data";

const enum State {
  None,
  Loading,
  Ready,
}

const enum Delay {
  Hide = 500,
  Show = 800,
}

type CallbackLoad = (objectId: number | string, popover: ControllerPopover, element: HTMLElement) => void;

interface PopoverOptions {
  attributeName?: string;
  className: string;
  dboAction: string;
  identifier: string;
  legacy?: boolean;
  loadCallback?: CallbackLoad;
}

interface HandlerData {
  attributeName: string;
  dboAction: string;
  legacy: boolean;
  loadCallback?: CallbackLoad;
  selector: string;
}

interface ElementData {
  element: HTMLElement;
  identifier: string;
  objectId: number | string;
}

interface CacheData {
  content: DocumentFragment | null;
  state: State;
}

class ControllerPopover implements AjaxCallbackObject {
  private activeId = "";
  private readonly cache = new Map<string, CacheData>();
  private readonly elements = new Map<string, ElementData>();
  private readonly handlers = new Map<string, HandlerData>();
  private hoverId = "";
  private readonly popover: HTMLDivElement;
  private readonly popoverContent: HTMLDivElement;
  private suspended = false;
  private timerEnter?: number = undefined;
  private timerLeave?: number = undefined;

  /**
   * Builds popover DOM elements and binds event listeners.
   */
  constructor() {
    this.popover = document.createElement("div");
    this.popover.className = "popover forceHide";

    this.popoverContent = document.createElement("div");
    this.popoverContent.className = "popoverContent";
    this.popover.appendChild(this.popoverContent);

    document.body.append(this.popover);

    // event listener
    this.popover.addEventListener("mouseenter", () => this.popoverMouseEnter());
    this.popover.addEventListener("mouseleave", () => this.mouseLeave());

    this.popover.addEventListener("animationend", () => this.clearContent());

    window.addEventListener("beforeunload", () => {
      this.suspended = true;

      if (this.timerEnter) {
        window.clearTimeout(this.timerEnter);
        this.timerEnter = undefined;
      }

      this.hidePopover();
    });

    DomChangeListener.add("WoltLabSuite/Core/Controller/Popover", (identifier) => this.initHandler(identifier));
  }

  /**
   * Initializes a popover handler.
   *
   * Usage:
   *
   * ControllerPopover.init({
   * 	attributeName: 'data-object-id',
   * 	className: 'fooLink',
   * 	identifier: 'com.example.bar.foo',
   * 	loadCallback: (objectId, popover) => {
   * 		// request data for object id (e.g. via WoltLabSuite/Core/Ajax)
   *
   * 		// then call this to set the content
   * 		popover.setContent('com.example.bar.foo', objectId, htmlTemplateString);
   * 	}
   * });
   */
  init(options: PopoverOptions): void {
    if (Environment.platform() !== "desktop") {
      return;
    }

    options.attributeName = options.attributeName || "data-object-id";
    options.legacy = (options.legacy as unknown) === true;

    if (this.handlers.has(options.identifier)) {
      return;
    }

    // Legacy implementations provided a selector for `className`.
    const selector = options.legacy ? options.className : `.${options.className}`;

    this.handlers.set(options.identifier, {
      attributeName: options.attributeName,
      dboAction: options.dboAction,
      legacy: options.legacy,
      loadCallback: options.loadCallback,
      selector,
    });

    this.initHandler(options.identifier);
  }

  /**
   * Initializes a popover handler.
   */
  private initHandler(identifier?: string): void {
    if (typeof identifier === "string" && identifier.length) {
      this.initElements(this.handlers.get(identifier)!, identifier);
    } else {
      this.handlers.forEach((value, key) => {
        this.initElements(value, key);
      });
    }
  }

  /**
   * Binds event listeners for popover-enabled elements.
   */
  private initElements(options: HandlerData, identifier: string): void {
    document.querySelectorAll(options.selector).forEach((element: HTMLElement) => {
      const id = DomUtil.identify(element);
      if (this.cache.has(id)) {
        return;
      }

      // Skip elements that are located inside a popover.
      if (element.closest(".popover, .popoverContainer") !== null) {
        this.cache.set(id, {
          content: null,
          state: State.None,
        });

        return;
      }

      const objectId = options.legacy ? id : ~~element.getAttribute(options.attributeName)!;
      if (objectId === 0) {
        return;
      }

      element.addEventListener("mouseenter", (ev) => this.mouseEnter(ev));
      element.addEventListener("mouseleave", () => this.mouseLeave());

      if (element instanceof HTMLAnchorElement && element.href) {
        element.addEventListener("click", () => this.hidePopover());
      }

      const cacheId = `${identifier}-${objectId}`;
      element.dataset.cacheId = cacheId;

      this.elements.set(id, {
        element,
        identifier,
        objectId: objectId.toString(),
      });

      if (!this.cache.has(cacheId)) {
        this.cache.set(cacheId, {
          content: null,
          state: State.None,
        });
      }
    });
  }

  /**
   * Sets the content for given identifier and object id.
   */
  setContent(identifier: string, objectId: number | string, content: string): void {
    const cacheId = `${identifier}-${objectId}`;
    const data = this.cache.get(cacheId);
    if (data === undefined) {
      throw new Error(`Unable to find element for object id '${objectId}' (identifier: '${identifier}').`);
    }

    let fragment = DomUtil.createFragmentFromHtml(content);
    if (!fragment.childElementCount) {
      fragment = DomUtil.createFragmentFromHtml("<p>" + content + "</p>");
    }

    data.content = fragment;
    data.state = State.Ready;

    if (this.activeId) {
      const activeElement = this.elements.get(this.activeId)!.element;

      if (activeElement.dataset.cacheId === cacheId) {
        this.show();
      }
    }
  }

  resetCache(identifier: string, objectId: number): void {
    const cacheId = `${identifier}-${objectId}`;
    if (!this.cache.has(cacheId)) {
      return;
    }

    this.cache.set(cacheId, {
      content: null,
      state: State.None,
    });
  }

  /**
   * Handles the mouse start hovering the popover-enabled element.
   */
  private mouseEnter(event: MouseEvent): void {
    if (this.suspended) {
      return;
    }

    if (this.timerEnter) {
      window.clearTimeout(this.timerEnter);
      this.timerEnter = undefined;
    }

    const id = DomUtil.identify(event.currentTarget as HTMLElement);
    if (this.activeId === id && this.timerLeave) {
      window.clearTimeout(this.timerLeave);
      this.timerLeave = undefined;
    }

    this.hoverId = id;

    this.timerEnter = window.setTimeout(() => {
      this.timerEnter = undefined;

      if (this.hoverId === id) {
        this.show();
      }
    }, Delay.Show);
  }

  /**
   * Handles the mouse leaving the popover-enabled element or the popover itself.
   */
  private mouseLeave(): void {
    this.hoverId = "";

    if (this.timerLeave) {
      return;
    }

    this.timerLeave = window.setTimeout(() => this.hidePopover(), Delay.Hide);
  }

  /**
   * Handles the mouse start hovering the popover element.
   */
  private popoverMouseEnter(): void {
    if (this.timerLeave) {
      window.clearTimeout(this.timerLeave);
      this.timerLeave = undefined;
    }
  }

  /**
   * Shows the popover and loads content on-the-fly.
   */
  private show(): void {
    if (this.timerLeave) {
      window.clearTimeout(this.timerLeave);
      this.timerLeave = undefined;
    }

    let forceHide = false;
    if (this.popover.classList.contains("active")) {
      if (this.activeId !== this.hoverId) {
        this.hidePopover();

        forceHide = true;
      }
    } else if (this.popoverContent.childElementCount) {
      forceHide = true;
    }

    if (forceHide) {
      this.popover.classList.add("forceHide");

      // Query a layout related property to force a reflow, otherwise the transition is optimized away.
      // eslint-disable-next-line @typescript-eslint/no-unused-expressions
      this.popover.offsetTop;

      this.clearContent();

      this.popover.classList.remove("forceHide");
    }

    this.activeId = this.hoverId;

    const elementData = this.elements.get(this.activeId);
    // check if source element is already gone
    if (elementData === undefined) {
      return;
    }

    const cacheId = elementData.element.dataset.cacheId!;
    const data = this.cache.get(cacheId)!;

    switch (data.state) {
      case State.Ready: {
        this.popoverContent.appendChild(data.content!);

        this.rebuild();

        break;
      }

      case State.None: {
        data.state = State.Loading;

        const handler = this.handlers.get(elementData.identifier)!;
        if (handler.loadCallback) {
          handler.loadCallback(elementData.objectId, this, elementData.element);
        } else if (handler.dboAction) {
          const callback = (data) => {
            this.setContent(elementData.identifier, elementData.objectId, data.returnValues.template);

            return true;
          };

          this.ajaxApi(
            {
              actionName: "getPopover",
              className: handler.dboAction,
              interfaceName: "wcf\\data\\IPopoverAction",
              objectIDs: [elementData.objectId],
            },
            callback,
            callback,
          );
        }

        break;
      }

      case State.Loading: {
        // Do not interrupt inflight requests.
        break;
      }
    }
  }

  /**
   * Hides the popover element.
   */
  private hidePopover(): void {
    if (this.timerLeave) {
      window.clearTimeout(this.timerLeave);
      this.timerLeave = undefined;
    }

    this.popover.classList.remove("active");
  }

  /**
   * Clears popover content by moving it back into the cache.
   */
  private clearContent(): void {
    if (this.activeId && this.popoverContent.childElementCount && !this.popover.classList.contains("active")) {
      const cacheId = this.elements.get(this.activeId)!.element.dataset.cacheId!;
      const activeElData = this.cache.get(cacheId)!;
      while (this.popoverContent.childNodes.length) {
        activeElData.content!.appendChild(this.popoverContent.childNodes[0]);
      }
    }
  }

  /**
   * Rebuilds the popover.
   */
  private rebuild(): void {
    if (this.popover.classList.contains("active")) {
      return;
    }

    this.popover.classList.remove("forceHide");
    this.popover.classList.add("active");

    UiAlignment.set(this.popover, this.elements.get(this.activeId)!.element, {
      vertical: "top",
    });
  }

  _ajaxSuccess() {
    // This class was designed in a strange way without utilizing this method.
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      ignoreError: true,
      silent: true,
    };
  }

  /**
   * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
   */
  ajaxApi(data: RequestPayload, success: CallbackSuccess, failure?: CallbackFailure): void {
    if (typeof success !== "function") {
      throw new TypeError("Expected a valid callback for parameter 'success'.");
    }

    Ajax.api(this, data, success, failure);
  }
}

let controllerPopover: ControllerPopover;

function getControllerPopover(): ControllerPopover {
  if (!controllerPopover) {
    controllerPopover = new ControllerPopover();
  }

  return controllerPopover;
}

/**
 * Initializes a popover handler.
 *
 * Usage:
 *
 * ControllerPopover.init({
 * 	attributeName: 'data-object-id',
 * 	className: 'fooLink',
 * 	identifier: 'com.example.bar.foo',
 * 	loadCallback: function(objectId, popover) {
 * 		// request data for object id (e.g. via WoltLabSuite/Core/Ajax)
 *
 * 		// then call this to set the content
 * 		popover.setContent('com.example.bar.foo', objectId, htmlTemplateString);
 * 	}
 * });
 *
 * @deprecated 6.1 Use `WoltLabSuite/Core/Component/Popover` instead
 */
export function init(options: PopoverOptions): void {
  getControllerPopover().init(options);
}

/**
 * Sets the content for given identifier and object id.
 */
export function setContent(identifier: string, objectId: number, content: string): void {
  getControllerPopover().setContent(identifier, objectId, content);
}

/**
 * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
 */
export function ajaxApi(data: RequestPayload, success: CallbackSuccess, failure: CallbackFailure): void {
  getControllerPopover().ajaxApi(data, success, failure);
}

/**
 * Resets the cached data for an object.
 */
export function resetCache(identifier: string, objectId: number): void {
  getControllerPopover().resetCache(identifier, objectId);
}
