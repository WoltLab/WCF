/**
 * @woltlabExcludeBundle all
 */

import UiUserSearchInput from "WoltLabSuite/Core/Ui/User/Search/Input";
import { checkDependencies } from "WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager";
import { getPhrase } from "WoltLabSuite/Core/Language";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import * as StringUtil from "WoltLabSuite/Core/StringUtil";
import { DatabaseObjectActionResponse } from "WoltLabSuite/Core/Ajax/Data";
import * as Ajax from "WoltLabSuite/Core/Ajax";

interface AclOption {
  categoryName: string;
  label: string;
  optionName: string;
}

interface AclValues {
  label: {
    [key: string]: string;
  };
  option: {
    [key: string]: {
      [key: string]: number;
    };
  };
}

interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: {
    options: {
      [key: string]: AclOption;
    };
    group: AclValues;
    user: AclValues;
    categories: {
      [key: string]: string;
    };
  };
}

export = class AclList {
  readonly #categoryName: string | undefined;
  readonly #container: HTMLElement;
  readonly #aclList: HTMLUListElement;
  readonly #permissionList: HTMLUListElement;
  readonly #searchInput: HTMLInputElement;
  readonly #objectID: number;
  readonly #objectTypeID: number;
  readonly #aclValuesFieldName: string;
  readonly #search: UiUserSearchInput;
  #values: {
    [key: string]: {
      [key: string]: {
        [key: string]: number;
      };
    };
  };

  constructor(
    containerSelector: string,
    objectTypeID: number,
    categoryName: string | undefined,
    objectID: number,
    includeUserGroups: boolean,
    initialPermissions: AjaxResponse | undefined,
    aclValuesFieldName: string | undefined,
  ) {
    this.#objectID = objectID || 0;
    this.#objectTypeID = objectTypeID;
    this.#categoryName = categoryName;
    if (includeUserGroups === undefined) {
      includeUserGroups = true;
    }
    this.#values = {
      group: {},
      user: {},
    };
    this.#aclValuesFieldName = aclValuesFieldName || "aclValues";

    // bind hidden container
    this.#container = document.querySelector(containerSelector)!;
    DomUtil.hide(this.#container);
    this.#container.classList.add("aclContainer");

    // insert container elements
    const elementContainer = this.#container.querySelector("dd")!;
    this.#aclList = document.createElement("ul");
    this.#aclList.classList.add("aclList", "containerList");
    elementContainer.appendChild(this.#aclList);

    this.#searchInput = document.createElement("input");
    this.#searchInput.type = "text";
    this.#searchInput.classList.add("long");
    this.#searchInput.placeholder = getPhrase("wcf.acl.search." + (!includeUserGroups ? "user." : "") + "description");
    elementContainer.appendChild(this.#searchInput);

    this.#permissionList = document.createElement("ul");
    this.#permissionList.classList.add("aclPermissionList", "containerList");
    this.#permissionList.dataset.grant = getPhrase("wcf.acl.option.grant");
    this.#permissionList.dataset.deny = getPhrase("wcf.acl.option.deny");
    DomUtil.hide(this.#permissionList);
    elementContainer.appendChild(this.#permissionList);

    // prepare search input
    this.#search = new UiUserSearchInput(this.#searchInput, {
      callbackSelect: this.addObject.bind(this),
      includeUserGroups: includeUserGroups,
      preventSubmit: true,
    });

    // bind event listener for submit
    const form = this.#container.closest("form")!;
    form.addEventListener("submit", () => {
      this.submit();
    });

    // reset ACL on click
    const resetButton = form.querySelector("input[type=reset]");
    resetButton?.addEventListener("click", () => {
      this.#reset();
    });

    if (initialPermissions) {
      this.#success(initialPermissions);
    } else {
      this.#loadACL();
    }
  }

  addObject(selectedItem: HTMLLIElement): boolean {
    const type = selectedItem.dataset.type!;
    const label = selectedItem.dataset.label!;
    const objectId = selectedItem.dataset.objectId!;

    const listItem = this.#createListItem(objectId, label, type);

    // toggle element
    this.#savePermissions();
    this.#aclList.querySelectorAll("li").forEach((element: HTMLLIElement) => {
      element.classList.remove("active");
    });
    listItem.classList.add("active");

    this.#search.addExcludedSearchValues(label);

    // uncheck all option values
    this.#permissionList.querySelectorAll("input[type=checkbox]").forEach((inputElement: HTMLInputElement) => {
      inputElement.checked = false;
    });

    // clear search input
    this.#searchInput.value = "";

    // show permissions
    DomUtil.show(this.#permissionList);

    return false;
  }

  submit() {
    this.#savePermissions();

    this.#save("group");
    this.#save("user");
  }

  getData() {
    this.#savePermissions();

    return this.#values;
  }

  #reset() {
    // reset stored values
    this.#values = {
      group: {},
      user: {},
    };

    // remove entries
    this.#aclList.innerHTML = "";
    this.#searchInput.value = "";

    // deselect all input elements
    DomUtil.hide(this.#permissionList);
    this.#permissionList.querySelectorAll("input[type=checkbox]").forEach((inputElement: HTMLInputElement) => {
      inputElement.checked = false;
    });
  }

  #loadACL() {
    Ajax.apiOnce({
      data: {
        actionName: "loadAll",
        className: "wcf\\data\\acl\\option\\ACLOptionAction",
        parameters: {
          categoryName: this.#categoryName,
          objectID: this.#objectID,
          objectTypeID: this.#objectTypeID,
        },
      },
      success: (data: AjaxResponse) => {
        this.#success(data);
      },
    });
  }

  #createListItem(objectID: string, label: string, type: string): HTMLLIElement {
    const html = `<fa-icon size="16" name="${type === "group" ? "users" : "user"}" solid></fa-icon>
        <span class="aclLabel">${StringUtil.escapeHTML(label)}</span>
        <button type="button" class="aclItemDeleteButton jsTooltip" title="${getPhrase("wcf.global.button.delete")}">
          <fa-icon size="16" name="xmark" solid></fa-icon>
        </button>`;
    const listItem = document.createElement("li");
    listItem.innerHTML = html;
    listItem.dataset.objectId = objectID;
    listItem.dataset.type = type;
    listItem.dataset.label = label;
    listItem.addEventListener("click", () => {
      if (listItem.classList.contains("active")) {
        return;
      }

      this.#select(listItem, true);
    });

    const deleteButton = listItem.querySelector(".aclItemDeleteButton") as HTMLButtonElement;
    deleteButton.addEventListener("click", () => this.#removeItem(listItem));

    this.#aclList.appendChild(listItem);

    return listItem;
  }

  #removeItem(listItem: HTMLLIElement) {
    this.#savePermissions();

    const type = listItem.dataset.type!;
    const objectID = listItem.dataset.objectId!;

    this.#search.removeExcludedSearchValues(listItem.dataset.label!);
    listItem.remove();

    // remove stored data
    if (this.#values[type][objectID]) {
      delete this.#values[type][objectID];
    }

    // try to select something else
    this.#selectFirstEntry();
  }

  #selectFirstEntry() {
    const listItem = this.#aclList.querySelector("li");
    if (listItem) {
      this.#select(listItem, false);
    } else {
      this.#reset();
    }
  }

  #success(data: AjaxResponse) {
    if (Object.keys(data.returnValues.options).length === 0) {
      return;
    }

    // prepare options
    const structure: { [key: string]: HTMLLIElement[] } = {};
    for (const [optionID, option] of Object.entries(data.returnValues.options)) {
      const listItem = document.createElement("li");

      listItem.innerHTML = `<span>${StringUtil.escapeHTML(option.label)}</span>
        <label for="grant${optionID}" class="jsTooltip" title="${getPhrase("wcf.acl.option.grant")}">
          <input type="checkbox" id="grant${optionID}" />
        </label>
        <label for="deny${optionID}" class="jsTooltip" title="${getPhrase("wcf.acl.option.deny")}">
          <input type="checkbox" id="deny${optionID}" />
        </label>`;
      listItem.dataset.optionId = optionID;
      listItem.dataset.optionName = option.optionName;

      const grantPermission = listItem.querySelector(`#grant${optionID}`) as HTMLInputElement;
      const denyPermission = listItem.querySelector(`#deny${optionID}`) as HTMLInputElement;

      grantPermission.dataset.type = "grant";
      grantPermission.dataset.optionId = optionID;
      grantPermission.addEventListener("change", this.#change.bind(this));

      denyPermission.dataset.type = "deny";
      denyPermission.dataset.optionId = optionID;
      denyPermission.addEventListener("change", this.#change.bind(this));

      if (!structure[option.categoryName]) {
        structure[option.categoryName] = [];
      }

      if (option.categoryName === "") {
        this.#permissionList.appendChild(listItem);
      } else {
        structure[option.categoryName].push(listItem);
      }
    }

    if (Object.keys(structure).length > 0) {
      for (const [categoryName, listItems] of Object.entries(structure)) {
        if (data.returnValues.categories[categoryName]) {
          const category = document.createElement("li");
          category.innerText = StringUtil.escapeHTML(data.returnValues.categories[categoryName]);
          this.#permissionList.appendChild(category);
        }

        listItems.forEach((listItem) => {
          this.#permissionList.appendChild(listItem);
        });
      }
    }

    // set data
    this.#parseData(data, "group");
    this.#parseData(data, "user");

    // show container
    DomUtil.show(this.#container);

    // Because the container might have been hidden before, we must ensure that
    // form builder field dependencies are checked again to avoid having ACL
    // form fields not being shown in form builder forms.
    checkDependencies();

    // pre-select an entry
    this.#selectFirstEntry();
  }

  #parseData(data: AjaxResponse, type: string) {
    if (Object.keys(data.returnValues[type].option).length === 0) {
      return;
    }

    // add list items
    for (const typeID in data.returnValues[type].label) {
      this.#createListItem(typeID, data.returnValues[type].label[typeID], type);

      this.#search.addExcludedSearchValues(data.returnValues[type].label[typeID]);
    }

    // add options
    this.#values[type] = data.returnValues[type].option;
  }

  #select(listItem: HTMLLIElement, savePermissions: boolean) {
    // save previous permissions
    if (savePermissions) {
      this.#savePermissions();
    }

    // switch active item
    this.#aclList.querySelectorAll("li").forEach((li: HTMLLIElement) => {
      li.classList.remove("active");
    });
    listItem.classList.add("active");

    // apply permissions for current item
    this.#setupPermissions(listItem.dataset.type!, listItem.dataset.objectId!);
  }

  #change(event: MouseEvent) {
    const checkbox = event.currentTarget as HTMLInputElement;
    const optionID = checkbox.dataset.optionId!;
    const type = checkbox.dataset.type!;

    if (checkbox.checked) {
      if (type === "deny") {
        (document.getElementById("grant" + optionID)! as HTMLInputElement).checked = false;
      } else {
        (document.getElementById("deny" + optionID)! as HTMLInputElement).checked = false;
      }
    }
  }

  #setupPermissions(type: string, objectID: string) {
    // reset all checkboxes to unchecked
    this.#permissionList.querySelectorAll("input[type='checkbox']").forEach((inputElement: HTMLInputElement) => {
      inputElement.checked = false;
    });

    // use stored permissions if applicable
    if (this.#values[type] && this.#values[type][objectID]) {
      for (const optionID in this.#values[type][objectID]) {
        if (this.#values[type][objectID][optionID] == 1) {
          const option = document.getElementById("grant" + optionID) as HTMLInputElement;
          option.checked = true;
          option.dispatchEvent(new Event("change"));
        } else {
          const option = document.getElementById("deny" + optionID) as HTMLInputElement;
          option.checked = true;
          option.dispatchEvent(new Event("change"));
        }
      }
    }

    // show permissions
    DomUtil.show(this.#permissionList);
  }

  #savePermissions() {
    // get active object
    const activeObject = this.#aclList.querySelector("li.active") as HTMLLIElement;
    if (!activeObject) {
      return;
    }

    const objectID = activeObject.dataset.objectId!;
    const type = activeObject.dataset.type!;

    // clear old values
    this.#values[type][objectID] = {};
    this.#permissionList.querySelectorAll("input[type='checkbox']").forEach((checkbox: HTMLInputElement) => {
      const optionValue = checkbox.dataset.type === "deny" ? 0 : 1;
      const optionID = checkbox.dataset.optionId!;

      if (checkbox.checked) {
        // store value
        this.#values[type][objectID][optionID] = optionValue;

        // reset value afterwards
        checkbox.checked = false;
      } else if (
        this.#values[type] &&
        this.#values[type][objectID] &&
        this.#values[type][objectID][optionID] &&
        this.#values[type][objectID][optionID] == optionValue
      ) {
        delete this.#values[type][objectID][optionID];
      }
    });
  }

  #save(type: string) {
    //TODO change to store as json value in one input
    /*if ($.getLength(this.#values[$type])) {
      const $form = this.#container.parents("form:eq(0)");

      for (const $objectID in this.#values[$type]) {
        const $object = this.#values[$type][$objectID];

        for (const $optionID in $object) {
          $(
            '<input type="hidden" name="' +
              this.#aclValuesFieldName +
              "[" +
              $type +
              "][" +
              $objectID +
              "][" +
              $optionID +
              ']" value="' +
              $object[$optionID] +
              '" />',
          ).appendTo($form);
        }
      }
    }*/
  }
};
