/**
 * Manages the sticky page header.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Header/Fixed
 */

import * as EventHandler from "../../../Event/Handler";
import * as UiAlignment from "../../Alignment";
import UiCloseOverlay, { Origin } from "../../CloseOverlay";
import UiDropdownSimple from "../../Dropdown/Simple";
import * as UiScreen from "../../Screen";

let _isMobile = false;

const _pageHeader = document.getElementById("pageHeader")!;
const _pageHeaderPanel = document.getElementById("pageHeaderPanel")!;
const _pageHeaderSearch = document.getElementById("pageHeaderSearch")!;
const _searchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
const _topMenu = document.getElementById("topMenu")!;
let _userPanelSearchButton: HTMLElement | null = null;

/**
 * Provides the collapsible search bar.
 */
function initSearchBar(): void {
  _pageHeaderSearch.addEventListener("click", (ev) => ev.stopPropagation());

  _userPanelSearchButton = document.getElementById("userPanelSearchButton")!;
  _userPanelSearchButton.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (_pageHeader.classList.contains("searchBarOpen")) {
      closeSearchBar();
    } else {
      openSearchBar();
    }
  });

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/Page/Header/Fixed", (origin, identifier) => {
    if (origin === Origin.DropDown) {
      const button = document.getElementById("pageHeaderSearchTypeSelect")!;
      if (button.dataset.target === identifier) {
        return;
      }
    }

    if (_pageHeader.classList.contains("searchBarForceOpen")) {
      return;
    }

    closeSearchBar();
  });
}

/**
 * Opens the search bar.
 */
export function openSearchBar(): void {
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
  _searchInput.focus();

  window.setTimeout(() => {
    _searchInput.selectionStart = _searchInput.selectionEnd = _searchInput.value.length;
  }, 1);
}

/**
 * Closes the search bar.
 */
export function closeSearchBar(): void {
  _pageHeader.classList.remove("searchBarOpen");
  _userPanelSearchButton?.parentElement!.classList.remove("open");

  ["bottom", "left", "right", "top"].forEach((propertyName) => {
    _pageHeaderSearch.style.removeProperty(propertyName);
  });

  _searchInput.blur();

  // close the scope selection
  const scope = _pageHeaderSearch.querySelector(".pageHeaderSearchType")!;
  UiDropdownSimple.close(scope.id);
}

/**
 * Initializes the sticky page header handler.
 */
export function init(): void {
  initSearchBar();

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
