/**
 * Provides the touch-friendly user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/User
 */

import PageMenuContainer from "./Container";
import { PageMenuProvider } from "./Provider";
import * as Language from "../../../Language";
import { getUserMenuProviders } from "../../User/Menu/Manager";
import { UserMenuProvider } from "../../User/Menu/Data/Provider";
import DomUtil from "../../../Dom/Util";

type CallbackOpen = (event: MouseEvent) => void;

type Tab = HTMLAnchorElement;
type TabPanel = HTMLDivElement;
type TabComponents = [Tab, TabPanel];

export class PageMenuUser implements PageMenuProvider {
  private readonly callbackOpen: CallbackOpen;
  private readonly container: PageMenuContainer;
  private readonly userMenuProviders = new Map<HTMLAnchorElement, UserMenuProvider>();
  private readonly tabPanels = new Map<HTMLAnchorElement, HTMLDivElement>();
  private readonly tabs: HTMLAnchorElement[] = [];
  private readonly userMenu: HTMLElement;

  constructor() {
    this.userMenu = document.querySelector(".userPanel")!;

    this.container = new PageMenuContainer(this);

    this.callbackOpen = (event) => {
      event.preventDefault();
      event.stopPropagation();

      this.container.toggle();
    };
  }

  enable(): void {
    this.userMenu.setAttribute("aria-expanded", "false");
    this.userMenu.setAttribute("role", "button");
    this.userMenu.tabIndex = 0;
    this.userMenu.addEventListener("click", this.callbackOpen);
  }

  disable(): void {
    this.container.close();

    this.userMenu.removeAttribute("aria-expanded");
    this.userMenu.removeAttribute("role");
    this.userMenu.removeAttribute("tabindex");
    this.userMenu.removeEventListener("click", this.callbackOpen);
  }

  getContent(): DocumentFragment {
    const fragment = document.createDocumentFragment();
    fragment.append(this.buildTabMenu());

    return fragment;
  }

  getMenuButton(): HTMLElement {
    return this.userMenu;
  }

  refresh(): void {
    const activeTab = this.tabs.find((element) => element.getAttribute("aria-selected") === "true");
    if (activeTab === undefined) {
      this.openNotifications();
    } else {
      // The UI elements in the tab panel are shared and can appear in a different
      // context. The element might have been moved elsewhere while the menu was
      // closed.
      this.attachViewToPanel(activeTab);
    }
  }

  private openNotifications(): void {
    const notifications = this.tabs.find((element) => element.dataset.origin === "userNotifications");
    if (!notifications) {
      throw new Error("Unable to find the notifications tab.");
    }

    this.openTab(notifications);
  }

  private openTab(tab: HTMLAnchorElement): void {
    if (tab.getAttribute("aria-selected") === "true") {
      return;
    }

    const activeTab = this.tabs.find((element) => element.getAttribute("aria-selected") === "true");
    if (activeTab) {
      activeTab.setAttribute("aria-selected", "false");
      activeTab.tabIndex = -1;

      const activePanel = this.tabPanels.get(activeTab)!;
      activePanel.hidden = true;
    }

    tab.setAttribute("aria-selected", "true");
    tab.tabIndex = 0;

    const tabPanel = this.tabPanels.get(tab)!;
    tabPanel.hidden = false;

    if (document.activeElement !== tab) {
      tab.focus();
    }

    this.attachViewToPanel(tab);
  }

  private attachViewToPanel(tab: HTMLAnchorElement): void {
    const tabPanel = this.tabPanels.get(tab)!;
    if (tabPanel.childElementCount === 0) {
      const provider = this.userMenuProviders.get(tab);
      if (provider) {
        const view = provider.getView();
        tabPanel.append(view.getElement());
        void view.open();
      } else {
        throw new Error("TODO: Legacy user panel menus");
      }
    }
  }

  private keydown(event: KeyboardEvent): void {
    const tab = event.currentTarget as HTMLAnchorElement;

    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();

      this.openTab(tab);

      return;
    }

    const navigationKeyEvents = ["ArrowLeft", "ArrowRight", "End", "Home"];
    if (!navigationKeyEvents.includes(event.key)) {
      return;
    }

    event.preventDefault();

    const currentIndex = this.tabs.indexOf(tab);
    const lastIndex = this.tabs.length - 1;

    let index: number;
    if (event.key === "ArrowLeft") {
      if (currentIndex === 0) {
        index = lastIndex;
      } else {
        index = currentIndex - 1;
      }
    } else if (event.key === "ArrowRight") {
      if (currentIndex === lastIndex) {
        index = 0;
      } else {
        index = currentIndex + 1;
      }
    } else if (event.key === "End") {
      index = lastIndex;
    } else {
      index = 0;
    }

    this.tabs[index].focus();
  }

  private buildTabMenu(): HTMLDivElement {
    const tabContainer = document.createElement("div");
    tabContainer.classList.add("pageMenuUserTabContainer");

    const tabList = document.createElement("div");
    tabList.classList.add("pageMenuUserTabList");
    tabList.setAttribute("role", "tablist");
    tabList.setAttribute("aria-label", Language.get("TODO"));
    tabContainer.append(tabList);

    // TODO: Inject the control panel first.

    getUserMenuProviders().forEach((provider) => {
      const [tab, tabPanel] = this.buildTab(provider);

      tabList.append(tab);
      tabContainer.append(tabPanel);

      this.tabs.push(tab);
      this.tabPanels.set(tab, tabPanel);
      this.userMenuProviders.set(tab, provider);
    });

    // TODO: Inject legacy user panel items.

    return tabContainer;
  }

  private buildTab(provider: UserMenuProvider): TabComponents {
    const tabId = DomUtil.getUniqueId();
    const panelId = DomUtil.getUniqueId();

    const tab = document.createElement("a");
    tab.classList.add("pageMenuUserTab");
    tab.dataset.origin = provider.getPanelButton().id;
    tab.id = tabId;
    tab.setAttribute("aria-controls", panelId);
    tab.setAttribute("aria-selected", "false");
    tab.setAttribute("role", "tab");
    tab.tabIndex = -1;

    const button = provider.getPanelButton().querySelector("a")!;
    tab.setAttribute("aria-label", button.dataset.title || button.title);
    tab.innerHTML = button.querySelector(".icon")!.outerHTML;

    tab.addEventListener("click", (event) => {
      event.preventDefault();

      this.openTab(tab);
    });
    tab.addEventListener("keydown", (event) => this.keydown(event));

    const panel = document.createElement("div");
    panel.classList.add("pageMenuUserTabPanel");
    panel.id = panelId;
    panel.hidden = true;
    panel.setAttribute("aria-labelledby", tabId);
    panel.setAttribute("role", "tabpanel");
    panel.tabIndex = 0;

    return [tab, panel];
  }
}

export function hasValidUserMenu(): boolean {
  return true;
}

export default PageMenuUser;
