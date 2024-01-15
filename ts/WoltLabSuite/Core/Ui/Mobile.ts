/**
 * Modifies the interface to provide a better usability for mobile devices.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { createFocusTrap, FocusTrap } from "focus-trap";
import * as Core from "../Core";
import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import * as Environment from "../Environment";
import * as UiAlignment from "./Alignment";
import UiCloseOverlay from "./CloseOverlay";
import * as UiDropdownReusable from "./Dropdown/Reusable";
import { PageMenuMain } from "./Page/Menu/Main";
import { PageMenuMainProvider } from "./Page/Menu/Main/Provider";
import { hasValidUserMenu, PageMenuUser } from "./Page/Menu/User";
import * as UiScreen from "./Screen";
import * as Language from "../Language";

let _dropdownMenu: HTMLUListElement | null = null;
let _dropdownMenuMessage: HTMLElement | null = null;
let _enabled = false;
let _enabledLGTouchNavigation = false;
let _enableMobileMenu = false;
let _focusTrap: FocusTrap | undefined = undefined;
const _knownMessages = new WeakSet<HTMLElement>();
let _mobileSidebarEnabled = false;
let _pageMenuMain: PageMenuMain;
let _pageMenuUser: PageMenuUser | undefined = undefined;
let _messageGroups: HTMLCollection | null = null;
let _pageMenuMainProvider: PageMenuMainProvider;
const _sidebars: HTMLElement[] = [];

function init(): void {
  _enabled = true;

  initButtonGroupNavigation();
  initMessages();
  initMobileMenu();

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/Mobile", closeAllMenus);
  DomChangeListener.add("WoltLabSuite/Core/Ui/Mobile", () => {
    initButtonGroupNavigation();
    initMessages();
  });

  document.addEventListener("scroll", () => closeDropdown(), { passive: true });
}

function initButtonGroupNavigation(): void {
  document.querySelectorAll(".buttonGroupNavigation").forEach((navigation) => {
    if (navigation.classList.contains("jsMobileButtonGroupNavigation")) {
      return;
    } else {
      navigation.classList.add("jsMobileButtonGroupNavigation");
    }

    const list = navigation.querySelector(".buttonList") as HTMLUListElement;
    if (list.childElementCount === 0) {
      // ignore objects without options
      return;
    }

    navigation.parentElement!.classList.add("hasMobileNavigation");

    const button = document.createElement("button");
    button.type = "button";
    button.innerHTML = '<fa-icon size="24" name="ellipsis-vertical"></fa-icon>';
    button.classList.add("dropdownLabel");
    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      closeAllMenus();

      navigation.classList.toggle("open");
    });

    list.addEventListener("click", function (event) {
      event.stopPropagation();
      navigation.classList.remove("open");
    });

    navigation.insertBefore(button, navigation.firstChild);
  });
}

function initMessages(): void {
  document.querySelectorAll(".message").forEach((message: HTMLElement) => {
    if (_knownMessages.has(message)) {
      return;
    }

    const navigation = message.querySelector(".jsMobileNavigation") as HTMLAnchorElement;
    if (navigation) {
      navigation.addEventListener("click", (event) => {
        event.stopPropagation();

        // mimic dropdown behavior
        window.setTimeout(() => {
          navigation.classList.remove("open");
        }, 10);
      });

      const quickOptions = message.querySelector(".messageQuickOptions") as HTMLElement;
      if (quickOptions && navigation.childElementCount) {
        quickOptions.classList.add("active");

        let buttonWrapper = quickOptions.querySelector(".messageQuickOptionsMobile");
        if (buttonWrapper === null) {
          buttonWrapper = document.createElement("li");
          buttonWrapper.innerHTML = `
            <button type="button" aria-label="${Language.get("wcf.global.button.more")}">
              <fa-icon name="ellipsis-vertical"></fa-icon>
            </button>
          `;
          buttonWrapper.classList.add("messageQuickOptionsMobile");
          quickOptions.append(buttonWrapper);
        }

        const button = buttonWrapper.querySelector("button")!;
        button.addEventListener("click", (event) => {
          event.stopPropagation();

          toggleMobileNavigation(message, quickOptions, navigation);
        });
      }
    }
    _knownMessages.add(message);
  });
}

function initMobileMenu(): void {
  if (_enableMobileMenu) {
    _pageMenuMain = new PageMenuMain(_pageMenuMainProvider);
    _pageMenuMain.enable();

    if (hasValidUserMenu()) {
      _pageMenuUser = new PageMenuUser();
      _pageMenuUser.enable();
    }
  }
}

function closeAllMenus(): void {
  document.querySelectorAll(".jsMobileButtonGroupNavigation.open, .jsMobileNavigation.open").forEach((menu) => {
    menu.classList.remove("open");
  });

  if (_enabled && _dropdownMenu) {
    closeDropdown();
  }
}

function enableMobileSidebar(): void {
  _mobileSidebarEnabled = true;
}

function disableMobileSidebar(): void {
  _mobileSidebarEnabled = false;
  _sidebars.forEach(function (sidebar) {
    sidebar.classList.remove("open");
  });
}

function setupMobileSidebar(): void {
  _sidebars.forEach(function (sidebar) {
    sidebar.addEventListener("mousedown", function (event) {
      if (_mobileSidebarEnabled && event.target === sidebar) {
        event.preventDefault();
        sidebar.classList.toggle("open");
      }
    });
  });
  _mobileSidebarEnabled = true;
}

function closeDropdown(): void {
  _dropdownMenu?.classList.remove("dropdownOpen");
}

function toggleMobileNavigation(message: HTMLElement, quickOptions: HTMLElement, navigation: HTMLElement): void {
  if (_dropdownMenu === null) {
    _dropdownMenu = document.createElement("ul");
    _dropdownMenu.className = "dropdownMenu";
    UiDropdownReusable.init("com.woltlab.wcf.jsMobileNavigation", _dropdownMenu);
  } else if (_dropdownMenu.classList.contains("dropdownOpen")) {
    closeDropdown();
    _focusTrap!.deactivate();
    _focusTrap = undefined;

    if (_dropdownMenuMessage === message) {
      // toggle behavior
      return;
    }
  }
  _dropdownMenu.innerHTML = "";
  UiCloseOverlay.execute();
  rebuildMobileNavigation(navigation);
  const previousNavigation = navigation.previousElementSibling as HTMLElement;
  if (previousNavigation && previousNavigation.classList.contains("messageFooterButtonsExtra")) {
    const divider = document.createElement("li");
    divider.className = "dropdownDivider";
    _dropdownMenu.appendChild(divider);
    rebuildMobileNavigation(previousNavigation);
  }
  UiAlignment.set(_dropdownMenu, quickOptions, {
    horizontal: "right",
    allowFlip: "vertical",
  });
  _dropdownMenu.classList.add("dropdownOpen");
  _dropdownMenuMessage = message;

  _focusTrap = createFocusTrap(_dropdownMenu, {
    allowOutsideClick: true,
    escapeDeactivates(): boolean {
      toggleMobileNavigation(message, quickOptions, navigation);

      return false;
    },
    setReturnFocus: quickOptions,
  });
  _focusTrap.activate();
}

function setupLGTouchNavigation(): void {
  _enabledLGTouchNavigation = true;
  document.querySelectorAll(".boxMenuHasChildren > a").forEach((element: HTMLElement) => {
    element.addEventListener(
      "touchstart",
      (event) => {
        if (_enabledLGTouchNavigation && element.getAttribute("aria-expanded") === "false") {
          event.preventDefault();

          element.setAttribute("aria-expanded", "true");

          // Register an new event listener after the touch ended, which is triggered once when an
          // element on the page is pressed. This allows us to reset the touch status of the navigation
          // entry when the entry is no longer open, so that it does not redirect to the page when you
          // click it again.
          element.addEventListener(
            "touchend",
            () => {
              document.body.addEventListener(
                "touchstart",
                () => {
                  document.body.addEventListener(
                    "touchend",
                    (event) => {
                      const parent = element.parentElement!;
                      const target = event.target as HTMLElement;
                      if (!parent.contains(target) && target !== parent) {
                        element.setAttribute("aria-expanded", "false");
                      }
                    },
                    {
                      once: true,
                    },
                  );
                },
                {
                  once: true,
                },
              );
            },
            { once: true },
          );
        }
      },
      { passive: false },
    );
  });
}

function enableLGTouchNavigation(): void {
  _enabledLGTouchNavigation = true;
}

function disableLGTouchNavigation(): void {
  _enabledLGTouchNavigation = false;
}

function rebuildMobileNavigation(navigation: HTMLElement): void {
  navigation.querySelectorAll(".button").forEach((button: HTMLElement) => {
    if (button.classList.contains("ignoreMobileNavigation") || button.classList.contains("reactButton")) {
      return;
    }

    const item = document.createElement("li");
    if (button.classList.contains("active")) {
      item.className = "active";
    }

    const label = button.querySelector("span:not(.icon)")!;
    item.innerHTML = `<a href="#">${label.textContent!}</a>`;
    item.children[0].addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      if (button.nodeName === "A") {
        button.click();
      } else {
        Core.triggerEvent(button, "click");
      }
      closeDropdown();
    });
    _dropdownMenu!.appendChild(item);
  });
}

/**
 * Initializes the mobile UI.
 */
