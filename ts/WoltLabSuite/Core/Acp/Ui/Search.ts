/**
 * Provides the search dropdown for the ACP.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { getPhrase } from "WoltLabSuite/Core/Language";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import { searchAcp, type AcpSearchResultGroup } from "WoltLabSuite/Core/Api/Acp/Search";

const DELAY = 250;
const TRIGGER_LENGTH = 3;

let providerName = "";
let lastQuery = "";
let timer: number | undefined;
let requestToken = 0;
let itemIndex = -1;
let navigationItems: HTMLAnchorElement[] = [];

function getSearchContainer(): HTMLElement | null {
  return document.getElementById("pageHeaderSearch");
}

function getSearchInput(): HTMLInputElement | null {
  return document.getElementById("pageHeaderSearchInput") as HTMLInputElement | null;
}

function getOrCreateList(input: HTMLInputElement): HTMLUListElement {
  let list = input.parentElement!.querySelector<HTMLUListElement>(".acpSearchDropdown");
  if (list === null) {
    list = document.createElement("ul");
    list.className = "dropdownMenu acpSearchDropdown";
    list.dataset.dropdownIgnorePageScroll = "true";
    input.insertAdjacentElement("afterend", list);
  }

  return list;
}

function showList(list: HTMLUListElement): void {
  const container = document.querySelector<HTMLElement>("#pageHeaderSearch .pageHeaderSearchInputContainer");
  if (container !== null) {
    const { bottom } = container.getBoundingClientRect();
    list.style.setProperty("top", `${Math.trunc(bottom)}px`, "important");
  }

  list.classList.add("dropdownOpen");
}

function clearList(list: HTMLUListElement): void {
  list.innerHTML = "";
  list.classList.remove("dropdownOpen");
  navigationItems = [];
  itemIndex = -1;
}

function renderEmpty(list: HTMLUListElement): void {
  clearList(list);

  const empty = document.createElement("li");
  empty.className = "dropdownText";
  empty.textContent = getPhrase("wcf.acp.search.noResults");
  list.append(empty);

  showList(list);
}

function renderResults(list: HTMLUListElement, groups: AcpSearchResultGroup[]): void {
  clearList(list);

  if (groups.length === 0) {
    renderEmpty(list);
    return;
  }

  groups.forEach((group, index) => {
    if (index > 0) {
      const divider = document.createElement("li");
      divider.className = "dropdownDivider";
      list.append(divider);
    }

    const caption = document.createElement("li");
    caption.className = "dropdownText";
    caption.textContent = group.title;
    list.append(caption);

    for (const item of group.items) {
      const li = document.createElement("li");
      const link = document.createElement("a");
      link.href = item.link;

      const title = document.createElement("span");
      title.textContent = item.title;
      link.append(title);

      if (item.subtitle) {
        const subtitle = document.createElement("small");
        subtitle.innerHTML = escapeHTML(item.subtitle);
        link.append(subtitle);
      }

      li.append(link);
      list.append(li);
      navigationItems.push(link);
    }
  });

  truncateSubtitles(list);
  showList(list);
}

function truncateSubtitles(list: HTMLUListElement): void {
  list.querySelectorAll<HTMLElement>("small").forEach((element) => {
    while (element.scrollWidth > element.clientWidth) {
      element.innerText = `… ${element.innerText.substring(3)}`;
    }
  });
}

function highlightItem(): void {
  navigationItems.forEach((link, index) => {
    link.parentElement!.classList.toggle("dropdownNavigationItem", index === itemIndex);
  });
}

function selectNext(): void {
  if (navigationItems.length === 0) {
    return;
  }

  itemIndex = (itemIndex + 1) % navigationItems.length;
  highlightItem();
}

function selectPrevious(): void {
  if (navigationItems.length === 0) {
    return;
  }

  itemIndex = itemIndex <= 0 ? navigationItems.length - 1 : itemIndex - 1;
  highlightItem();
}

function activateSelection(): boolean {
  if (itemIndex < 0 || itemIndex >= navigationItems.length) {
    return false;
  }

  window.location.href = navigationItems[itemIndex].href;
  return true;
}

async function performSearch(query: string): Promise<void> {
  const input = getSearchInput();
  if (input === null) {
    return;
  }

  const token = ++requestToken;
  const list = getOrCreateList(input);

  const response = await searchAcp(query, providerName);
  if (token !== requestToken) {
    return;
  }
  if (!response.ok) {
    return;
  }

  renderResults(list, response.value.results);
}

function scheduleSearch(query: string): void {
  if (timer !== undefined) {
    window.clearTimeout(timer);
  }

  timer = window.setTimeout(() => {
    timer = undefined;
    void performSearch(query);
  }, DELAY);
}

function onInput(event: Event): void {
  const input = event.currentTarget as HTMLInputElement;
  const query = input.value.trim();

  if (query.length < TRIGGER_LENGTH) {
    requestToken++;
    if (timer !== undefined) {
      window.clearTimeout(timer);
      timer = undefined;
    }
    lastQuery = "";

    const list = input.parentElement!.querySelector<HTMLUListElement>(".acpSearchDropdown");
    if (list !== null) {
      clearList(list);
    }

    return;
  }

  if (query === lastQuery) {
    return;
  }

  lastQuery = query;
  scheduleSearch(query);
}

function onKeyDown(event: KeyboardEvent): void {
  switch (event.key) {
    case "ArrowDown":
      event.preventDefault();
      selectNext();
      return;
    case "ArrowUp":
      event.preventDefault();
      selectPrevious();
      return;
    case "Enter":
      if (activateSelection()) {
        event.preventDefault();
      }
      return;
    case "Escape": {
      const input = event.currentTarget as HTMLInputElement;
      if (input.value.trim() === "") {
        event.preventDefault();
        input.blur();
      }
      return;
    }
  }
}

function onBlur(event: FocusEvent): void {
  const input = event.currentTarget as HTMLInputElement;
  // Defer to allow click on result items to register.
  window.setTimeout(() => {
    const list = input.parentElement?.querySelector<HTMLUListElement>(".acpSearchDropdown");
    if (list !== null && list !== undefined) {
      clearList(list);
    }
  }, 250);
}

function initProviderSelection(): void {
  const dropdown = document.getElementById("pageHeaderSearchType");
  if (dropdown === null) {
    return;
  }

  dropdown.querySelectorAll<HTMLAnchorElement>("a[data-provider-name]").forEach((link) => {
    link.addEventListener("click", (event) => {
      event.preventDefault();

      const label = document.querySelector<HTMLElement>(".pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel");
      if (label !== null) {
        label.textContent = link.textContent;
      }

      const oldProvider = providerName;
      const newProvider = link.dataset.providerName === "everywhere" ? "" : link.dataset.providerName!;
      providerName = newProvider;

      if (oldProvider !== newProvider) {
        const input = getSearchInput();
        if (input !== null) {
          const query = input.value.trim();
          if (query.length >= TRIGGER_LENGTH) {
            lastQuery = query;
            void performSearch(query);
          }
        }
      }
    });
  });
}

function initShortcuts(input: HTMLInputElement): void {
  document.addEventListener(
    "keydown",
    (event) => {
      if (event.key !== "s") {
        return;
      }
      if (event.defaultPrevented || document.activeElement !== document.body) {
        return;
      }

      event.preventDefault();
      input.focus();
    },
    { passive: false },
  );
}

export function setup(): void {
  const container = getSearchContainer();
  if (container === null) {
    return;
  }

  const input = getSearchInput();
  if (input === null) {
    return;
  }

  const form = container.querySelector("form");
  form?.addEventListener("submit", (event) => event.preventDefault());

  input.autocomplete = "off";
  input.addEventListener("input", onInput);
  input.addEventListener("keydown", onKeyDown);
  input.addEventListener("blur", onBlur);

  initProviderSelection();
  initShortcuts(input);
}
