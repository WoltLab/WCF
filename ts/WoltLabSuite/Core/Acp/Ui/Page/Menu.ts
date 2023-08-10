/**
 * Provides the ACP menu navigation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import PerfectScrollbar from "perfect-scrollbar";

import * as EventHandler from "../../../Event/Handler";
import * as UiScreen from "../../../Ui/Screen";

const _acpPageMenu = document.getElementById("acpPageMenu") as HTMLElement;
const _acpPageSubMenu = document.getElementById("acpPageSubMenu") as HTMLElement;
let _activeMenuItem = "";
const _menuItems = new Map<string, HTMLAnchorElement>();
const _menuItemContainers = new Map<string, HTMLOListElement>();
const _pageContainer = document.getElementById("pageContainer") as HTMLElement;
let _perfectScrollbarActive = false;
const _perfectScrollbars: Map<HTMLElement, PerfectScrollbar> = new Map();

/**
 * Initializes the ACP menu navigation.
 */
export function init(): void {
  document.querySelectorAll(".acpPageMenuLink").forEach((link: HTMLAnchorElement) => {
    const menuItem = link.dataset.menuItem!;
    if (link.classList.contains("active")) {
      _activeMenuItem = menuItem;
    }

    link.addEventListener("click", (ev) => toggle(ev));

    _menuItems.set(menuItem, link);
  });

  document.querySelectorAll(".acpPageSubMenuCategoryList").forEach((container: HTMLOListElement) => {
    const menuItem = container.dataset.menuItem!;
    _menuItemContainers.set(menuItem, container);
  });

  // menu is missing on the login page or during WCFSetup
  if (_acpPageMenu === null) {
    return;
  }

  UiScreen.on("screen-lg", {
    match: enablePerfectScrollbar,
    unmatch: disablePerfectScrollbar,
    setup: enablePerfectScrollbar,
  });

  window.addEventListener("resize", () => {
    if (_perfectScrollbarActive) {
      _perfectScrollbars.get(_acpPageMenu)?.update();
      _perfectScrollbars.get(_acpPageSubMenu)?.update();
    }
  });
}

function enablePerfectScrollbar(): void {
  const options = {
    suppressScrollX: true,
    wheelPropagation: false,
  };

  _perfectScrollbars.set(_acpPageMenu, new PerfectScrollbar(_acpPageMenu, options));
  _perfectScrollbars.set(_acpPageSubMenu, new PerfectScrollbar(_acpPageSubMenu, options));

  _perfectScrollbarActive = true;
}

function disablePerfectScrollbar(): void {
  _perfectScrollbars.get(_acpPageMenu)?.destroy();
  _perfectScrollbars.get(_acpPageSubMenu)?.destroy();

  _perfectScrollbarActive = false;
}

/**
 * Toggles a menu item.
 */
function toggle(event: MouseEvent): void {
  event.preventDefault();
  event.stopPropagation();

  const link = event.currentTarget as HTMLAnchorElement;
  const menuItem = link.dataset.menuItem!;
  let acpPageSubMenuActive = false;

  // remove active marking from currently active menu
  if (_activeMenuItem) {
    _menuItems.get(_activeMenuItem)!.classList.remove("active");
    _menuItemContainers.get(_activeMenuItem)!.classList.remove("active");
  }

  if (_activeMenuItem === menuItem) {
    // current item was active before
    _activeMenuItem = "";
  } else {
    link.classList.add("active");
    _menuItemContainers.get(menuItem)!.classList.add("active");

    _activeMenuItem = menuItem;
    acpPageSubMenuActive = true;
  }

  if (acpPageSubMenuActive) {
    _pageContainer.classList.add("acpPageSubMenuActive");
  } else {
    _pageContainer.classList.remove("acpPageSubMenuActive");
  }

  if (_perfectScrollbarActive) {
    _acpPageSubMenu.scrollTop = 0;

    _perfectScrollbars.get(_acpPageSubMenu)?.update();
  }

  EventHandler.fire("com.woltlab.wcf.AcpMenu", "resize");
}
