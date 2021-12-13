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

type TabList = HTMLDivElement;
type TabPanelContainer = HTMLDivElement;
type TabMenu = [TabList, TabPanelContainer];

export class PageMenuUser implements PageMenuProvider {
  private readonly callbackOpen: CallbackOpen;
  private readonly container: PageMenuContainer;
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
    fragment.append(...this.buildTabMenu());

    return fragment;
  }

  getMenuButton(): HTMLElement {
    return this.userMenu;
  }

  private buildTabMenu(): TabMenu {
    const tabList = document.createElement("div");
    tabList.classList.add("pageMenuUserTabList");
    tabList.setAttribute("role", "tablist");
    tabList.setAttribute("aria-label", Language.get("TODO"));

    const tabPanelContainer = document.createElement("div");

    // TODO: Inject the control panel first.

    getUserMenuProviders().forEach((provider) => {
      const [tab, tabPanel] = this.buildTab(provider);

      tabList.append(tab);
      tabPanelContainer.append(tabPanel);
    });

    // TODO: Inject legacy user panel items.

    return [tabList, tabPanelContainer];
  }

  private buildTab(provider: UserMenuProvider): TabComponents {
    const tabId = DomUtil.getUniqueId();
    const panelId = DomUtil.getUniqueId();

    const tab = document.createElement("a");
    tab.classList.add("pageMenuUserTab");
    tab.id = tabId;
    tab.setAttribute("aria-controls", panelId);
    tab.setAttribute("aria-selected", "false");
    tab.setAttribute("role", "tab");
    tab.tabIndex = -1;

    const button = provider.getPanelButton().querySelector("a")!;
    tab.setAttribute("aria-label", button.dataset.title || button.title);
    tab.innerHTML = button.querySelector(".icon")!.outerHTML;

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
