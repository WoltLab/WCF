/**
 * User editing capabilities for the user list.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Editor
 * @since       3.1
 */
define(["require", "exports", "tslib", "./Content/Remove/Handler", "../../../Ajax", "../../../Core", "../../../Event/Handler", "../../../Language", "../../../Ui/Notification", "../../../Ui/Dropdown/Simple", "../../../Dom/Util"], function (require, exports, tslib_1, Handler_1, Ajax, Core, EventHandler, Language, UiNotification, Simple_1, Util_1) {
    "use strict";
    Handler_1 = tslib_1.__importDefault(Handler_1);
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiUserEditor {
        /**
         * Initializes the edit dropdown for each user.
         */
        constructor() {
            document.querySelectorAll(".jsUserRow").forEach((userRow) => this.initUser(userRow));
            EventHandler.add("com.woltlab.wcf.acp.user", "refresh", (data) => this.refreshUsers(data));
        }
        /**
         * Initializes the edit dropdown for a user.
         */
        initUser(userRow) {
            const userId = ~~userRow.dataset.objectId;
            const dropdownId = `userListDropdown${userId}`;
            const dropdownMenu = Simple_1.default.getDropdownMenu(dropdownId);
            const legacyButtonContainer = userRow.querySelector(".jsLegacyButtons");
            if (dropdownMenu.childElementCount === 0 && legacyButtonContainer.childElementCount === 0) {
                const toggleButton = userRow.querySelector(".dropdownToggle");
                toggleButton.classList.add("disabled");
                return;
            }
            Simple_1.default.registerCallback(dropdownId, (identifier, action) => {
                if (action === "open") {
                    this.rebuild(dropdownMenu, legacyButtonContainer);
                }
            });
            const editLink = dropdownMenu.querySelector(".jsEditLink");
            if (editLink !== null) {
                const toggleButton = userRow.querySelector(".dropdownToggle");
                toggleButton.addEventListener("dblclick", (event) => {
                    event.preventDefault();
                    editLink.click();
                });
            }
            const sendNewPassword = dropdownMenu.querySelector(".jsSendNewPassword");
            if (sendNewPassword !== null) {
                sendNewPassword.addEventListener("click", (event) => {
                    event.preventDefault();
                    // emulate clipboard selection
                    EventHandler.fire("com.woltlab.wcf.clipboard", "com.woltlab.wcf.user", {
                        data: {
                            actionName: "com.woltlab.wcf.user.sendNewPassword",
                            parameters: {
                                confirmMessage: Language.get("wcf.acp.user.action.sendNewPassword.confirmMessage"),
                                objectIDs: [userId],
                            },
                        },
                        responseData: {
                            actionName: "com.woltlab.wcf.user.sendNewPassword",
                            objectIDs: [userId],
                        },
                    });
                });
            }
            const deleteContent = dropdownMenu.querySelector(".jsDeleteContent");
            if (deleteContent !== null) {
                new Handler_1.default(deleteContent, userId);
            }
            const toggleConfirmEmail = dropdownMenu.querySelector(".jsConfirmEmailToggle");
            if (toggleConfirmEmail !== null) {
                toggleConfirmEmail.addEventListener("click", (event) => {
                    event.preventDefault();
                    Ajax.api({
                        _ajaxSetup: () => {
                            const isEmailConfirmed = Core.stringToBool(userRow.dataset.emailConfirmed);
                            return {
                                data: {
                                    actionName: (isEmailConfirmed ? "un" : "") + "confirmEmail",
                                    className: "wcf\\data\\user\\UserAction",
                                    objectIDs: [userId],
                                },
                            };
                        },
                    }, undefined, (data) => {
                        document.querySelectorAll(".jsUserRow").forEach((userRow) => {
                            const userId = ~~userRow.dataset.objectId;
                            if (data.objectIDs.includes(userId)) {
                                const confirmEmailButton = dropdownMenu.querySelector(".jsConfirmEmailToggle");
                                switch (data.actionName) {
                                    case "confirmEmail":
                                        userRow.dataset.emailConfirmed = "true";
                                        confirmEmailButton.textContent = confirmEmailButton.dataset.unconfirmEmailMessage;
                                        break;
                                    case "unconfirmEmail":
                                        userRow.dataset.emailEonfirmed = "false";
                                        confirmEmailButton.textContent = confirmEmailButton.dataset.confirmEmailMessage;
                                        break;
                                    default:
                                        throw new Error("Unreachable");
                                }
                            }
                        });
                        UiNotification.show();
                    });
                });
            }
        }
        /**
         * Rebuilds the dropdown by adding wrapper links for legacy buttons,
         * that will eventually receive the click event.
         */
        rebuild(dropdownMenu, legacyButtonContainer) {
            dropdownMenu.querySelectorAll(".jsLegacyItem").forEach((element) => element.remove());
            // inject buttons
            const items = [];
            let deleteButton = null;
            Array.from(legacyButtonContainer.children).forEach((button) => {
                if (button.classList.contains("jsObjectAction") && button.dataset.objectAction === "delete") {
                    deleteButton = button;
                    return;
                }
                const item = document.createElement("li");
                item.className = "jsLegacyItem";
                item.innerHTML = '<a href="#"></a>';
                const link = item.children[0];
                link.textContent = button.dataset.tooltip || button.title;
                link.addEventListener("click", (event) => {
                    event.preventDefault();
                    // forward click onto original button
                    if (button.nodeName === "A") {
                        button.click();
                    }
                    else {
                        Core.triggerEvent(button, "click");
                    }
                });
                items.push(item);
            });
            items.forEach((item) => {
                dropdownMenu.insertAdjacentElement("afterbegin", item);
            });
            if (deleteButton !== null) {
                const dispatchDeleteButton = dropdownMenu.querySelector(".jsDispatchDelete");
                dispatchDeleteButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    deleteButton.click();
                });
            }
            // check if there are visible items before each divider
            const listItems = Array.from(dropdownMenu.children);
            listItems.forEach((element) => Util_1.default.show(element));
            let hasItem = false;
            listItems.forEach((item) => {
                if (item.classList.contains("dropdownDivider")) {
                    if (!hasItem) {
                        Util_1.default.hide(item);
                    }
                }
                else {
                    hasItem = true;
                }
            });
        }
        refreshUsers(data) {
            document.querySelectorAll(".jsUserRow").forEach((userRow) => {
                const userId = ~~userRow.dataset.objectId;
                if (data.userIds.includes(userId)) {
                    const userStatusIcons = userRow.querySelector(".userStatusIcons");
                    const banned = Core.stringToBool(userRow.dataset.banned);
                    let iconBanned = userRow.querySelector(".jsUserStatusBanned");
                    if (banned && iconBanned === null) {
                        iconBanned = document.createElement("span");
                        iconBanned.className = "icon icon16 fa-lock jsUserStatusBanned jsTooltip";
                        iconBanned.title = Language.get("wcf.user.status.banned");
                        userStatusIcons.appendChild(iconBanned);
                    }
                    else if (!banned && iconBanned !== null) {
                        iconBanned.remove();
                    }
                    const isDisabled = !Core.stringToBool(userRow.dataset.enabled);
                    let iconIsDisabled = userRow.querySelector(".jsUserStatusIsDisabled");
                    if (isDisabled && iconIsDisabled === null) {
                        iconIsDisabled = document.createElement("span");
                        iconIsDisabled.className = "icon icon16 fa-power-off jsUserStatusIsDisabled jsTooltip";
                        iconIsDisabled.title = Language.get("wcf.user.status.isDisabled");
                        userStatusIcons.appendChild(iconIsDisabled);
                    }
                    else if (!isDisabled && iconIsDisabled !== null) {
                        iconIsDisabled.remove();
                    }
                }
            });
        }
    }
    return AcpUiUserEditor;
});
