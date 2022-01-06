/**
 * Provides the touch-friendly main menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/Main
 */

import PageMenuContainer, { Orientation } from "./Container";
import { PageMenuProvider } from "./Provider";
import * as Language from "../../../Language";
import DomUtil from "../../../Dom/Util";
import { MenuItem, PageMenuMainProvider } from "./Main/Provider";

type CallbackOpen = (event: MouseEvent) => void;

export class PageMenuMain implements PageMenuProvider {
  private readonly callbackOpen: CallbackOpen;
  private readonly container: PageMenuContainer;
  private readonly mainMenu: HTMLElement;
  private readonly menuItemProvider: PageMenuMainProvider;

  constructor(menuItemProvider: PageMenuMainProvider) {
    this.mainMenu = document.querySelector(".mainMenu")!;
    this.menuItemProvider = menuItemProvider;

    this.container = new PageMenuContainer(this, Orientation.Left);

    this.callbackOpen = (event) => {
      event.preventDefault();
      event.stopPropagation();

      this.container.toggle();
    };
  }

  enable(): void {
    this.mainMenu.setAttribute("aria-expanded", "false");
    this.mainMenu.setAttribute("aria-label", Language.get("wcf.menu.page"));
    this.mainMenu.setAttribute("role", "button");
    this.mainMenu.tabIndex = 0;
    this.mainMenu.addEventListener("click", this.callbackOpen);

    this.refreshUnreadIndicator();
  }

  disable(): void {
    this.container.close();

    this.mainMenu.removeAttribute("aria-expanded");
    this.mainMenu.removeAttribute("aria-label");
    this.mainMenu.removeAttribute("role");
    this.mainMenu.removeAttribute("tabindex");
    this.mainMenu.removeEventListener("click", this.callbackOpen);
  }

  getContent(): DocumentFragment {
    const container = document.createElement("div");
    container.classList.add("pageMenuMainContainer");
    container.addEventListener("scroll", () => this.updateOverflowIndicator(container), { passive: true });

    container.append(this.buildMainMenu());

    const footerMenu = this.buildFooterMenu();
    if (footerMenu) {
      container.append(footerMenu);
    }

    // Detect changes to the height of the children, for example, when a submenu is being expanded.
    const observer = new ResizeObserver(() => this.updateOverflowIndicator(container));
    Array.from(container.children).forEach((menu) => {
      observer.observe(menu);
    });

    const fragment = document.createDocumentFragment();
    fragment.append(container);

    return fragment;
  }

  getMenuButton(): HTMLElement {
    return this.mainMenu;
  }

  sleep(): void {
    /* Does nothing */
  }

  wakeup(): void {
    this.refreshUnreadIndicator();
  }

  private buildMainMenu(): HTMLElement {
    const boxMenu = this.mainMenu.querySelector(".boxMenu") as HTMLElement;

    const nav = this.buildMenu(boxMenu);
    nav.setAttribute("aria-label", window.PAGE_TITLE);
    nav.setAttribute("role", "navigation");

    this.showActiveMenuItem(nav);

    return nav;
  }

  private showActiveMenuItem(menu: HTMLElement): void {
    const activeMenuItem = menu.querySelector<HTMLElement>('.pageMenuMainItemLink[aria-current="page"]');
    if (activeMenuItem) {
      let element: HTMLElement | null = activeMenuItem;
      while (element && element.parentElement) {
        element = element.parentElement.closest<HTMLElement>(".pageMenuMainItemList");
        if (element) {
          element.hidden = false;

          const button = element.previousElementSibling;
          button?.setAttribute("aria-expanded", "true");
        }
      }
    }
  }

  private buildFooterMenu(): HTMLElement | null {
    const box = document.querySelector('.box[data-box-identifier="com.woltlab.wcf.FooterMenu"]');
    if (box === null) {
      return null;
    }

    const boxMenu = box.querySelector(".boxMenu") as HTMLElement;
    const nav = this.buildMenu(boxMenu);
    nav.classList.add("pageMenuMainNavigationFooter");

    const label = box.querySelector("nav")!.getAttribute("aria-label")!;
    nav.setAttribute("aria-label", label);

    return nav;
  }

  private buildMenu(boxMenu: HTMLElement): HTMLElement {
    const menuItems = this.menuItemProvider.getMenuItems(boxMenu);

    const nav = document.createElement("nav");
    nav.classList.add("pageMenuMainNavigation");
    nav.append(this.buildMenuItemList(menuItems));

    return nav;
  }

