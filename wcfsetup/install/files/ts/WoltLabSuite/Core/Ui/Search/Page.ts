import * as Core from "../../Core";
import * as DomTraverse from "../../Dom/Traverse";
import DomUtil from "../../Dom/Util";
import UiDropdownSimple from "../Dropdown/Simple";
import * as UiScreen from "../Screen";
import UiSearchInput from "./Input";

function click(event: MouseEvent): void {
  event.preventDefault();

  const pageHeader = document.getElementById("pageHeader") as HTMLElement;
  pageHeader.classList.add("searchBarForceOpen");
  window.setTimeout(() => {
    pageHeader.classList.remove("searchBarForceOpen");
  }, 10);

  const target = event.currentTarget as HTMLElement;
  const objectType = target.dataset.objectType;

  const container = document.getElementById("pageHeaderSearchParameters") as HTMLElement;
  container.innerHTML = "";

  const extendedLink = target.dataset.extendedLink;
  if (extendedLink) {
    const link = document.querySelector(".pageHeaderSearchExtendedLink") as HTMLAnchorElement;
    link.href = extendedLink;
  }

  const parameters = new Map<string, string>();
  try {
    const data = JSON.parse(target.dataset.parameters || "");
    if (Core.isPlainObject(data)) {
      Object.keys(data).forEach((key) => {
        parameters.set(key, data[key]);
      });
    }
  } catch (e) {
    // Ignore JSON parsing failure.
  }

  if (objectType) {
    parameters.set("types[]", objectType);
  }

  parameters.forEach((value, key) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = key;
    input.value = value;
    container.appendChild(input);
  });

  // update label
  const inputContainer = document.getElementById("pageHeaderSearchInputContainer") as HTMLElement;
  const button = inputContainer.querySelector(
    ".pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel"
  ) as HTMLElement;
  button.textContent = target.textContent;
}

export function init(objectType: string): void {
  const searchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;

  new UiSearchInput(searchInput, {
    ajax: {
      className: "wcf\\data\\search\\keyword\\SearchKeywordAction",
    },
    autoFocus: false,
    callbackDropdownInit(dropdownMenu) {
      dropdownMenu.classList.add("dropdownMenuPageSearch");

      if (UiScreen.is("screen-lg")) {
        dropdownMenu.dataset.dropdownAlignmentHorizontal = "right";

        const minWidth = searchInput.clientWidth;
        dropdownMenu.style.setProperty("min-width", minWidth + "px", "");

        // calculate offset to ignore the width caused by the submit button
        const parent = searchInput.parentElement!;
        const offsetRight =
          DomUtil.offset(parent).left + parent.clientWidth - (DomUtil.offset(searchInput).left + minWidth);
        const offsetTop = DomUtil.styleAsInt(window.getComputedStyle(parent), "padding-bottom");
        dropdownMenu.style.setProperty(
          "transform",
          "translateX(-" + Math.ceil(offsetRight) + "px) translateY(-" + offsetTop + "px)",
          ""
        );
      }
    },
    callbackSelect() {
      setTimeout(() => {
        const form = DomTraverse.parentByTag(searchInput, "FORM") as HTMLFormElement;
        form.submit();
      }, 1);

      return true;
    },
  });

  const searchType = document.querySelector(".pageHeaderSearchType") as HTMLElement;
  const dropdownMenu = UiDropdownSimple.getDropdownMenu(DomUtil.identify(searchType))!;
  dropdownMenu.querySelectorAll("a[data-object-type]").forEach((link) => {
    link.addEventListener("click", click);
  });

  // trigger click on init
  const link = dropdownMenu.querySelector('a[data-object-type="' + objectType + '"]') as HTMLAnchorElement;
  link.click();
}
