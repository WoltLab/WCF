/**
 * Provides a specialized tab menu used for message options, integrates better into the editor.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import * as DomUtil from "WoltLabSuite/Core/Dom/Util";
import { listenToCkeditor } from "../Ckeditor/Event";

class TabMenu {
  readonly #tabs: HTMLElement[];
  readonly #tabContainers: HTMLElement[];
  #activeTabName = "";
  #wysiwygContainerId = "";
  #collapsible: boolean;

  constructor(
    tabs: HTMLElement[],
    tabContainers: HTMLElement[],
    activeTabName: string,
    wysiwygContainerId: string,
    collapsible: boolean = true,
  ) {
    this.#tabs = tabs;
    this.#tabContainers = tabContainers;
    this.#wysiwygContainerId = wysiwygContainerId;
    this.#collapsible = collapsible;

    this.#init();

    if (activeTabName) {
      this.setActiveTab(activeTabName);
    } else {
      this.#closeAllTabs();
    }
  }

  setActiveTab(tabName: string): void {
    if (this.#activeTabName === tabName) {
      if (this.#collapsible) {
        this.#activeTabName = "";
        this.#closeAllTabs();
      }

      return;
    }

    this.#closeAllTabs();

    const tab = this.#tabs.find((element) => element.dataset.name === tabName);
    if (!tab) {
      console.debug(`Unknown tab '${tabName}'.`);
      return;
    }
    const tabIndex = this.#tabs.indexOf(tab);
    tab.classList.add("active");
    tab.querySelector("button")!.setAttribute("aria-expanded", "true");
    this.#tabContainers[tabIndex].hidden = false;
    this.#tabContainers[tabIndex].classList.add("active");
    this.#activeTabName = tabName;
  }

  showTab(tabName: string): void {
    const tab = this.#tabs.find((element) => element.dataset.name === tabName);
    if (tab === undefined) {
      return;
    }

    tab.hidden = false;
  }

  hideTab(tabName: string): void {
    const tab = this.#tabs.find((element) => element.dataset.name === tabName);
    if (tab === undefined) {
      return;
    }

    tab.hidden = true;

    if (tab.classList.contains("active")) {
      this.#closeAllTabs();
    }
  }

  isHiddenTab(tabName: string): boolean {
    const tab = this.#tabs.find((element) => element.dataset.name === tabName);
    if (tab === undefined) {
      return true;
    }

    return tab.hidden !== false;
  }

  setTabCounter(tabName: string, value: number): void {
    const tab = this.#tabs.find((element) => element.dataset.name === tabName);
    if (tab === undefined) {
      throw new Error(`Unknown tab '${tabName}'.`);
    }

    let badgeUpdate = tab.querySelector(".badgeUpdate");
    if (value === 0) {
      badgeUpdate?.remove();

      return;
    }

    if (badgeUpdate === null) {
      badgeUpdate = document.createElement("span");
      badgeUpdate.classList.add("badge", "badgeUpdate");
      tab.querySelector("a, button")!.append(badgeUpdate);
    }

    badgeUpdate.textContent = value.toString();
  }

  #init(): void {
    for (let i = 0; i < this.#tabs.length; i++) {
      const tab = this.#tabs[i];
      const tabContainer = this.#tabContainers[i];

      const anchor = tab.querySelector("a");
      if (anchor) {
        const buttonReplacement = document.createElement("button");
        buttonReplacement.type = "button";
        DomUtil.replaceElement(anchor, buttonReplacement);
      }

      const button = tab.querySelector("button")!;
      button.setAttribute("aria-haspopup", "true");
      button.setAttribute("aria-expanded", "false");
      button.setAttribute("aria-controls", tabContainer.id);
      button.addEventListener("click", () => {
        this.setActiveTab(tab.dataset.name!);
      });
    }

    if (this.#wysiwygContainerId) {
      listenToCkeditor(document.getElementById(this.#wysiwygContainerId)!).reset(() => {
        this.#closeAllTabs();
      });
    }
  }

  #closeAllTabs(): void {
    for (let i = 0; i < this.#tabs.length; i++) {
      const tab = this.#tabs[i];
      tab.classList.remove("active");
      tab.querySelector("button")!.setAttribute("aria-expanded", "false");
      this.#tabContainers[i].hidden = true;
      this.#tabContainers[i].classList.remove("active");
    }
  }

  get activeTabName(): string {
    return this.#activeTabName;
  }
}

const tabMenus = new Map<string, TabMenu>();

function initTabMenu(tabMenu: HTMLElement): void {
  const tabs = tabMenu.querySelectorAll<HTMLElement>(":scope > nav [data-name]");
  const tabContainers = tabMenu.querySelectorAll<HTMLElement>(":scope > .messageTabMenuContent");

  if (!tabs.length) {
    console.debug(`No tabs found in message tab menu ('${tabMenu.dataset.wysiwygContainerId}').`);
    return;
  }
  if (tabs.length != tabContainers.length) {
    console.debug(`Amount of tabs does not equal amount of tab containers ('${tabMenu.dataset.wysiwygContainerId}').`);
    return;
  }

  let activeTabName = "";
  if (tabMenu.dataset.preselect) {
    if (tabMenu.dataset.preselect === "true") {
      activeTabName = tabs[0].dataset.name!;
    } else {
      activeTabName = tabMenu.dataset.preselect;
    }
  }

  const tabMenuObj = new TabMenu(
    Array.from(tabs),
    Array.from(tabContainers),
    activeTabName,
    tabMenu.dataset.wysiwygContainerId ?? "",
    tabMenu.dataset.collapsible !== "false",
  );

  if (tabMenu.dataset.wysiwygContainerId) {
    tabMenus.set(tabMenu.dataset.wysiwygContainerId, tabMenuObj);
  }
}

export function getTabMenu(identifier: string): TabMenu | undefined {
  setup();

  return tabMenus.get(identifier);
}

let initialized = false;

export function setup(): void {
  if (initialized) {
    return;
  }

  initialized = true;

  wheneverFirstSeen(".messageTabMenu", (tabMenu) => initTabMenu(tabMenu));
}
