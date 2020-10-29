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
import UiCloseOverlay from "../../CloseOverlay";
import UiDropdownSimple from "../../Dropdown/Simple";
import * as UiScreen from "../../Screen";

let _isMobile = false;

let _pageHeader: HTMLElement;
let _pageHeaderContainer: HTMLElement;
let _pageHeaderPanel: HTMLElement;
let _pageHeaderSearch: HTMLElement;
let _searchInput: HTMLInputElement;
let _topMenu: HTMLElement;
let _userPanelSearchButton: HTMLElement;

/**
 * Provides the collapsible search bar.
 */
function initSearchBar(): void {
  _pageHeaderSearch = document.getElementById("pageHeaderSearch")!;
  _pageHeaderSearch.addEventListener("click", (ev) => ev.stopPropagation());

  _pageHeaderPanel = document.getElementById("pageHeaderPanel")!;
  _searchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
  _topMenu = document.getElementById("topMenu")!;

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

  UiCloseOverlay.add("WoltLabSuite/Core/Ui/Page/Header/Fixed", () => {
    if (_pageHeader.classList.contains("searchBarForceOpen")) {
      return;
    }

    closeSearchBar();
  });

  EventHandler.add("com.woltlab.wcf.MainMenuMobile", "more", (data) => {
    if (data.identifier === "com.woltlab.wcf.search") {
      data.handler.close(true);

      _userPanelSearchButton.click();
    }
  });
}

/**
 * Opens the search bar.
 */
function openSearchBar(): void {
  window.WCF.Dropdown.Interactive.Handler.closeAll();

  _pageHeader.classList.add("searchBarOpen");
  _userPanelSearchButton.parentElement!.classList.add("open");

  if (!_isMobile) {
    // calculate value for `right` on desktop
    UiAlignment.set(_pageHeaderSearch, _topMenu, {
      horizontal: "right",
    });
  }

  _pageHeaderSearch.style.setProperty("top", _pageHeaderPanel.clientHeight + "px", "");
  _searchInput.focus();

  window.setTimeout(() => {
    _searchInput.selectionStart = _searchInput.selectionEnd = _searchInput.value.length;
  }, 1);
}

/**
 * Closes the search bar.
 */
function closeSearchBar(): void {
  _pageHeader.classList.remove("searchBarOpen");
  _userPanelSearchButton.parentElement!.classList.remove("open");

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
  _pageHeader = document.getElementById("pageHeader")!;
  _pageHeaderContainer = document.getElementById("pageHeaderContainer")!;

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
