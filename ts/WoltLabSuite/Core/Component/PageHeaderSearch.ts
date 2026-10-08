/**
 * Opens the search of the `system_pageHeader` in place of the main menu.
 *
 * The suggestions, the search scope and the submission are handled by `Ui/Search/Page`.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */

import UiCloseOverlay, { Origin } from "../Ui/CloseOverlay";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import DomUtil from "../Dom/Util";

let header: HTMLElement;
let search: HTMLElement;
let input: HTMLInputElement;
let button: HTMLAnchorElement;

function isOpen(): boolean {
  return header.classList.contains("searchBarOpen");
}

function open(): void {
  if (isOpen()) {
    return;
  }

  UiCloseOverlay.execute(Origin.Search);

  header.classList.add("searchBarOpen");
  button.setAttribute("aria-expanded", "true");
  button.querySelector("fa-icon")!.setIcon("xmark");

  input.focus();
  input.setSelectionRange(input.value.length, input.value.length);
}

function close(returnFocus: boolean): void {
  if (!isOpen()) {
    return;
  }

  header.classList.remove("searchBarOpen");
  button.setAttribute("aria-expanded", "false");
  button.querySelector("fa-icon")!.setIcon("magnifying-glass");

  if (returnFocus) {
    button.focus();
  }
}

export function setup(): void {
  const element = document.getElementById("pageHeaderSearch");
  if (element === null) {
    return;
  }

  search = element;
  header = document.getElementById("pageHeader")!;
  input = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
  button = document.getElementById("userPanelSearchButton") as HTMLAnchorElement;

  button.addEventListener("click", (event) => {
    event.preventDefault();

    if (isOpen()) {
      close(true);
    } else {
      open();
    }
  });

  // The link has the role of a button, which is activated by the space key too.
  button.addEventListener("keydown", (event) => {
    if (event.key === " ") {
      event.preventDefault();
      button.click();
    }
  });

  const searchType = search.querySelector<HTMLElement>(".pageHeaderSearchType")!;
  const searchTypeId = DomUtil.identify(searchType);
  const dropdownIds = [DomUtil.identify(input.parentElement!), searchTypeId];

  // The scope list is moved out of the search when it opens.
  UiDropdownSimple.getDropdownMenu(searchTypeId)?.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest("a[data-object-type]") !== null) {
      input.focus();
    }
  });

  // The suggestions and the scope list live outside the search, Escape closes them before the search.
  search.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") {
      return;
    }

    event.preventDefault();

    const openDropdownId = dropdownIds.find((id) => UiDropdownSimple.isOpen(id));
    if (openDropdownId !== undefined) {
      UiDropdownSimple.close(openDropdownId);
    } else {
      close(true);
    }
  });

  // `UiCloseOverlay` does not expose the target of a click, which is needed to spare the dropdowns of the search.
  document.addEventListener("click", (event) => {
    if (!isOpen()) {
      return;
    }

    const target = event.target;
    if (!(target instanceof Node) || !target.isConnected) {
      return;
    }

    if (search.contains(target) || button.contains(target)) {
      return;
    }

    if (dropdownIds.some((id) => UiDropdownSimple.getDropdownMenu(id)?.contains(target))) {
      return;
    }

    close(false);
  });

  // The suggestions and the scope selection are dropdowns that must not close the search.
  UiCloseOverlay.add("WoltLabSuite/Core/Component/PageHeaderSearch", (origin, identifier) => {
    if (origin === Origin.Document || origin === Origin.Search) {
      return;
    }

    if (origin === Origin.DropDown && identifier !== undefined) {
      const dropdown = document.getElementById(identifier);
      if (dropdown !== null && search.contains(dropdown)) {
        return;
      }
    }

    close(false);
  });
}