export function setup(enableMobileMenu: boolean, pageMenuMainProvider: PageMenuMainProvider): void {
  _enableMobileMenu = enableMobileMenu;
  _pageMenuMainProvider = pageMenuMainProvider;

  document.querySelectorAll(".boxesSidebarLeft").forEach((sidebar: HTMLElement) => {
    _sidebars.push(sidebar);
  });

  if (Environment.touch()) {
    document.documentElement.classList.add("touch");
  }
  if (Environment.platform() !== "desktop") {
    document.documentElement.classList.add("mobile");
  }

  const messageGroupList = document.querySelector(".messageGroupList");
  if (messageGroupList) {
    _messageGroups = messageGroupList.getElementsByClassName("messageGroup");
  }

  UiScreen.on("screen-md-down", {
    match: enable,
    unmatch: disable,
    setup: init,
  });
  UiScreen.on("screen-sm-down", {
    match: enableShadow,
    unmatch: disableShadow,
    setup: enableShadow,
  });
  UiScreen.on("screen-md-down", {
    match: enableMobileSidebar,
    unmatch: disableMobileSidebar,
    setup: setupMobileSidebar,
  });

  // On the large tablets (e.g. iPad Pro) the navigation is not usable, because there is not the mobile
  // layout displayed, but the normal one for the desktop. The navigation reacts to a hover status if a
  // menu item has several submenu items. Logically, this cannot be created with the tablet, so that we
  // display the submenu here after a single click and only follow the link after another click.
  if (Environment.touch() && (Environment.platform() === "ios" || Environment.platform() === "android")) {
    UiScreen.on("screen-lg", {
      match: enableLGTouchNavigation,
      unmatch: disableLGTouchNavigation,
      setup: setupLGTouchNavigation,
    });
  }
}

