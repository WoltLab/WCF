/**
 * Shows the control panel and the user menus (notifications, moderation, conversations, …)
 * as tabs inside the user drawer of the `system_pageHeader` on small screens.
 *
 * The tabs are built on every open from the registered user menus, their panels borrow
 * the elements that are shown as dropdowns on large screens.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */

import { getContainer, getUserMenuProviders } from "../Ui/User/Menu/Manager";
import { UserMenuProvider } from "../Ui/User/Menu/Data/Provider";
import { getElement as getControlPanelElement } from "../Ui/User/Menu/ControlPanel";
import { is as isMediaQuery } from "../Ui/Screen";
import DomUtil from "../Dom/Util";
import { open as openDrawer } from "./Drawer";

type OpenDetail = {
  tab?: string;
};

type Tab = {
  button: HTMLButtonElement;
  origin: HTMLElement;
  provider: UserMenuProvider | undefined;
};

const CONTROL_PANEL = "userMenu";

let drawer: HTMLElement;
let tabList: HTMLElement;
let panel: HTMLElement;
let tabs: Tab[] = [];
let activeTab: Tab | undefined = undefined;
let borrowed: { element: HTMLElement; parent: HTMLElement; provider: UserMenuProvider | undefined } | undefined =
  undefined;

function getProviders(): UserMenuProvider[] {
  // The notifications come first, the others keep the order of the user panel.
  return Array.from(getUserMenuProviders()).sort((a, b) => {
    if (a.getIdentifier() === "com.woltlab.wcf.notifications") {
      return -1;
    }
    if (b.getIdentifier() === "com.woltlab.wcf.notifications") {
      return 1;
    }

    return a.getPanelButton().compareDocumentPosition(b.getPanelButton()) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
  });
}

function createTab(origin: HTMLElement, provider: UserMenuProvider | undefined, icon: string): Tab {
  const link = origin.querySelector("a")!;

  const button = document.createElement("button");
  button.type = "button";
  button.id = DomUtil.getUniqueId();
  button.classList.add("userMenuDrawerTab");
  button.dataset.origin = origin.id;
  button.setAttribute("role", "tab");
  button.setAttribute("aria-controls", panel.id);
  button.setAttribute("aria-selected", "false");
  button.tabIndex = -1;
  button.innerHTML = icon;

  const label = document.createElement("span");
  label.classList.add("userMenuDrawerTabLabel");
  // `jsTooltip` moves the title into `data-tooltip`.
  label.textContent = link.dataset.tooltip || link.title;
  button.append(label);

  const tab: Tab = { button, origin, provider };
  button.addEventListener("click", () => selectTab(tab));
  button.addEventListener("keydown", (event) => keydown(event, tab));

  return tab;
}

function refreshCounters(): void {
  for (const { button, origin } of tabs) {
    const counter = origin.querySelector(".badge")?.textContent?.trim() ?? "";

    let badge = button.querySelector(".userMenuDrawerTabCount");
    if (counter === "") {
      badge?.remove();
    } else {
      if (badge === null) {
        badge = document.createElement("span");
        badge.classList.add("userMenuDrawerTabCount");
        button.append(badge);
      }
      badge.textContent = counter;
    }
  }
}

function buildTabs(): void {
  tabList.innerHTML = "";
  tabs = [];

  const controlPanel = document.getElementById(CONTROL_PANEL)!;
  tabs.push(createTab(controlPanel, undefined, '<fa-icon size="24" name="user"></fa-icon>'));

  for (const provider of getProviders()) {
    const origin = provider.getPanelButton();
    const icon = origin.querySelector("fa-icon")?.outerHTML ?? '<fa-icon size="24" name="question"></fa-icon>';

    tabs.push(createTab(origin, provider, icon));
  }

  tabList.append(...tabs.map(({ button }) => button));
  refreshCounters();
}

function returnBorrowedElement(): void {
  if (borrowed === undefined) {
    return;
  }

  if (borrowed.provider !== undefined) {
    // Also releases the focus trap of the view.
    borrowed.provider.getView().close();
  } else {
    borrowed.element.hidden = true;
  }

  borrowed.parent.append(borrowed.element);
  borrowed = undefined;
}

function selectTab(tab: Tab): void {
  if (tab === activeTab) {
    return;
  }

  returnBorrowedElement();

  for (const { button } of tabs) {
    const selected = button === tab.button;
    button.setAttribute("aria-selected", selected ? "true" : "false");
    button.tabIndex = selected ? 0 : -1;
  }
  panel.setAttribute("aria-labelledby", tab.button.id);
  activeTab = tab;

  const element = tab.provider ? tab.provider.getView().getElement() : getControlPanelElement();
  // A view that was never opened as a dropdown is not part of the document yet.
  borrowed = { element, parent: element.parentElement ?? getContainer(), provider: tab.provider };
  panel.append(element);

  if (tab.provider) {
    void tab.provider.getView().open(false);
  } else {
    element.hidden = false;
  }
}

function keydown(event: KeyboardEvent, tab: Tab): void {
  const index = tabs.indexOf(tab);
  const previous = index === 0 ? tabs.length - 1 : index - 1;
  const next = index === tabs.length - 1 ? 0 : index + 1;
  const isRtl = document.documentElement.dir === "rtl";

  let target: number;
  switch (event.key) {
    case "ArrowLeft":
      target = isRtl ? next : previous;
      break;
    case "ArrowRight":
      target = isRtl ? previous : next;
      break;
    case "Home":
      target = 0;
      break;
    case "End":
      target = tabs.length - 1;
      break;
    default:
      return;
  }

  event.preventDefault();
  tabs[target].button.focus();
}

function onOpen(detail: OpenDetail | undefined): void {
  buildTabs();

  selectTab(tabs.find(({ origin }) => origin.id === detail?.tab) ?? tabs[0]);
}

function onClose(): void {
  returnBorrowedElement();
  activeTab = undefined;
}

export function setup(): void {
  const element = document.getElementById("userMenuDrawer");
  if (element === null) {
    return;
  }

  drawer = element;
  tabList = drawer.querySelector(".userMenuDrawerTabs")!;
  panel = drawer.querySelector(".userMenuDrawerPanel")!;
  panel.id = DomUtil.getUniqueId();

  drawer.addEventListener("drawer:open", (event: CustomEvent<OpenDetail | undefined>) => onOpen(event.detail));
  drawer.addEventListener("drawer:close", () => onClose());

  // The counters of the user panel are updated by polling and push notifications.
  new MutationObserver(() => {
    if (activeTab !== undefined) {
      refreshCounters();
    }
  }).observe(document.querySelector(".userPanelItems")!, { characterData: true, childList: true, subtree: true });

  // The bell opens the notifications in the drawer instead of the dropdown on small screens.
  const notifications = document.getElementById("userNotifications");
  notifications?.addEventListener(
    "click",
    (event) => {
      if (isMediaQuery("screen-lg")) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      openDrawer(drawer.id, {
        opener: notifications.querySelector("a")!,
        detail: { tab: notifications.id } satisfies OpenDetail,
      });
    },
    { capture: true },
  );
}
