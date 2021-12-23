/**
 * Provides the touch-friendly user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/User
 */

import PageMenuContainer, { Orientation } from "./Container";
import { PageMenuProvider } from "./Provider";
import * as Language from "../../../Language";
import { getUserMenuProviders } from "../../User/Menu/Manager";
import { UserMenuProvider } from "../../User/Menu/Data/Provider";
import DomUtil from "../../../Dom/Util";
import { getElement as getControlPanelElement } from "../../User/Menu/ControlPanel";
import * as EventHandler from "../../../Event/Handler";

type CallbackOpen = (event: MouseEvent) => void;

type Tab = HTMLAnchorElement;
type TabPanel = HTMLElement;
type TabComponents = [Tab, TabPanel];

type TabData = {
  icon: string;
  label: string;
  origin: string;
};

type LegacyDropdownInteractive = {
  getContainer(): JQuery;
};
type LegacyUserPanelApi = {
  close(): void;
  getDropdown(): LegacyDropdownInteractive;
  open(): void;
  toggle(): void;
};

export class PageMenuUser implements PageMenuProvider {
  private activeTab?: Tab = undefined;
  private readonly callbackOpen: CallbackOpen;
  private readonly container: PageMenuContainer;
  private readonly legacyUserPanels = new Map<Tab, LegacyUserPanelApi>();
  private readonly userMenuProviders = new Map<Tab, UserMenuProvider>();
  private readonly tabPanels = new Map<Tab, HTMLElement>();
  private readonly tabs: Tab[] = [];
  private readonly userMenu: HTMLElement;

