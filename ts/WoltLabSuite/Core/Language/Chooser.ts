/**
 * Dropdown language chooser.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Language/Chooser
 */

import * as Core from "../Core";
import * as Language from "../Language";
import DomUtil from "../Dom/Util";
import UiDropdownSimple from "../Ui/Dropdown/Simple";

type ChooserId = string;
type CallbackSelect = (listItem: HTMLElement) => void;
type SelectFieldOrHiddenInput = HTMLInputElement | HTMLSelectElement;

interface LanguageData {
  iconPath: string;
  languageCode?: string;
  languageName: string;
}

interface Languages {
  [key: string]: LanguageData;
}

interface ChooserData {
  callback: CallbackSelect;
  dropdownMenu: HTMLUListElement;
  dropdownToggle: HTMLAnchorElement;
  element: SelectFieldOrHiddenInput;
}

const _choosers = new Map<ChooserId, ChooserData>();
const _forms = new WeakMap<HTMLFormElement, ChooserId[]>();

/**
 * Sets up DOM and event listeners for a language chooser.
 */
function initElement(
  chooserId: string,
  element: SelectFieldOrHiddenInput,
  languageId: number,
  languages: Languages,
  callback: CallbackSelect,
  allowEmptyValue: boolean,
) {
  let container: HTMLElement;

  const parent = element.parentElement!;
  if (parent.nodeName === "DD") {
    container = document.createElement("div");
    container.className = "dropdown";

    // language chooser is the first child so that descriptions and error messages
    // are always shown below the language chooser
    parent.insertAdjacentElement("afterbegin", container);
  } else {
    container = parent;
    container.classList.add("dropdown");
  }

  DomUtil.hide(element);

  const dropdownToggle = document.createElement("a");
  dropdownToggle.className = "dropdownToggle dropdownIndicator boxFlag box24 inputPrefix";
  if (parent.nodeName === "DD") {
    dropdownToggle.classList.add("button");
  }
  container.appendChild(dropdownToggle);

  const dropdownMenu = document.createElement("ul");
  dropdownMenu.className = "dropdownMenu";
  container.appendChild(dropdownMenu);

  function callbackClick(event: MouseEvent): void {
    const target = event.currentTarget as HTMLElement;
    const languageId = ~~target.dataset.languageId!;

    const activeItem = dropdownMenu.querySelector(".active");
    if (activeItem !== null) {
      activeItem.classList.remove("active");
    }

    if (languageId) {
      target.classList.add("active");
    }

    select(chooserId, languageId, target);
  }

  // add language dropdown items
  Object.entries(languages).forEach(([langId, language]) => {
    const listItem = document.createElement("li");
    listItem.className = "boxFlag";
    listItem.addEventListener("click", callbackClick);
    listItem.dataset.languageId = langId;
    if (language.languageCode !== undefined) {
      listItem.dataset.languageCode = language.languageCode;
    }
    dropdownMenu.appendChild(listItem);

    const link = document.createElement("a");
    link.className = "box24";
    listItem.appendChild(link);

    const img = document.createElement("img");
    img.src = language.iconPath;
    img.alt = "";
    img.className = "iconFlag";
    link.appendChild(img);

    const span = document.createElement("span");
    span.textContent = language.languageName;
    link.appendChild(span);

    if (+langId === languageId) {
      dropdownToggle.innerHTML = link.innerHTML;
    }
  });

  // add dropdown item for "no selection"
  if (allowEmptyValue) {
    const divider = document.createElement("li");
    divider.className = "dropdownDivider";
    dropdownMenu.appendChild(divider);

    const listItem = document.createElement("li");
    listItem.dataset.languageId = "0";
    listItem.addEventListener("click", callbackClick);
    dropdownMenu.appendChild(listItem);

    const link = document.createElement("a");
    link.textContent = Language.get("wcf.global.language.noSelection");
    listItem.appendChild(link);

    if (languageId === 0) {
      dropdownToggle.innerHTML = link.innerHTML;
    }

    listItem.addEventListener("click", callbackClick);
  } else if (languageId === 0) {
    dropdownToggle.innerHTML = "";

    const div = document.createElement("div");
    dropdownToggle.appendChild(div);

    const icon = document.createElement("span");
    icon.className = "icon icon24 fa-question pointer";
    div.appendChild(icon);

    const span = document.createElement("span");
    span.textContent = Language.get("wcf.global.language.noSelection");
    div.appendChild(span);
  }

  UiDropdownSimple.init(dropdownToggle);

  _choosers.set(chooserId, {
    callback: callback,
    dropdownMenu: dropdownMenu,
    dropdownToggle: dropdownToggle,
    element: element,
  });

  // bind to submit event
  const form = element.closest("form") as HTMLFormElement;
  if (form !== null) {
    form.addEventListener("submit", onSubmit);

    let chooserIds = _forms.get(form);
    if (chooserIds === undefined) {
      chooserIds = [];
      _forms.set(form, chooserIds);
    }

    chooserIds.push(chooserId);
  }
}

