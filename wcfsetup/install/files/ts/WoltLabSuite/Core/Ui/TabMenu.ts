/**
 * Common interface for tab menu access.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/TabMenu (alias)
 * @module  WoltLabSuite/Core/Ui/TabMenu
 */

import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import TabMenuSimple from "./TabMenu/Simple";
import UiCloseOverlay from "./CloseOverlay";
import * as UiScreen from "./Screen";
import * as UiScroll from "./Scroll";

let _activeList: HTMLUListElement | null = null;
let _enableTabScroll = false;
const _tabMenus = new Map<string, TabMenuSimple>();

/**
 * Initializes available tab menus.
 */
function init() {
  document.querySelectorAll(".tabMenuContainer:not(.staticTabMenuContainer)").forEach((container: HTMLElement) => {
    const containerId = DomUtil.identify(container);
    if (_tabMenus.has(containerId)) {
      return;
    }

    let tabMenu = new TabMenuSimple(container);
    if (!tabMenu.validate()) {
      return;
    }

    const returnValue = tabMenu.init();
    _tabMenus.set(containerId, tabMenu);
    if (returnValue instanceof HTMLElement) {
      const parent = returnValue.parentNode as HTMLElement;
      const parentTabMenu = getTabMenu(parent.id);
      if (parentTabMenu) {
        tabMenu = parentTabMenu;
        tabMenu.select(returnValue.id, undefined, true);
      }
    }

    const list = document.querySelector("#" + containerId + " > nav > ul") as HTMLUListElement;
    list.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (event.target === list) {
        list.classList.add("active");
        _activeList = list;
      } else {
        list.classList.remove("active");
        _activeList = null;
      }
    });

    // bind scroll listener
    container.querySelectorAll(".tabMenu, .menu").forEach((menu: HTMLElement) => {
      function callback() {
        timeout = null;

        rebuildMenuOverflow(menu);
      }

      let timeout: number | null = null;
      menu.querySelector("ul")!.addEventListener(
        "scroll",
        () => {
          if (timeout !== null) {
            window.clearTimeout(timeout);
          }

          // slight delay to avoid calling this function too often
          timeout = window.setTimeout(callback, 10);
        },
        { passive: true }
      );
    });

    // The validation of input fields, e.g. [required], yields strange results when
    // the erroneous element is hidden inside a tab. The submit button will appear
    // to not work and a warning is displayed on the console. We can work around this
    // by manually checking if the input fields validate on submit and display the
    // parent tab ourselves.
    const form = container.closest("form");
    if (form !== null) {
      const submitButton = form.querySelector('input[type="submit"]');
      if (submitButton !== null) {
        submitButton.addEventListener("click", (event) => {
          if (event.defaultPrevented) {
            return;
          }

          container.querySelectorAll("input, select").forEach((element: HTMLInputElement | HTMLSelectElement) => {
            if (!element.checkValidity()) {
              event.preventDefault();

              // Select the tab that contains the erroneous element.
              const tabMenu = getTabMenu(element.closest(".tabMenuContainer")!.id)!;
              const tabMenuContent = element.closest(".tabMenuContent") as HTMLElement;
              tabMenu.select(tabMenuContent.dataset.name || "");
              UiScroll.element(element, () => {
                element.reportValidity();
              });

              return;
            }
          });
        });
      }
    }
  });
}

/**
 * Selects the first tab containing an element with class `formError`.
 */
function selectErroneousTabs(): void {
  _tabMenus.forEach((tabMenu) => {
    let foundError = false;
    tabMenu.getContainers().forEach((container) => {
      if (!foundError && container.querySelector(".formError") !== null) {
        foundError = true;
        tabMenu.select(container.id);
      }
    });
  });
}

function scrollEnable(isSetup) {
  _enableTabScroll = true;
  _tabMenus.forEach((tabMenu) => {
    const activeTab = tabMenu.getActiveTab();
    if (isSetup) {
      rebuildMenuOverflow(activeTab.closest(".menu, .tabMenu") as HTMLElement);
    } else {
      scrollToTab(activeTab);
    }
  });
}

function scrollDisable() {
  _enableTabScroll = false;
}

function scrollMenu(list, left, scrollLeft, scrollWidth, width, paddingRight) {
  // allow some padding to indicate overflow
  if (paddingRight) {
    left -= 15;
  } else if (left > 0) {
    left -= 15;
  }

  if (left < 0) {
    left = 0;
  } else {
    // ensure that our left value is always within the boundaries
    left = Math.min(left, scrollWidth - width);
  }

  if (scrollLeft === left) {
    return;
  }

  list.classList.add("enableAnimation");

  // new value is larger, we're scrolling towards the end
  if (scrollLeft < left) {
    list.firstElementChild.style.setProperty("margin-left", `${scrollLeft - left}px`, "");
  } else {
    // new value is smaller, we're scrolling towards the start
    list.style.setProperty("padding-left", `${scrollLeft - left}px`, "");
  }

  setTimeout(() => {
    list.classList.remove("enableAnimation");
    list.firstElementChild.style.removeProperty("margin-left");
    list.style.removeProperty("padding-left");
    list.scrollLeft = left;
  }, 300);
}

