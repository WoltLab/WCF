/**
 * Provides the menu items for the mobile main menu in the admin panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Page/Menu/Main/Backend
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
      title,
    };
  });
}

function getMenuItems(category: HTMLOListElement): MenuItem[] {
  return Array.from(category.querySelectorAll(".acpPageSubMenuLink")).map((link: HTMLAnchorElement) => {
    return {
      active: link.classList.contains("active"),
      children: [],
      counter: 0,
      depth: 2,
      link: link.href,
      title: link.textContent!,
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
          title,
        };
      },
    );

    return menuItems;
  }
}

export default AcpUiPageMenuMainBackend;