  private buildMenuItemList(menuItems: MenuItem[]): HTMLUListElement {
    const list = document.createElement("ul");
    list.classList.add("pageMenuMainItemList");

    menuItems
      .filter((menuItem) => {
        // Remove links that have no target (`#`) and do not contain any children.
        if (!menuItem.link && menuItem.children.length === 0) {
          return false;
        }

        return true;
      })
      .forEach((menuItem) => {
        list.append(this.buildMenuItem(menuItem));
      });

    return list;
  }

  private buildMenuItem(menuItem: MenuItem): HTMLLIElement {
    const listItem = document.createElement("li");
    listItem.dataset.depth = menuItem.depth.toString();
    listItem.classList.add("pageMenuMainItem");

    if (menuItem.link) {
      const link = document.createElement("a");
      link.classList.add("pageMenuMainItemLink");
      link.href = menuItem.link;
      link.textContent = menuItem.title;
      if (menuItem.active) {
        link.setAttribute("aria-current", "page");
      }

      listItem.append(link);
    } else {
      const label = document.createElement("a");
      label.classList.add("pageMenuMainItemLabel");
      label.href = "#";
      label.textContent = menuItem.title;
      label.addEventListener("click", (event) => {
        event.preventDefault();

        const button = label.nextElementSibling as HTMLAnchorElement;
        button.click();
      });

      // The button to expand the link group is used instead.
      label.setAttribute("aria-hidden", "true");

      listItem.append(label);
    }

    if (menuItem.counter > 0) {
      const counter = document.createElement("span");
      counter.classList.add("pageMenuMainItemCounter", "badge", "badgeUpdate");
      counter.setAttribute("aria-hidden", "true");
      counter.textContent = menuItem.counter.toString();

      listItem.classList.add("pageMenuMainItemOutstandingItems");
      listItem.append(counter);
    }

    if (menuItem.children.length) {
      listItem.classList.add("pageMenuMainItemExpandable");

      const menuId = DomUtil.getUniqueId();

      const button = document.createElement("a");
      button.classList.add("pageMenuMainItemToggle");
      button.tabIndex = 0;
      button.setAttribute("role", "button");
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-controls", menuId);
      button.innerHTML = '<span class="icon icon24 fa-angle-down" aria-hidden="true"></span>';

      let ariaLabel = menuItem.title;
      if (menuItem.link) {
        ariaLabel = Language.get("wcf.menu.page.button.toggle", { title: menuItem.title });
      }
      button.setAttribute("aria-label", ariaLabel);

      const list = this.buildMenuItemList(menuItem.children);
      list.id = menuId;
      list.hidden = true;

      button.addEventListener("click", (event) => {
        event.preventDefault();

        this.toggleList(button, list);
      });
      button.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();

          button.click();
        }
      });

      list.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          event.preventDefault();
          event.stopPropagation();

          this.toggleList(button, list);
        }
      });

      listItem.append(button, list);
    }

    return listItem;
  }

  private toggleList(button: HTMLAnchorElement, list: HTMLUListElement): void {
    if (list.hidden) {
      button.setAttribute("aria-expanded", "true");
      list.hidden = false;
    } else {
      button.setAttribute("aria-expanded", "false");
      list.hidden = true;

      if (document.activeElement !== button) {
        button.focus();
      }
    }
  }

  private refreshUnreadIndicator(): void {
    const hasUnreadItems = this.mainMenu.querySelector(".boxMenuLinkOutstandingItems") !== null;
    if (hasUnreadItems) {
      this.mainMenu.classList.add("pageMenuMobileButtonHasContent");
    } else {
      this.mainMenu.classList.remove("pageMenuMobileButtonHasContent");
    }
  }

  private updateOverflowIndicator(container: HTMLElement): void {
    const hasOverflow = container.clientHeight < container.scrollHeight;
    if (hasOverflow) {
      if (container.scrollTop > 0) {
        container.classList.add("pageMenuMainContainerOverflowTop");
      } else {
        container.classList.remove("pageMenuMainContainerOverflowTop");
      }

      if (container.clientHeight + container.scrollTop < container.scrollHeight) {
        container.classList.add("pageMenuMainContainerOverflowBottom");
      } else {
        container.classList.remove("pageMenuMainContainerOverflowBottom");
      }
    } else {
      container.classList.remove("pageMenuMainContainerOverflowTop", "pageMenuMainContainerOverflowBottom");
    }
  }
}

export default PageMenuMain;
