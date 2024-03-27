/**
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Language", "../../StringUtil", "../../Dom/Change/Listener", "../../Dom/Util", "../User/Search/Input"], function (require, exports, tslib_1, Language, StringUtil, Listener_1, Util_1, Input_1) {
    "use strict";
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Input_1 = tslib_1.__importDefault(Input_1);
    class UiAclSimple {
        aclListContainer;
        list;
        prefix;
        inputName;
        searchInput;
        constructor(prefix, inputName) {
            this.prefix = prefix || "";
            this.inputName = inputName || "aclValues";
            const container = document.getElementById(this.prefix + "aclInputContainer");
            const invertPermissionsDl = document.getElementById(this.prefix + "invertPermissionsDl");
            const allowAll = document.getElementById(this.prefix + "aclAllowAll");
            allowAll.addEventListener("change", () => {
                Util_1.default.hide(container);
                if (invertPermissionsDl) {
                    Util_1.default.hide(invertPermissionsDl);
                }
            });
            const denyAll = document.getElementById(this.prefix + "aclAllowAll_no");
            denyAll.addEventListener("change", () => {
                Util_1.default.show(container);
                if (invertPermissionsDl) {
                    Util_1.default.show(invertPermissionsDl);
                }
            });
            this.list = document.getElementById(this.prefix + "aclAccessList");
            this.list.querySelectorAll(".aclItemDeleteButton").forEach((button) => {
                button.addEventListener("click", (event) => this.removeItem(event));
            });
            const excludedSearchValues = [];
            this.list.querySelectorAll(".aclLabel").forEach((label) => {
                excludedSearchValues.push(label.textContent);
            });
            this.searchInput = new Input_1.default(document.getElementById(this.prefix + "aclSearchInput"), {
                callbackSelect: this.select.bind(this),
                includeUserGroups: true,
                excludedSearchValues: excludedSearchValues,
                preventSubmit: true,
            });
            this.aclListContainer = document.getElementById(this.prefix + "aclListContainer");
            const invertPermission = document.getElementById(this.prefix + "invertPermissions");
            if (invertPermission) {
                invertPermission.addEventListener("change", () => {
                    this.invertPermissions(true);
                });
            }
            const normalPermission = document.getElementById(this.prefix + "invertPermissions_no");
            if (normalPermission) {
                normalPermission.addEventListener("change", () => {
                    this.invertPermissions(false);
                });
            }
            const invertPermissionRadioButton = document.getElementById(this.prefix + "invertPermissions");
            if (invertPermissionRadioButton) {
                this.invertPermissions(invertPermissionRadioButton.checked);
            }
            Listener_1.default.trigger();
        }
        select(listItem) {
            const type = listItem.dataset.type;
            const label = listItem.dataset.label;
            const objectId = listItem.dataset.objectId;
            const iconName = type === "group" ? "users" : "user";
            const html = `<fa-icon class="aclListItemIcon" name="${iconName}"></fa-icon>
      <span class="aclLabel">${StringUtil.escapeHTML(label)}</span>
      <button type="button" class="aclItemDeleteButton jsTooltip" title="${Language.get("wcf.global.button.delete")}">
        <fa-icon name="xmark"></fa-icon>
      </button>
      <input type="hidden" name="${this.inputName}[${type}][]" value="${objectId}">`;
            const item = document.createElement("li");
            item.classList.add("aclListItem");
            item.innerHTML = html;
            const firstUser = this.list.querySelector('fa-icon[name="user"]');
            if (firstUser === null) {
                this.list.appendChild(item);
            }
            else {
                this.list.insertBefore(item, firstUser.parentNode);
            }
            Util_1.default.show(this.aclListContainer);
            this.searchInput.addExcludedSearchValues(label);
            const deleteButton = item.querySelector(".aclItemDeleteButton");
            deleteButton.addEventListener("click", (event) => this.removeItem(event));
            Listener_1.default.trigger();
            return false;
        }
        removeItem(event) {
            const target = event.currentTarget;
            const parent = target.parentElement;
            const label = parent.querySelector(".aclLabel");
            this.searchInput.removeExcludedSearchValues(label.textContent);
            parent.remove();
            if (this.list.childElementCount === 0) {
                Util_1.default.hide(this.aclListContainer);
            }
        }
        invertPermissions(invert) {
            const aclListContainerDt = document.getElementById(this.prefix + "aclListContainerDt");
            const aclSearchInputLabel = document.getElementById(this.prefix + "aclSearchInputLabel");
            aclListContainerDt.textContent = Language.get(invert ? "wcf.acl.access.denied" : "wcf.acl.access.granted");
            aclSearchInputLabel.textContent = Language.get(invert ? "wcf.acl.access.deny" : "wcf.acl.access.grant");
        }
    }
    return UiAclSimple;
});
