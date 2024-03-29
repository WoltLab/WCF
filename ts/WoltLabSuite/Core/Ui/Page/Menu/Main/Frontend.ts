/**
 * Provides the menu items for the mobile main menu in the frontend.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { MenuItem, MenuItemDepth, PageMenuMainProvider } from "./Provider";

function normalizeMenuItem(menuItem: HTMLElement, depth: MenuItemDepth): MenuItem {
  const anchor = menuItem.querySelector(".boxMenuLink") as HTMLAnchorElement;
  const title = anchor.querySelector(".boxMenuLinkTitle")!.textContent as string;

  let counter = 0;
  const outstandingItems = anchor.querySelector(".boxMenuLinkOutstandingItems");
  if (outstandingItems) {
    counter = parseInt(outstandingItems.textContent!.replace(/[^0-9]/, ""), 10);
  }

  const subMenu = menuItem.querySelector("ol");
  let children: MenuItem[] = [];
  if (subMenu instanceof HTMLOListElement) {
    let childDepth = depth;
    if (childDepth < 3) {
      childDepth = (depth + 1) as MenuItemDepth;
    }

    children = Array.from(subMenu.children).map((subMenuItem: HTMLElement) => {
      return normalizeMenuItem(subMenuItem, childDepth);
    });
  }

  // `link.href` represents the computed link, not the raw value.
  const href = anchor.getAttribute("href");
  let link: string | undefined = undefined;
  let openInNewWindow: boolean | undefined = undefined;
  if (href && href !== "#") {
    link = anchor.href;

    if (anchor.target === "_blank") {
      openInNewWindow = true;
    }
  }

  const active = menuItem.classList.contains("active");

  const identifier = anchor.parentElement!.dataset.identifier!;

  return {
    active,
    children,
    counter,
    depth,
    identifier,
    link,
    openInNewWindow,
    title,
  };
}

export class UiPageMenuMainFrontend implements PageMenuMainProvider {
  getMenuItems(container: HTMLElement): MenuItem[] {
    return Array.from(container.children).map((element: HTMLElement) => {
      return normalizeMenuItem(element, 0);
    });
  }
}

export default UiPageMenuMainFrontend;
