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
import { identify } from "../Dom/Util";

let _isMobile = false;
let _scrollTop: number | undefined = undefined;

const _isAcp = document.body.classList.contains("wcfAcp");
const _pageHeader = document.getElementById("pageHeader")!;
const _pageHeaderPanel = document.getElementById("pageHeaderPanel")!;
const _pageHeaderSearch = document.getElementById("pageHeaderSearch")!;
let _pageHeaderSearchMobile: HTMLElement | undefined = undefined;
const _pageHeaderSearchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
const _topMenu = document.getElementById("topMenu")!;
const _userPanelSearchButton = document.getElementById("userPanelSearchButton");

/**
 * Provides the collapsible search bar.
 */
function initSearchBar(): void {
  _pageHeaderSearch.addEventListener("click", (event) => event.stopPropagation());

  const searchType = document.querySelector(".pageHeaderSearchType") as HTMLElement;
  const dropdownMenuId = identify(searchType);
  const dropdownMenu = UiDropdownSimple.getDropdownMenu(dropdownMenuId)!;
  dropdownMenu.addEventListener("click", (event) => {
    // This prevents triggering the `UiCloseOverlay`.
    event.stopPropagation();

    UiDropdownSimple.close(dropdownMenuId);
  });

  _userPanelSearchButton?.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (_pageHeader.classList.contains("searchBarOpen")) {
      closeSearch();
    } else {
      openSearch();
    }
  });

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/Search", (origin, identifier) => {
    if (origin === Origin.DropDown) {
      const button = document.getElementById("pageHeaderSearchTypeSelect")!;
      if (button.dataset.target === identifier) {
        return;
      }
    }

    closeSearch();

    _pageHeaderSearchMobile?.setAttribute("aria-expanded", "false");
  });
}

function initMobileSearch(): void {
  const searchButton = document.getElementById("pageHeaderSearchMobile")!;
  _pageHeaderSearchMobile = searchButton;

  searchButton.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (searchButton.getAttribute("aria-expanded") === "true") {
      closeSearch();

      searchButton.setAttribute("aria-expanded", "false");
    } else {
      if (Environment.platform() === "ios") {
        _scrollTop = document.body.scrollTop;
        UiScreen.scrollDisable();
      }

      openSearch();

      _pageHeaderSearch.style.setProperty("top", `${_pageHeader.offsetHeight}px`, "");
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

      closeSearch();

      searchButton.setAttribute("aria-expanded", "false");
    }
  });
}

function openSearch(): void {
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
    // Places the caret at the end of the search input.
    const length = _pageHeaderSearchInput.value.length;
    _pageHeaderSearchInput.selectionStart = length;
    _pageHeaderSearchInput.selectionEnd = length;
  }, 1);
}

function closeSearch(): void {
  _pageHeader.classList.remove("searchBarOpen");
  _pageHeaderSearch.classList.remove("open");
  _userPanelSearchButton?.parentElement!.classList.remove("open");

  ["bottom", "left", "right", "top"].forEach((propertyName) => {
    _pageHeaderSearch.style.removeProperty(propertyName);
  });

  if (Environment.platform() === "ios") {
    UiScreen.scrollEnable();

    if (_scrollTop !== undefined) {
      document.body.scrollTop = _scrollTop;
      _scrollTop = undefined;
    }
  }

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

  UiScreen.on("screen-md-down", {
    match() {
      _isMobile = true;
    },
    unmatch() {
      _isMobile = false;
      _scrollTop = undefined;
    },
    setup() {
      _isMobile = true;

      initMobileSearch();
    },
  });

  EventHandler.add("com.woltlab.wcf.Search", "close", closeSearch);
}
