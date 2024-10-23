/**
 * Provides page actions such as "jump to top" and clipboard actions.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "../../Core";
import * as Language from "../../Language";
import * as UiScreen from "../../Ui/Screen";

const _buttons = new Map<string, HTMLElement>();

let _container: HTMLElement;
let _didInit = false;
let _lastPosition = -1;
let _toTopButton: HTMLElement;
let _wrapper: HTMLElement;
let _toTopButtonThreshold = 300;

const _resetLastPosition = Core.debounce(() => {
  _lastPosition = -1;
}, 50);

function buildToTopButton(): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("button", "buttonPrimary", "pageActionButtonToTop", "initiallyHidden", "jsTooltip");
  button.title = Language.get("wcf.global.scrollUp");
  button.setAttribute("aria-hidden", "true");
  button.innerHTML = '<fa-icon size="32" name="angle-up" solid></fa-icon>';

  button.addEventListener("click", scrollToTop);

  return button;
}

function onScroll(): void {
  if (document.documentElement.classList.contains("disableScrolling")) {
    // Ignore any scroll events that take place while body scrolling is disabled,
    // because it messes up the scroll offsets.
    return;
  }

  const offset = window.pageYOffset;
  if (offset === _lastPosition) {
    // Ignore any scroll event that is fired but without a position change. This can
    // happen after closing a dialog that prevented the body from being scrolled.
    _resetLastPosition();
    return;
  }

  if (offset >= _toTopButtonThreshold) {
    if (_toTopButton.classList.contains("initiallyHidden")) {
      _toTopButton.classList.remove("initiallyHidden");
    }

    _toTopButton.setAttribute("aria-hidden", "false");
  } else {
    _toTopButton.setAttribute("aria-hidden", "true");
  }

  renderContainer();

  if (_lastPosition !== -1) {
    _wrapper.classList[offset < _lastPosition ? "remove" : "add"]("scrolledDown");
  }

  _lastPosition = -1;
}

function scrollToTop(event: MouseEvent): void {
  event.preventDefault();

  const topAnchor = document.getElementById("top")!;
  topAnchor.scrollIntoView({ behavior: "smooth" });
}

/**
 * Toggles the container's visibility.
 */
function renderContainer() {
  const visibleChild = Array.from(_container.children).find((element) => {
    return element.getAttribute("aria-hidden") === "false";
  });

  _container.classList[visibleChild ? "add" : "remove"]("active");

  if (visibleChild) {
    _wrapper.classList.add("pageActionHasContextButtons");
  } else {
    _wrapper.classList.remove("pageActionHasContextButtons");
  }
}

/**
 * Initializes the page action container.
 */
export function setup(): void {
  if (_didInit) {
    return;
  }

  _didInit = true;

  _wrapper = document.createElement("div");
  _wrapper.className = "pageAction";

  _container = document.createElement("div");
  _container.className = "pageActionButtons";
  _wrapper.appendChild(_container);

  _toTopButton = buildToTopButton();
  _wrapper.appendChild(_toTopButton);

  document.body.appendChild(_wrapper);

  const debounce = Core.debounce(onScroll, 100);
  window.addEventListener(
    "scroll",
    () => {
      if (_lastPosition === -1) {
        _lastPosition = window.pageYOffset;

        // Invoke the scroll handler once to immediately respond to
        // the user action before debouncing all further calls.
        window.setTimeout(() => {
          onScroll();

          _lastPosition = window.pageYOffset;
        }, 60);
      }

      debounce();
    },
    { passive: true },
  );

  window.addEventListener(
    "touchstart",
    () => {
      // Force a reset of the scroll position to trigger an immediate reaction
      // when the user touches the display again.
      if (_lastPosition !== -1) {
        _lastPosition = -1;
      }
    },
    { passive: true },
  );

  UiScreen.on("screen-sm-down", {
    match() {
      _toTopButtonThreshold = 50;
    },
    unmatch() {
      _toTopButtonThreshold = 300;
    },
    setup() {
      _toTopButtonThreshold = 50;
    },
  });

  onScroll();
}

/**
 * Adds a button to the page action list. You can optionally provide a button name to
 * insert the button right before it. Unmatched button names or empty value will cause
 * the button to be prepended to the list.
 */
export function add(buttonName: string, button: HTMLElement, insertBeforeButton?: string): void {
  setup();

  // The wrapper is required for backwards compatibility, because some implementations rely on a
  // dedicated parent element to insert elements, for example, for drop-down menus.
  const wrapper = document.createElement("div");
  wrapper.className = "pageActionButton";
  wrapper.dataset.name = buttonName;
  wrapper.setAttribute("aria-hidden", "true");

  button.classList.add("button");
  button.classList.add("buttonPrimary");
  wrapper.appendChild(button);

  let insertBefore: HTMLElement | null = null;
  if (insertBeforeButton) {
    insertBefore = _buttons.get(insertBeforeButton) || null;
    if (insertBefore) {
      insertBefore = insertBefore.parentElement;
    }
  }

  if (!insertBefore && _container.childElementCount) {
    insertBefore = _container.children[0] as HTMLElement;
  }
  if (!insertBefore) {
    insertBefore = _container.firstChild as HTMLElement;
  }

  _container.insertBefore(wrapper, insertBefore);
  _wrapper.classList.remove("scrolledDown");

  _buttons.set(buttonName, button);

  // Query a layout related property to force a reflow, otherwise the transition is optimized away.
  // eslint-disable-next-line @typescript-eslint/no-unused-expressions
  wrapper.offsetParent;

  // Toggle the visibility to force the transition to be applied.
  wrapper.setAttribute("aria-hidden", "false");

  renderContainer();
}

/**
 * Returns true if there is a registered button with the provided name.
 */
export function has(buttonName: string): boolean {
  return _buttons.has(buttonName);
}

/**
 * Returns the stored button by name or undefined.
 */
export function get(buttonName: string): HTMLElement | undefined {
  return _buttons.get(buttonName);
}

/**
 * Removes a button by its button name.
 */
export function remove(buttonName: string): void {
  const button = _buttons.get(buttonName);
  if (button !== undefined) {
    const listItem = button.parentElement!;
    const callback = () => {
      try {
        if (Core.stringToBool(listItem.getAttribute("aria-hidden"))) {
          _container.removeChild(listItem);
          _buttons.delete(buttonName);
        }

        listItem.removeEventListener("transitionend", callback);
      } catch {
        // ignore errors if the element has already been removed
      }
    };

    listItem.addEventListener("transitionend", callback);

    hide(buttonName);
  }
}

/**
 * Hides a button by its button name.
 */
export function hide(buttonName: string): void {
  const button = _buttons.get(buttonName);
  if (button) {
    const parent = button.parentElement!;
    parent.setAttribute("aria-hidden", "true");

    renderContainer();
  }
}

/**
 * Shows a button by its button name.
 */
export function show(buttonName: string): void {
  const button = _buttons.get(buttonName);
  if (button) {
    const parent = button.parentElement!;
    if (parent.classList.contains("initiallyHidden")) {
      parent.classList.remove("initiallyHidden");
    }

    parent.setAttribute("aria-hidden", "false");
    _wrapper.classList.remove("scrolledDown");

    renderContainer();
  }
}