  constructor() {
    this.userMenu = document.querySelector(".userPanel")!;

    this.container = new PageMenuContainer(this, Orientation.Right);

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

    this.refreshUnreadIndicator();
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

  sleep(): void {
    if (this.activeTab) {
      this.closeTab(this.activeTab);
    }
  }

  wakeup(): void {
    if (this.activeTab) {
      // The UI elements in the tab panel are shared and can appear in a different
      // context. The element might have been moved elsewhere while the menu was
      // closed.
      this.openTab(this.activeTab);
    } else {
      this.openNotifications();
    }

    this.refreshTabUnreadIndicators();
    this.refreshUnreadIndicator();
  }

  private openNotifications(): void {
    const notifications = this.tabs.find((element) => element.dataset.origin === "userNotifications");
    if (!notifications) {
      throw new Error("Unable to find the notifications tab.");
    }

    this.openTab(notifications);
  }

  private openTab(tab: Tab): void {
    this.closeActiveTab();

    tab.setAttribute("aria-selected", "true");
    tab.tabIndex = 0;

    const tabPanel = this.tabPanels.get(tab)!;
    tabPanel.hidden = false;

    if (document.activeElement !== tab) {
      tab.focus();
    }

    this.attachViewToPanel(tab);

    this.activeTab = tab;
  }

  private closeActiveTab(): void {
    if (!this.activeTab) {
      return;
    }

    this.closeTab(this.activeTab);

    this.activeTab = undefined;
  }

  private closeTab(tab: Tab): void {
    tab.setAttribute("aria-selected", "false");
    tab.tabIndex = -1;

    const tabPanel = this.tabPanels.get(tab)!;
    tabPanel.hidden = true;

    const legacyPanel = this.legacyUserPanels.get(tab);
    if (legacyPanel) {
      legacyPanel.close();
    }

    this.refreshTabUnreadIndicators();
  }

  private attachViewToPanel(tab: Tab): void {
    const origin = tab.dataset.origin!;
    const tabPanel = this.tabPanels.get(tab)!;

    if (origin === "userMenu") {
      const element = getControlPanelElement();
      element.hidden = false;

      if (tabPanel.childElementCount === 0) {
        tabPanel.append(element);
      }
    } else {
      if (tabPanel.childElementCount === 0) {
        const provider = this.userMenuProviders.get(tab);
        if (provider) {
          const view = provider.getView();
          tabPanel.append(view.getElement());
          void view.open();
        } else {
          const legacyPanel = this.legacyUserPanels.get(tab)!;
          legacyPanel.open();

          const { top } = tabPanel.getBoundingClientRect();

          const container = legacyPanel.getDropdown().getContainer()[0];
          container.style.setProperty("--offset-top", `${top}px`);
        }
      }
    }
  }

  private keydown(event: KeyboardEvent): void {
    const tab = event.currentTarget as Tab;

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

  private buildTabMenu(): HTMLElement {
    const tabContainer = document.createElement("div");
    tabContainer.classList.add("pageMenuUserTabContainer");

    const tabList = document.createElement("div");
    tabList.classList.add("pageMenuUserTabList");
    tabList.setAttribute("role", "tablist");
    tabList.setAttribute("aria-label", Language.get("TODO"));
    tabContainer.append(tabList);

    this.buildControlPanelTab(tabList, tabContainer);

    getUserMenuProviders().forEach((provider) => {
      const [tab, tabPanel] = this.buildTab(provider);

      tabList.append(tab);
      tabContainer.append(tabPanel);

      this.tabs.push(tab);
      this.tabPanels.set(tab, tabPanel);
      this.userMenuProviders.set(tab, provider);
    });

    this.buildLegacyTabs(tabList, tabContainer);

    return tabContainer;
  }

  private buildTab(provider: UserMenuProvider): TabComponents {
    const panelButton = provider.getPanelButton();
    const button = panelButton.querySelector("a")!;

    const data: TabData = {
      icon: button.querySelector(".icon")!.outerHTML,
      label: button.dataset.title || button.title,
      origin: panelButton.id,
    };

    return this.buildTabComponents(data);
  }

  private buildControlPanelTab(tabList: HTMLElement, tabContainer: HTMLElement): void {
    const panel = document.getElementById("topMenu")!;
    const userMenu = document.getElementById("userMenu")!;
    const userMenuButton = userMenu.querySelector("a")!;

    const data: TabData = {
      icon: panel.querySelector(".userPanelAvatar .userAvatarImage")!.outerHTML,
      label: userMenuButton.dataset.title || userMenuButton.title,
      origin: userMenu.id,
    };

    const [tab, tabPanel] = this.buildTabComponents(data);

    tabList.append(tab);
    tabContainer.append(tabPanel);

    this.tabs.push(tab);
    this.tabPanels.set(tab, tabPanel);
  }

  private buildLegacyTabs(tabList: HTMLElement, tabContainer: HTMLElement): void {
    const userPanelItems = document.querySelector(".userPanelItems") as HTMLUListElement;

    type LegacyPanel = {
      api: LegacyUserPanelApi;
      element: HTMLElement;
    };
    const legacyPanelData: { panels: LegacyPanel[] } = {
      panels: [],
    };
    EventHandler.fire("com.woltlab.wcf.pageMenu", "legacyMenu", legacyPanelData);

    Array.from(userPanelItems.children)
      .filter((listItem: HTMLLIElement) => {
        const element = legacyPanelData.panels.find((panel) => panel.element === listItem);

        return element !== undefined;
      })
      .map((listItem: HTMLLIElement) => {
        const button = listItem.querySelector("a")!;

        return {
          icon: button.querySelector(".icon")!.outerHTML,
          label: button.dataset.title || button.title,
          origin: listItem.id,
        } as TabData;
      })
      .forEach((data: TabData) => {
        const [tab, tabPanel] = this.buildTabComponents(data);

        tabList.append(tab);
        tabContainer.append(tabPanel);

        this.tabs.push(tab);
        this.tabPanels.set(tab, tabPanel);

        const legacyPanel = legacyPanelData.panels.find((panel) => panel.element.id === data.origin)!;
        this.legacyUserPanels.set(tab, legacyPanel.api);
      });
  }

  private buildTabComponents(data: TabData): TabComponents {
    const tabId = DomUtil.getUniqueId();
    const panelId = DomUtil.getUniqueId();

    const tab = document.createElement("a");
    tab.classList.add("pageMenuUserTab");
    tab.dataset.hasUnreadContent = "false";
    tab.dataset.origin = data.origin;
    tab.id = tabId;
    tab.setAttribute("aria-controls", panelId);
    tab.setAttribute("aria-selected", "false");
    tab.setAttribute("role", "tab");
    tab.tabIndex = -1;

    tab.setAttribute("aria-label", data.label);
    tab.innerHTML = data.icon;

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

  private refreshUnreadIndicator(): void {
    const hasUnreadItems = this.userMenu.querySelector(".badge.badgeUpdate") !== null;
    if (hasUnreadItems) {
      this.userMenu.classList.add("pageMenuMobileButtonHasContent");
    } else {
      this.userMenu.classList.remove("pageMenuMobileButtonHasContent");
    }
  }

  private refreshTabUnreadIndicators(): void {
    this.userMenuProviders.forEach((provider, tab) => {
      if (provider.hasUnreadContent()) {
        tab.dataset.hasUnreadContent = "true";
      } else {
        tab.dataset.hasUnreadContent = "false";
      }
    });
  }
}

export function hasValidUserMenu(): boolean {
  return true;
}

export default PageMenuUser;