function rebuildMenuOverflow(menu: HTMLElement): void {
  if (!_enableTabScroll) {
    return;
  }

  const width = menu.clientWidth;
  const list = menu.querySelector("ul") as HTMLElement;
  const scrollLeft = list.scrollLeft;
  const scrollWidth = list.scrollWidth;
  const overflowLeft = scrollLeft > 0;

  let overlayLeft = menu.querySelector(".tabMenuOverlayLeft");
  if (overflowLeft) {
    if (overlayLeft === null) {
      overlayLeft = document.createElement("span");
      overlayLeft.className = "tabMenuOverlayLeft icon icon24 fa-angle-left";
      overlayLeft.addEventListener("click", () => {
        const listWidth = list.clientWidth;
        scrollMenu(list, list.scrollLeft - ~~(listWidth / 2), list.scrollLeft, list.scrollWidth, listWidth, 0);
      });
      menu.insertBefore(overlayLeft, menu.firstChild);
    }

    overlayLeft.classList.add("active");
  } else if (overlayLeft !== null) {
    overlayLeft.classList.remove("active");
  }

  const overflowRight = width + scrollLeft < scrollWidth;
  let overlayRight = menu.querySelector(".tabMenuOverlayRight");
  if (overflowRight) {
    if (overlayRight === null) {
      overlayRight = document.createElement("span");
      overlayRight.className = "tabMenuOverlayRight icon icon24 fa-angle-right";
      overlayRight.addEventListener("click", () => {
        const listWidth = list.clientWidth;
        scrollMenu(list, list.scrollLeft + ~~(listWidth / 2), list.scrollLeft, list.scrollWidth, listWidth, 0);
      });

      menu.appendChild(overlayRight);
    }
    overlayRight.classList.add("active");
  } else if (overlayRight !== null) {
    overlayRight.classList.remove("active");
  }
}

/**
 * Sets up tab menus and binds listeners.
 */
export function setup(): void {
  init();
  selectErroneousTabs();

  DomChangeListener.add("WoltLabSuite/Core/Ui/TabMenu", init);
  UiCloseOverlay.add("WoltLabSuite/Core/Ui/TabMenu", () => {
    if (_activeList) {
      _activeList.classList.remove("active");
      _activeList = null;
    }
  });

  UiScreen.on("screen-sm-down", {
    match() {
      scrollEnable(false);
    },
    unmatch: scrollDisable,
    setup() {
      scrollEnable(true);
    },
  });

  window.addEventListener("hashchange", () => {
    const hash = TabMenuSimple.getIdentifierFromHash();
    const element = hash ? document.getElementById(hash) : null;
    if (element !== null && element.classList.contains("tabMenuContent")) {
      _tabMenus.forEach((tabMenu) => {
        if (tabMenu.hasTab(hash)) {
          tabMenu.select(hash);
        }
      });
    }
  });

  const hash = TabMenuSimple.getIdentifierFromHash();
  if (hash) {
    window.setTimeout(() => {
      // check if page was initially scrolled using a tab id
      const tabMenuContent = document.getElementById(hash);
      if (tabMenuContent && tabMenuContent.classList.contains("tabMenuContent")) {
        const scrollY = window.scrollY || window.pageYOffset;
        if (scrollY > 0) {
          const parent = tabMenuContent.parentNode as HTMLElement;

          let offsetTop = parent.offsetTop - 50;
          if (offsetTop < 0) {
            offsetTop = 0;
          }

          if (scrollY > offsetTop) {
            let y = DomUtil.offset(parent).top;
            if (y <= 50) {
              y = 0;
            } else {
              y -= 50;
            }

            window.scrollTo(0, y);
          }
        }
      }
    }, 100);
  }
}

/**
 * Returns a TabMenuSimple instance for given container id.
 */
export function getTabMenu(containerId: string): TabMenuSimple | undefined {
  return _tabMenus.get(containerId);
}

export function scrollToTab(tab: HTMLElement): void {
  if (!_enableTabScroll) {
    return;
  }

  const list = tab.closest("ul")!;
  const width = list.clientWidth;
  const scrollLeft = list.scrollLeft;
  const scrollWidth = list.scrollWidth;
  if (width === scrollWidth) {
    // no overflow, ignore
    return;
  }

  // check if tab is currently visible
  const left = tab.offsetLeft;
  let shouldScroll = false;
  if (left < scrollLeft) {
    shouldScroll = true;
  }

  let paddingRight = false;
  if (!shouldScroll) {
    const visibleWidth = width - (left - scrollLeft);
    let virtualWidth = tab.clientWidth;
    if (tab.nextElementSibling !== null) {
      paddingRight = true;
      virtualWidth += 20;
    }

    if (visibleWidth < virtualWidth) {
      shouldScroll = true;
    }
  }

  if (shouldScroll) {
    scrollMenu(list, left, scrollLeft, scrollWidth, width, paddingRight);
  }
}