/**
 * Enables the mobile UI.
 */
export function enable(): void {
  UiCloseOverlay.execute();

  _enabled = true;
  if (_enableMobileMenu) {
    _pageMenuMain.enable();
    _pageMenuUser?.enable();
  }
}

/**
 * Enables shadow links for larger click areas on messages.
 */
export function enableShadow(): void {
  if (_messageGroups) {
    rebuildShadow(_messageGroups, ".messageGroupLink");
  }
}

/**
 * Disables the mobile UI.
 */
export function disable(): void {
  UiCloseOverlay.execute();

  _enabled = false;
  if (_enableMobileMenu) {
    _pageMenuMain.disable();
    _pageMenuUser?.disable();
  }
}

/**
 * Disables shadow links.
 */
export function disableShadow(): void {
  if (_messageGroups) {
    removeShadow(_messageGroups);
  }
  if (_dropdownMenu) {
    closeDropdown();
  }
}

export function rebuildShadow(elements: HTMLElement[] | HTMLCollection, linkSelector: string): void {
  Array.from(elements).forEach((element) => {
    const parent = element.parentElement as HTMLElement;

    let shadow = parent.querySelector(".mobileLinkShadow") as HTMLAnchorElement;
    if (shadow === null) {
      const link = element.querySelector(linkSelector) as HTMLAnchorElement;
      if (link.href) {
        shadow = document.createElement("a");
        shadow.className = "mobileLinkShadow";
        shadow.href = link.href;
        shadow.setAttribute("aria-labelledby", DomUtil.identify(link));
        parent.appendChild(shadow);
        parent.classList.add("mobileLinkShadowContainer");
      }
    }
  });
}

export function removeShadow(elements: HTMLElement[] | HTMLCollection): void {
  Array.from(elements).forEach((element) => {
    const parent = element.parentElement!;
    if (parent.classList.contains("mobileLinkShadowContainer")) {
      const shadow = parent.querySelector(".mobileLinkShadow");
      if (shadow !== null) {
        shadow.remove();
      }

      parent.classList.remove("mobileLinkShadowContainer");
    }
  });
}