/**
 * Selects a language from the dropdown list.
 */
function select(chooserId: string, languageId: number, listItem?: HTMLElement): void {
  const chooser = _choosers.get(chooserId)!;

  if (listItem === undefined) {
    listItem = Array.from(chooser.dropdownMenu.children).find((element: HTMLElement) => {
      return ~~element.dataset.languageId! === languageId;
    }) as HTMLElement;

    if (listItem === undefined) {
      throw new Error(`The language id '${languageId}' is unknown`);
    }
  }

  chooser.element.value = languageId.toString();
  Core.triggerEvent(chooser.element, "change");

  chooser.dropdownToggle.innerHTML = listItem.children[0].innerHTML;

  _choosers.set(chooserId, chooser);

  // execute callback
  if (typeof chooser.callback === "function") {
    chooser.callback(listItem);
  }
}

/**
 * Inserts hidden fields for the language chooser value on submit.
 */
function onSubmit(event: Event): void {
  const form = event.currentTarget as HTMLFormElement;
  const elementIds = _forms.get(form)!;

  elementIds.forEach((elementId) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = elementId;
    input.value = getLanguageId(elementId).toString();

    form.appendChild(input);
  });
}

/**
 * Initializes a language chooser.
 */
export function init(
  containerId: string,
  chooserId: string,
  languageId: number,
  languages: Languages,
  callback: CallbackSelect,
  allowEmptyValue: boolean,
): void {
  if (_choosers.has(chooserId)) {
    return;
  }

  const container = document.getElementById(containerId);
  if (container === null) {
    throw new Error(`Expected a valid container id, cannot find '${chooserId}'.`);
  }

  let element = document.getElementById(chooserId) as SelectFieldOrHiddenInput;
  if (element === null) {
    element = document.createElement("input");
    element.type = "hidden";
    element.id = chooserId;
    element.name = chooserId;
    element.value = languageId.toString();

    container.appendChild(element);
  }

  initElement(chooserId, element, languageId, languages, callback, allowEmptyValue);
}

/**
 * Returns the chooser for an input field.
 */
export function getChooser(chooserId: string): ChooserData {
  const chooser = _choosers.get(chooserId);
  if (chooser === undefined) {
    throw new Error(`Expected a valid language chooser input element, '${chooserId}' is not i18n input field.`);
  }

  return chooser;
}

/**
 * Returns the selected language for a certain chooser.
 */
export function getLanguageId(chooserId: string): number {
  return ~~getChooser(chooserId).element.value;
}

/**
 * Removes the chooser with given id.
 */
export function removeChooser(chooserId: string): void {
  _choosers.delete(chooserId);
}

/**
 * Sets the language for a certain chooser.
 */
export function setLanguageId(chooserId: string, languageId: number): void {
  if (_choosers.get(chooserId) === undefined) {
    throw new Error(`Expected a valid  input element, '${chooserId}' is not i18n input field.`);
  }

  select(chooserId, languageId);
}
