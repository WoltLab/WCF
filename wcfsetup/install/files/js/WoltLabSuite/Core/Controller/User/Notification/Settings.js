/**
 * Handles email notification type for user notification settings.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Controller/User/Notification/Settings
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dropdown/Reusable"], function (require, exports, tslib_1, Language, UiDropdownReusable) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Language = tslib_1.__importStar(Language);
    UiDropdownReusable = tslib_1.__importStar(UiDropdownReusable);
    let _dropDownMenu;
    let _objectId = 0;
    function stateChange(event) {
        const checkbox = event.currentTarget;
        const objectId = ~~checkbox.dataset.objectId;
        const emailSettingsType = document.querySelector(`.notificationSettingsEmailType[data-object-id="${objectId}"]`);
        if (emailSettingsType !== null) {
            if (checkbox.checked) {
                emailSettingsType.classList.remove("disabled");
            }
            else {
                emailSettingsType.classList.add("disabled");
            }
        }
    }
    function click(event) {
        event.preventDefault();
        const button = event.currentTarget;
        _objectId = ~~button.dataset.objectId;
        createDropDown();
        setCurrentEmailType(getCurrentEmailTypeInputElement().value);
        showDropDown(button);
    }
    function createDropDown() {
        if (_dropDownMenu) {
            return;
        }
        _dropDownMenu = document.createElement("ul");
        _dropDownMenu.className = "dropdownMenu";
        ["instant", "daily", "divider", "none"].forEach((value) => {
            const listItem = document.createElement("li");
            if (value === "divider") {
                listItem.className = "dropdownDivider";
            }
            else {
                const link = document.createElement("a");
                link.href = "#";
                link.textContent = Language.get(`wcf.user.notification.mailNotificationType.${value}`);
                listItem.appendChild(link);
                listItem.dataset.value = value;
                listItem.addEventListener("click", (ev) => setEmailType(ev));
            }
            _dropDownMenu.appendChild(listItem);
        });
        UiDropdownReusable.init("UiNotificationSettingsEmailType", _dropDownMenu);
    }
    function setCurrentEmailType(currentValue) {
        _dropDownMenu.querySelectorAll("li").forEach((button) => {
            const value = button.dataset.value;
            if (value === currentValue) {
                button.classList.add("active");
            }
            else {
                button.classList.remove("active");
            }
        });
    }
    function showDropDown(referenceElement) {
        UiDropdownReusable.toggleDropdown("UiNotificationSettingsEmailType", referenceElement);
    }
    function setEmailType(event) {
        event.preventDefault();
        const listItem = event.currentTarget;
        const value = listItem.dataset.value;
        getCurrentEmailTypeInputElement().value = value;
        const button = document.querySelector(`.notificationSettingsEmailType[data-object-id="${_objectId}"]`);
        button.title = Language.get(`wcf.user.notification.mailNotificationType.${value}`);
        const icon = button.querySelector(".jsIconNotificationSettingsEmailType");
        icon.classList.remove("fa-clock-o", "fa-flash", "fa-times", "green", "red");
        switch (value) {
            case "daily":
                icon.classList.add("fa-clock-o", "green");
                break;
            case "instant":
                icon.classList.add("fa-flash", "green");
                break;
            case "none":
                icon.classList.add("fa-times", "red");
                break;
        }
        _objectId = 0;
    }
    function getCurrentEmailTypeInputElement() {
        return document.getElementById(`settings_${_objectId}_mailNotificationType`);
    }
    /**
     * Binds event listeners for all notifications supporting emails.
     */
    function init() {
        document.querySelectorAll(".jsCheckboxNotificationSettingsState").forEach((checkbox) => {
            checkbox.addEventListener("change", (ev) => stateChange(ev));
        });
        document.querySelectorAll(".notificationSettingsEmailType").forEach((button) => {
            button.addEventListener("click", (ev) => click(ev));
        });
    }
    exports.init = init;
});
