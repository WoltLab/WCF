/**
 * Dynamically transforms menu-like structures to handle items exceeding the available width
 * by moving them into a separate dropdown.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 5.5 This module is unused and will be removed.
 * @woltlabExcludeBundle all
 */

import DomChangeListener from "../Dom/Change/Listener";
import DomUtil from "../Dom/Util";
import * as DomTraverse from "../Dom/Traverse";
import UiDropdownSimple from "./Dropdown/Simple";

const _containers = new Map<string, HTMLElement>();
const _dropdowns = new Map<string, HTMLLIElement>();
const _dropdownMenus = new Map<string, HTMLUListElement>();
const _itemLists = new Map<string, HTMLUListElement>();

/**
 * Register default menus and set up event listeners.
 */
export function setup(): void {
  if (document.getElementById("mainMenu") !== null) {
    register("mainMenu");
  }

  const navigationHeader = document.querySelector(".navigationHeader");
  if (navigationHeader !== null) {
    register(DomUtil.identify(navigationHeader));
  }

  window.addEventListener("resize", rebuildAll);
  DomChangeListener.add("WoltLabSuite/Core/Ui/FlexibleMenu", registerTabMenus);
}

/**
 * Registers a menu by element id.
 */
export function register(containerId: string): void {
  const container = document.getElementById(containerId);
  if (container === null) {
    throw new Error("Expected a valid element id, '" + containerId + "' does not exist.");
  }

  if (_containers.has(containerId)) {
    return;
  }

  const list = DomTraverse.childByTag(container, "UL");
  if (list === null) {
    throw new Error("Expected an <ul> element as child of container '" + containerId + "'.");
  }

  _containers.set(containerId, container);
  _itemLists.set(containerId, list);

  rebuild(containerId);
}

/**
 * Registers tab menus.
 */
export function registerTabMenus(): void {
  document
    .querySelectorAll(".tabMenuContainer:not(.jsFlexibleMenuEnabled), .messageTabMenu:not(.jsFlexibleMenuEnabled)")
    .forEach((tabMenu) => {
      const nav = DomTraverse.childByTag(tabMenu, "NAV");
      if (nav !== null) {
        tabMenu.classList.add("jsFlexibleMenuEnabled");
        register(DomUtil.identify(nav));
      }
    });
}

/**
 * Rebuilds all menus, e.g. on window resize.
 */
export function rebuildAll(): void {
  _containers.forEach((container, containerId) => {
    rebuild(containerId);
  });
}

/**
 * Rebuild the menu identified by given element id.
 */
export function rebuild(containerId: string): void {
  const container = _containers.get(containerId);
  if (container === undefined) {
    throw new Error("Expected a valid element id, '" + containerId + "' is unknown.");
  }

  const styles = window.getComputedStyle(container);
  const parent = container.parentNode as HTMLElement;
  let availableWidth = parent.clientWidth;
  availableWidth -= DomUtil.styleAsInt(styles, "margin-left");
  availableWidth -= DomUtil.styleAsInt(styles, "margin-right");

  const list = _itemLists.get(containerId)!;
  const items = DomTraverse.childrenByTag(list, "LI");
  let dropdown = _dropdowns.get(containerId);
  let dropdownWidth = 0;
  if (dropdown !== undefined) {
    // show all items for calculation
    for (let i = 0, length = items.length; i < length; i++) {
      const item = items[i];
      if (item.classList.contains("dropdown")) {
        continue;
      }

      DomUtil.show(item);
    }
    if (dropdown.parentNode !== null) {
      dropdownWidth = DomUtil.outerWidth(dropdown);
    }
  }

  const currentWidth = list.scrollWidth - dropdownWidth;
  const hiddenItems: HTMLLIElement[] = [];
  if (currentWidth > availableWidth) {
    // hide items starting with the last one
    for (let i = items.length - 1; i >= 0; i--) {
      const item = items[i];

      // ignore dropdown and active item
      if (
        item.classList.contains("dropdown") ||
        item.classList.contains("active") ||
        item.classList.contains("ui-state-active")
      ) {
        continue;
      }

      hiddenItems.push(item);
      DomUtil.hide(item);

      if (list.scrollWidth < availableWidth) {
        break;
      }
    }
  }

  if (hiddenItems.length) {
    let dropdownMenu: HTMLUListElement;
    if (dropdown === undefined) {
      dropdown = document.createElement("li");
      dropdown.className = "dropdown jsFlexibleMenuDropdown";

      const icon = document.createElement("a");
      icon.innerHTML = '<fa-icon name="list"></fa-icon>';
      dropdown.appendChild(icon);

      dropdownMenu = document.createElement("ul");
      dropdownMenu.classList.add("dropdownMenu");
      dropdown.appendChild(dropdownMenu);

      _dropdowns.set(containerId, dropdown);
      _dropdownMenus.set(containerId, dropdownMenu);
      UiDropdownSimple.init(icon);
    } else {
      dropdownMenu = _dropdownMenus.get(containerId)!;
    }

    if (dropdown.parentNode === null) {
      list.appendChild(dropdown);
    }

    // build dropdown menu
    const fragment = document.createDocumentFragment();
    hiddenItems.forEach((hiddenItem) => {
      const item = document.createElement("li");
      item.innerHTML = hiddenItem.innerHTML;

      item.addEventListener("click", (event) => {
        event.preventDefault();

        hiddenItem.querySelector("a")?.click();

        // force a rebuild to guarantee the active item being visible
        setTimeout(() => {
          rebuild(containerId);
        }, 59);
      });

      fragment.appendChild(item);
    });

    dropdownMenu.innerHTML = "";
    dropdownMenu.appendChild(fragment);
  } else if (dropdown !== undefined && dropdown.parentNode !== null) {
    dropdown.remove();
  }
}
