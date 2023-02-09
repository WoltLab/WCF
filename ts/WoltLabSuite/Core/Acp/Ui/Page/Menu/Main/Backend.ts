/**
 * Provides the menu items for the mobile main menu in the admin panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { MenuItem, PageMenuMainProvider } from "../../../../../Ui/Page/Menu/Main/Provider";

function getSubMenuItems(subMenu: HTMLElement, menuItem: string): MenuItem[] {
  const categoryList = subMenu.querySelector(`.acpPageSubMenuCategoryList[data-menu-item="${menuItem}"]`)!;
  return Array.from(categoryList.querySelectorAll(".acpPageSubMenuCategory")).map((category: HTMLOListElement) => {
    const title = category.querySelector("span")!.textContent!;
    const children = getMenuItems(category);

    return {
      active: false,
      children,
      counter: 0,
      depth: 1,
      identifier: null,
      title,
    };
  });
}

function getMenuItems(category: HTMLOListElement): MenuItem[] {
  return Array.from(category.querySelectorAll(".acpPageSubMenuLink")).map((link: HTMLAnchorElement) => {
    const children = getMenuItemActions(link);

    let active = link.classList.contains("active");
    if (children.length === 0 && link.parentElement!.classList.contains("active")) {
      active = true;
    }

    return {
      active,
      children,
      counter: 0,
      depth: 2,
      identifier: null,
      link: link.href,
      title: link.textContent!,
    };
  });
}

function getMenuItemActions(link: HTMLAnchorElement): MenuItem[] {
  const listItem = link.parentElement!;
  if (!listItem.classList.contains("acpPageSubMenuLinkWrapper")) {
    return [];
  }

  return Array.from(listItem.querySelectorAll(".acpPageSubMenuIcon")).map((action: HTMLAnchorElement) => {
    return {
      active: action.classList.contains("active"),
      children: [],
      counter: 0,
      depth: 2,
      identifier: null,
      link: action.href,
      title: action.dataset.tooltip || action.title,
    };
  });
}

export class AcpUiPageMenuMainBackend implements PageMenuMainProvider {
  getMenuItems(_container: HTMLElement): MenuItem[] {
    const menu = document.getElementById("acpPageMenu")!;
    const subMenu = document.getElementById("acpPageSubMenu")!;

    const menuItems: MenuItem[] = Array.from(menu.querySelectorAll(".acpPageMenuLink")).map(
      (link: HTMLAnchorElement) => {
        const menuItem = link.dataset.menuItem!;
        const title = link.querySelector(".acpPageMenuItemLabel")!.textContent!;
        const children = getSubMenuItems(subMenu, menuItem);

        return {
          active: false,
          children,
          counter: 0,
          depth: 0,
          identifier: null,
          title,
        };
      },
    );

    return menuItems;
  }
}

export default AcpUiPageMenuMainBackend;
