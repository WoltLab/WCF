/**
 * Manages the sticky page header.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Header/Fixed
 */

import * as EventHandler from "../Event/Handler";
import * as UiAlignment from "./Alignment";
import UiCloseOverlay, { Origin } from "./CloseOverlay";
import UiDropdownSimple from "./Dropdown/Simple";
import * as UiScreen from "./Screen";
import * as Environment from "../Environment";

let _isMobile = false;

const _isAcp = document.body.classList.contains("wcfAcp");
const _pageHeader = document.getElementById("pageHeader")!;
const _pageHeaderPanel = document.getElementById("pageHeaderPanel")!;
const _pageHeaderSearch = document.getElementById("pageHeaderSearch")!;
const _pageHeaderSearchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
const _topMenu = document.getElementById("topMenu")!;
const _userPanelSearchButton = document.getElementById("userPanelSearchButton");

/**
 * Provides the collapsible search bar.
 */
function initSearchBar(): void {
  _pageHeaderSearch.addEventListener("click", (event) => event.stopPropagation());

  _userPanelSearchButton?.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (_pageHeader.classList.contains("searchBarOpen")) {
      closeSearchBar();
    } else {
      openSearchBar();
    }
  });

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/Search", (origin, identifier) => {
    if (origin === Origin.DropDown) {
      const button = document.getElementById("pageHeaderSearchTypeSelect");
      if (button && button.dataset.target === identifier) {
        return;
      }
    }

    if (_pageHeader.classList.contains("searchBarForceOpen")) {
      return;
    }

    closeSearchBar();
  });
}

function initSearchButton(): void {
  let scrollTop: number | null = null;
  const searchButton = document.getElementById("pageHeaderSearchMobile")!;
  searchButton.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (searchButton.getAttribute("aria-expanded") === "true") {
      closeSearch(_pageHeaderSearch, scrollTop);
      closeSearchBar();

      searchButton.setAttribute("aria-expanded", "false");
    } else {
      if (Environment.platform() === "ios") {
        scrollTop = document.body.scrollTop;
        UiScreen.scrollDisable();
      }

      openSearchBar();

      const pageHeader = document.getElementById("pageHeader")!;
      _pageHeaderSearch.style.setProperty("top", `${pageHeader.offsetHeight}px`, "");
      _pageHeaderSearch.classList.add("open");
      _pageHeaderSearchInput.focus();

      if (Environment.platform() === "ios") {
        document.body.scrollTop = 0;
      }

      searchButton.setAttribute("aria-expanded", "true");
    }
  });

  _pageHeaderSearch.addEventListener("click", (event) => {
    event.stopPropagation();

    if (event.target === _pageHeaderSearch) {
      event.preventDefault();

      closeSearch(_pageHeaderSearch, scrollTop);
      closeSearchBar();

      searchButton.setAttribute("aria-expanded", "false");
    }
  });

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/MobileSearch", (origin, identifier) => {
    if (!_isAcp && origin === Origin.DropDown) {
      const button = document.getElementById("pageHeaderSearchTypeSelect")!;
      if (button.dataset.target === identifier) {
        return;
      }
    }

    closeSearch(_pageHeaderSearch, scrollTop);
    if (!_isAcp) {
      closeSearchBar();
    }

    searchButton.setAttribute("aria-expanded", "false");
  });
}

function closeSearch(searchBar: HTMLElement, scrollTop: number | null): void {
  if (searchBar) {
    searchBar.classList.remove("open");
  }

  if (Environment.platform() === "ios") {
    UiScreen.scrollEnable();

    if (scrollTop !== null) {
      document.body.scrollTop = scrollTop;
      scrollTop = null;
    }
  }
}

function openSearchBar(): void {
  UiCloseOverlay.execute();

  _pageHeader.classList.add("searchBarOpen");
  _userPanelSearchButton?.parentElement!.classList.add("open");

  if (!_isMobile) {
    // calculate value for `right` on desktop
    UiAlignment.set(_pageHeaderSearch, _topMenu, {
      horizontal: "right",
    });
  }

  _pageHeaderSearch.style.setProperty("top", `${_pageHeaderPanel.clientHeight}px`, "");
  _pageHeaderSearchInput.focus();

  window.setTimeout(() => {
    _pageHeaderSearchInput.selectionStart = _pageHeaderSearchInput.selectionEnd = _pageHeaderSearchInput.value.length;
  }, 1);
}

function closeSearchBar(): void {
  _pageHeader.classList.remove("searchBarOpen");
  _userPanelSearchButton?.parentElement!.classList.remove("open");

  ["bottom", "left", "right", "top"].forEach((propertyName) => {
    _pageHeaderSearch.style.removeProperty(propertyName);
  });

  _pageHeaderSearchInput.blur();

  // close the scope selection
  const scope = _pageHeaderSearch.querySelector(".pageHeaderSearchType")!;
  UiDropdownSimple.close(scope.id);
}

/**
 * Initializes the sticky page header handler.
 */
export function init(): void {
  // The search bar is unavailable during WCFSetup or the login.
  if (_isAcp && _pageHeaderSearch === null) {
    return;
  }

  initSearchBar();
  initSearchButton();

  UiScreen.on("screen-md-down", {
    match() {
      _isMobile = true;
    },
    unmatch() {
      _isMobile = false;
    },
    setup() {
      _isMobile = true;
    },
  });

  EventHandler.add("com.woltlab.wcf.Search", "close", closeSearchBar);
}
