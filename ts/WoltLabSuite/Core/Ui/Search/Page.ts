import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";
import UiDropdownSimple from "../Dropdown/Simple";
import * as UiScreen from "../Screen";
import UiSearchInput from "./Input";

const parameters = new Map<string, string>();

function click(event: MouseEvent): void {
  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const objectType = target.dataset.objectType;

  const extendedLink = target.dataset.extendedLink;
  if (extendedLink) {
    const link = document.querySelector(".pageHeaderSearchExtendedLink") as HTMLAnchorElement;
    link.href = extendedLink;
  }

  parameters.clear();

  try {
    const data = JSON.parse(target.dataset.parameters || "");
    if (Core.isPlainObject(data)) {
      Object.keys(data).forEach((key) => {
        parameters.set(key, data[key] as string);
      });
    }
  } catch {
    // Ignore JSON parsing failure.
  }

  if (objectType && objectType !== "everywhere") {
    parameters.set("type", objectType);
  }

  // update label
  const inputContainer = document.getElementById("pageHeaderSearchInputContainer") as HTMLElement;
  const button = inputContainer.querySelector(
    ".pageHeaderSearchType > .button > .pageHeaderSearchTypeLabel",
  ) as HTMLElement;
  button.textContent = target.textContent;
}

export function init(objectType: string): void {
  const searchInput = document.getElementById("pageHeaderSearchInput") as HTMLInputElement;
  const form = searchInput.form!;

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
        dropdownMenu.style.setProperty("min-width", `${minWidth}px`, "");

        // calculate offset to ignore the width caused by the submit button
        const parent = searchInput.parentElement!;
        const offsetRight =
          DomUtil.offset(parent).left + parent.clientWidth - (DomUtil.offset(searchInput).left + minWidth);
        const offsetTop = DomUtil.styleAsInt(window.getComputedStyle(parent), "padding-bottom");
        dropdownMenu.style.setProperty(
          "transform",
          `translateX(-${Math.ceil(offsetRight)}px) translateY(-${offsetTop}px)`,
          "",
        );
      }
    },
    callbackSelect() {
      setTimeout(() => {
        // Do not use `form.submit()`, it does not trigger the `submit` event.
        // See https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit
        submit(form, searchInput);
      }, 1);

      return true;
    },
    suppressErrors: true,
  });

  const searchType = document.querySelector(".pageHeaderSearchType") as HTMLElement;
  const dropdownMenu = UiDropdownSimple.getDropdownMenu(DomUtil.identify(searchType))!;
  dropdownMenu.querySelectorAll("a[data-object-type]").forEach((link) => {
    link.addEventListener("click", click);
  });

  // trigger click on init
  const link = dropdownMenu.querySelector('a[data-object-type="' + objectType + '"]') as HTMLAnchorElement;
  link.click();

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    submit(form, searchInput);
  });
}

function submit(form: HTMLFormElement, input: HTMLInputElement): void {
  const url = new URL(form.action);
  url.search += url.search !== "" ? "&" : "?";
  url.search += new URLSearchParams([["q", input.value.trim()], ...Array.from(parameters)]).toString();
  window.location.href = url.toString();
}
