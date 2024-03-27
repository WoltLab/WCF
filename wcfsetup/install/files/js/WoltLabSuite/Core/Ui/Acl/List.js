/**
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ui/User/Search/Input", "WoltLabSuite/Core/Form/Builder/Field/Dependency/Manager", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/StringUtil", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Core"], function (require, exports, tslib_1, Input_1, Manager_1, Language_1, Util_1, StringUtil, Ajax, Core_1) {
    "use strict";
    Input_1 = tslib_1.__importDefault(Input_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    StringUtil = tslib_1.__importStar(StringUtil);
    Ajax = tslib_1.__importStar(Ajax);
    return class AclList {
        #categoryName;
        #container;
        #aclList;
        #permissionList;
        #searchInput;
        #objectID;
        #objectTypeID;
        #aclValuesFieldName;
        #search;
        #values;
        constructor(containerSelector, objectTypeID, categoryName, objectID, includeUserGroups, initialPermissions, aclValuesFieldName) {
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
            this.#container = document.querySelector(containerSelector);
            Util_1.default.hide(this.#container);
            this.#container.classList.add("aclContainer");
            // insert container elements
            const elementContainer = this.#container.querySelector("dd");
            this.#aclList = document.createElement("ul");
            this.#aclList.classList.add("aclList");
            elementContainer.appendChild(this.#aclList);
            this.#searchInput = document.createElement("input");
            this.#searchInput.type = "text";
            this.#searchInput.classList.add("long");
            this.#searchInput.placeholder = (0, Language_1.getPhrase)("wcf.acl.search." + (!includeUserGroups ? "user." : "") + "description");
            elementContainer.appendChild(this.#searchInput);
            this.#permissionList = document.createElement("div");
            this.#permissionList.classList.add("aclPermissionList");
            Util_1.default.hide(this.#permissionList);
            elementContainer.appendChild(this.#permissionList);
            // prepare search input
            this.#search = new Input_1.default(this.#searchInput, {
                callbackSelect: this.addObject.bind(this),
                includeUserGroups: includeUserGroups,
                preventSubmit: true,
            });
            // bind event listener for submit
            const form = this.#container.closest("form");
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
            }
            else {
                this.#loadACL();
            }
        }
        getData() {
            this.#savePermissions();
            return this.#values;
        }
        addObject(selectedItem) {
            const type = selectedItem.dataset.type;
            const label = selectedItem.dataset.label;
            const objectId = selectedItem.dataset.objectId;
            const listItem = this.#createListItem(objectId, label, type);
            // toggle element
            this.#savePermissions();
            this.#aclList.querySelectorAll("li").forEach((element) => {
                element.classList.remove("active");
            });
            listItem.classList.add("active");
            this.#search.addExcludedSearchValues(label);
            this.#select(listItem, false);
            // clear search input
            this.#searchInput.value = "";
            // show permissions
            Util_1.default.show(this.#permissionList);
            return false;
        }
        submit() {
            this.#savePermissions();
            this.#save("group");
            this.#save("user");
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
            Util_1.default.hide(this.#permissionList);
            this.#permissionList.querySelectorAll("input[type=radio]").forEach((inputElement) => {
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
                success: (data) => {
                    this.#success(data);
                },
            });
        }
        #createListItem(objectID, label, type) {
            const html = `<fa-icon size="16" name="${type === "group" ? "users" : "user"}" solid></fa-icon>
        <span class="aclLabel">${StringUtil.escapeHTML(label)}</span>
        <button type="button" class="aclItemDeleteButton jsTooltip" title="${(0, Language_1.getPhrase)("wcf.global.button.delete")}">
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
            const deleteButton = listItem.querySelector(".aclItemDeleteButton");
            deleteButton.addEventListener("click", () => this.#removeItem(listItem));
            this.#aclList.appendChild(listItem);
            return listItem;
        }
        #removeItem(listItem) {
            this.#savePermissions();
            const type = listItem.dataset.type;
            const objectID = listItem.dataset.objectId;
            this.#search.removeExcludedSearchValues(listItem.dataset.label);
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
            }
            else {
                this.#reset();
            }
        }
        #success(data) {
            if (Object.keys(data.returnValues.options).length === 0) {
                return;
            }
            const header = document.createElement("div");
            header.classList.add("aclHeader");
            header.innerHTML = `<span class="aclHeaderSpan aclHeaderInherited">${(0, Language_1.getPhrase)("wcf.acl.option.inherited")}</span>
        <span class="aclHeaderSpan aclHeaderGrant">${(0, Language_1.getPhrase)("wcf.acl.option.grant")}</span>
        <span class="aclHeaderSpan aclHeaderDeny">${(0, Language_1.getPhrase)("wcf.acl.option.deny")}</span>`;
            this.#permissionList.appendChild(header);
            // prepare options
            const structure = {};
            for (const [optionID, option] of Object.entries(data.returnValues.options)) {
                const listItem = document.createElement("div");
                listItem.classList.add("aclOption", "aclPermissionListItem");
                listItem.innerHTML = `<span class="aclOptionTitle">${StringUtil.escapeHTML(option.label)}</span>
        <label for="inherited${optionID}" class="inherited aclOptionInputLabel jsTooltip" title="${(0, Language_1.getPhrase)("wcf.acl.option.inherited")}">
          <input type="radio" id="inherited${optionID}" />
        </label>
        <label for="grant${optionID}" class="grant aclOptionInputLabel jsTooltip" title="${(0, Language_1.getPhrase)("wcf.acl.option.grant")}">
          <input type="radio" id="grant${optionID}" />
        </label>
        <label for="deny${optionID}" class="deny aclOptionInputLabel jsTooltip" title="${(0, Language_1.getPhrase)("wcf.acl.option.deny")}">
          <input type="radio" id="deny${optionID}" />
        </label>`;
                listItem.dataset.optionId = optionID;
                listItem.dataset.optionName = option.optionName;
                const grantPermission = listItem.querySelector(`#grant${optionID}`);
                const denyPermission = listItem.querySelector(`#deny${optionID}`);
                const inheritedPermission = listItem.querySelector(`#inherited${optionID}`);
                grantPermission.dataset.type = "grant";
                grantPermission.dataset.optionId = optionID;
                grantPermission.addEventListener("change", this.#change.bind(this));
                denyPermission.dataset.type = "deny";
                denyPermission.dataset.optionId = optionID;
                denyPermission.addEventListener("change", this.#change.bind(this));
                inheritedPermission.dataset.type = "inherited";
                inheritedPermission.dataset.optionId = optionID;
                inheritedPermission.addEventListener("change", this.#change.bind(this));
                if (!structure[option.categoryName]) {
                    structure[option.categoryName] = [];
                }
                if (option.categoryName === "") {
                    this.#permissionList.appendChild(listItem);
                }
                else {
                    structure[option.categoryName].push(listItem);
                }
            }
            if (Object.keys(structure).length > 0) {
                for (const [categoryName, listItems] of Object.entries(structure)) {
                    if (data.returnValues.categories[categoryName]) {
                        const category = document.createElement("div");
                        category.classList.add("aclCategory", "aclPermissionListItem");
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
            Util_1.default.show(this.#container);
            // Because the container might have been hidden before, we must ensure that
            // form builder field dependencies are checked again to avoid having ACL
            // form fields not being shown in form builder forms.
            (0, Manager_1.checkDependencies)();
            // pre-select an entry
            this.#selectFirstEntry();
        }
        #parseData(data, type) {
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
        #select(listItem, savePermissions) {
            // save previous permissions
            if (savePermissions) {
                this.#savePermissions();
            }
            // switch active item
            this.#aclList.querySelectorAll("li").forEach((li) => {
                li.classList.remove("active");
            });
            listItem.classList.add("active");
            // apply permissions for current item
            this.#setupPermissions(listItem.dataset.type, listItem.dataset.objectId);
        }
        #change(event) {
            const checkbox = event.currentTarget;
            const optionID = checkbox.dataset.optionId;
            const type = checkbox.dataset.type;
            if (checkbox.checked) {
                switch (type) {
                    case "grant":
                        document.getElementById("deny" + optionID).checked = false;
                        document.getElementById("inherited" + optionID).checked = false;
                        break;
                    case "deny":
                        document.getElementById("grant" + optionID).checked = false;
                        document.getElementById("inherited" + optionID).checked = false;
                        break;
                    case "inherited":
                        document.getElementById("deny" + optionID).checked = false;
                        document.getElementById("grant" + optionID).checked = false;
                        break;
                }
            }
        }
        #setupPermissions(type, objectID) {
            // reset all checkboxes to default value
            this.#permissionList.querySelectorAll("input[type='radio']").forEach((inputElement) => {
                inputElement.checked = inputElement.dataset.type === "inherited";
            });
            // use stored permissions if applicable
            if (this.#values[type] && this.#values[type][objectID]) {
                for (const optionID in this.#values[type][objectID]) {
                    if (this.#values[type][objectID][optionID] == 1) {
                        const option = document.getElementById("grant" + optionID);
                        option.checked = true;
                        option.dispatchEvent(new Event("change"));
                    }
                    else {
                        const option = document.getElementById("deny" + optionID);
                        option.checked = true;
                        option.dispatchEvent(new Event("change"));
                    }
                }
            }
            // show permissions
            Util_1.default.show(this.#permissionList);
        }
        #savePermissions() {
            // get active object
            const activeObject = this.#aclList.querySelector("li.active");
            if (!activeObject) {
                return;
            }
            const objectID = activeObject.dataset.objectId;
            const type = activeObject.dataset.type;
            // clear old values
            this.#values[type][objectID] = {};
            this.#permissionList.querySelectorAll("input[type='radio']").forEach((checkbox) => {
                if (checkbox.dataset.type === "inherited") {
                    return;
                }
                const optionValue = checkbox.dataset.type === "deny" ? 0 : 1;
                const optionID = checkbox.dataset.optionId;
                if (checkbox.checked) {
                    // store value
                    this.#values[type][objectID][optionID] = optionValue;
                    // reset value afterwards
                    checkbox.checked = false;
                }
                else if (this.#values[type] &&
                    this.#values[type][objectID] &&
                    this.#values[type][objectID][optionID] &&
                    this.#values[type][objectID][optionID] == optionValue) {
                    delete this.#values[type][objectID][optionID];
                }
            });
        }
        #save(type) {
            const form = this.#container.closest("form");
            const name = this.#aclValuesFieldName + "[" + type + "]";
            let input = form.querySelector("input[name='" + name + "']");
            if (input) {
                // combine json values
                input.value = JSON.stringify((0, Core_1.extend)(JSON.parse(input.value), this.#values[type]));
            }
            else {
                input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                input.value = JSON.stringify(this.#values[type]);
                form.appendChild(input);
            }
        }
    };
});
