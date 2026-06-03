/**
 * Provides the search dropdown for the ACP.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Acp/Search", "WoltLabSuite/Core/Ui/Dropdown/Simple"], function (require, exports, tslib_1, Language_1, Search_1, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Simple_1 = tslib_1.__importDefault(Simple_1);
    const DELAY = 250;
    const TRIGGER_LENGTH = 3;
    let providerName = "";
    let lastQuery = "";
    let timer;
    let requestToken = 0;
    let itemIndex = -1;
    let navigationItems = [];
    function getSearchContainer() {
        return document.getElementById("pageHeaderSearch");
    }
    function getSearchInput() {
        return document.getElementById("pageHeaderSearchInput");
    }
    function getOrCreateList(input) {
        let list = input.parentElement.querySelector(".acpSearchDropdown");
        if (list === null) {
            list = document.createElement("ul");
            list.className = "dropdownMenu acpSearchDropdown";
            list.dataset.dropdownIgnorePageScroll = "true";
            input.insertAdjacentElement("afterend", list);
        }
        return list;
    }
    function showList(list) {
        const container = document.querySelector("#pageHeaderSearch .pageHeaderSearchInputContainer");
        if (container !== null) {
            const { bottom } = container.getBoundingClientRect();
            list.style.setProperty("top", `${Math.trunc(bottom)}px`, "important");
        }
        list.classList.add("dropdownOpen");
    }
    function clearList(list) {
        list.innerHTML = "";
        list.classList.remove("dropdownOpen");
        navigationItems = [];
        itemIndex = -1;
    }
    function renderEmpty(list) {
        clearList(list);
        const empty = document.createElement("li");
        empty.className = "dropdownText";
        empty.textContent = (0, Language_1.getPhrase)("wcf.acp.search.noResults");
        list.append(empty);
        showList(list);
    }
    function renderResults(list, groups) {
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
                    subtitle.textContent = item.subtitle;
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
    function truncateSubtitles(list) {
        list.querySelectorAll("small").forEach((element) => {
            while (element.scrollWidth > element.clientWidth) {
                element.innerText = `… ${element.innerText.substring(3)}`;
            }
        });
    }
    function highlightItem() {
        navigationItems.forEach((link, index) => {
            link.parentElement.classList.toggle("dropdownNavigationItem", index === itemIndex);
        });
    }
    function selectNext() {
        if (navigationItems.length === 0) {
            return;
        }
        itemIndex = (itemIndex + 1) % navigationItems.length;
        highlightItem();
    }
    function selectPrevious() {
        if (navigationItems.length === 0) {
            return;
        }
        itemIndex = itemIndex <= 0 ? navigationItems.length - 1 : itemIndex - 1;
        highlightItem();
    }
    function activateSelection() {
        if (itemIndex < 0 || itemIndex >= navigationItems.length) {
            return false;
        }
        window.location.href = navigationItems[itemIndex].href;
        return true;
    }
    async function performSearch(query) {
        const input = getSearchInput();
        if (input === null) {
            return;
        }
        const token = ++requestToken;
        const list = getOrCreateList(input);
        const response = await (0, Search_1.searchAcp)(query, providerName);
        if (token !== requestToken) {
            return;
        }
        if (!response.ok) {
            return;
        }
        renderResults(list, response.value.results);
    }
    function scheduleSearch(query) {
        if (timer !== undefined) {
            window.clearTimeout(timer);
        }
        timer = window.setTimeout(() => {
            timer = undefined;
            void performSearch(query);
        }, DELAY);
    }
    function onInput(event) {
        const input = event.currentTarget;
        const query = input.value.trim();
        if (query.length < TRIGGER_LENGTH) {
            requestToken++;
            if (timer !== undefined) {
                window.clearTimeout(timer);
                timer = undefined;
            }
            lastQuery = "";
            const list = input.parentElement.querySelector(".acpSearchDropdown");
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
    function onKeyDown(event) {
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
                const input = event.currentTarget;
                if (input.value.trim() === "") {
                    event.preventDefault();
                    input.blur();
                }
                return;
            }
        }
    }
    function onBlur(event) {
        const input = event.currentTarget;
        // Defer to allow click on result items to register.
        window.setTimeout(() => {
            const list = input.parentElement?.querySelector(".acpSearchDropdown");
            if (list !== null && list !== undefined) {
                clearList(list);
            }
        }, 250);
    }
    function initProviderSelection() {
        // `UiDropdownSimple` reparents the dropdown menu to a global container
        // during its lazy initialization, so the menu items are no longer
        // descendants of `#pageHeaderSearchType` at this point. Look it up
        // through the dropdown registry instead.
        const menu = Simple_1.default.getDropdownMenu("pageHeaderSearchType");
        if (menu === undefined) {
            return;
        }
        menu.addEventListener("click", (event) => {
            const target = event.target;
            const link = target?.closest("a[data-provider-name]") ?? null;
            if (link === null) {
                return;
            }
            event.preventDefault();
            const label = document.querySelector(".pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel");
            if (label !== null) {
                label.textContent = link.textContent;
            }
            const oldProvider = providerName;
            const newProvider = link.dataset.providerName === "everywhere" ? "" : link.dataset.providerName;
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
    }
    function initShortcuts(input) {
        document.addEventListener("keydown", (event) => {
            if (event.key !== "s") {
                return;
            }
            if (event.defaultPrevented || document.activeElement !== document.body) {
                return;
            }
            event.preventDefault();
            input.focus();
        }, { passive: false });
    }
    function setup() {
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
});
