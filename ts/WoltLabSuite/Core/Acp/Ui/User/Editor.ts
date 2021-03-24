/**
 * User editing capabilities for the user list.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Editor
 * @since       3.1
 */

import AcpUserContentRemoveHandler from "./Content/Remove/Handler";
import * as Ajax from "../../../Ajax";
import * as Core from "../../../Core";
import * as EventHandler from "../../../Event/Handler";
import * as Language from "../../../Language";
import * as UiNotification from "../../../Ui/Notification";
import UiDropdownSimple from "../../../Ui/Dropdown/Simple";
import { AjaxCallbackObject, DatabaseObjectActionResponse } from "../../../Ajax/Data";
import DomUtil from "../../../Dom/Util";

interface RefreshUsersData {
  userIds: number[];
}

class AcpUiUserEditor {
  /**
   * Initializes the edit dropdown for each user.
   */
  constructor() {
    document.querySelectorAll(".jsUserRow").forEach((userRow: HTMLTableRowElement) => this.initUser(userRow));

    EventHandler.add("com.woltlab.wcf.acp.user", "refresh", (data: RefreshUsersData) => this.refreshUsers(data));
  }

  /**
   * Initializes the edit dropdown for a user.
   */
  private initUser(userRow: HTMLTableRowElement): void {
    const userId = ~~userRow.dataset.objectId!;
    const dropdownId = `userListDropdown${userId}`;
    const dropdownMenu = UiDropdownSimple.getDropdownMenu(dropdownId)!;
    const legacyButtonContainer = userRow.querySelector(".jsLegacyButtons") as HTMLElement;

    if (dropdownMenu.childElementCount === 0 && legacyButtonContainer.childElementCount === 0) {
      const toggleButton = userRow.querySelector(".dropdownToggle") as HTMLAnchorElement;
      toggleButton.classList.add("disabled");

      return;
    }

    UiDropdownSimple.registerCallback(dropdownId, (identifier, action) => {
      if (action === "open") {
        this.rebuild(dropdownMenu, legacyButtonContainer);
      }
    });

    const editLink = dropdownMenu.querySelector(".jsEditLink") as HTMLAnchorElement;
    if (editLink !== null) {
      const toggleButton = userRow.querySelector(".dropdownToggle") as HTMLAnchorElement;
      toggleButton.addEventListener("dblclick", (event) => {
        event.preventDefault();

        editLink.click();
      });
    }

    const sendNewPassword = dropdownMenu.querySelector(".jsSendNewPassword") as HTMLAnchorElement;
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

    const deleteContent = dropdownMenu.querySelector(".jsDeleteContent") as HTMLAnchorElement;
    if (deleteContent !== null) {
      new AcpUserContentRemoveHandler(deleteContent, userId);
    }

    const toggleConfirmEmail = dropdownMenu.querySelector(".jsConfirmEmailToggle") as HTMLAnchorElement;
    if (toggleConfirmEmail !== null) {
      toggleConfirmEmail.addEventListener("click", (event) => {
        event.preventDefault();

        Ajax.api(
          {
            _ajaxSetup: () => {
              const isEmailConfirmed = Core.stringToBool(userRow.dataset.emailConfirmed!);

              return {
                data: {
                  actionName: (isEmailConfirmed ? "un" : "") + "confirmEmail",
                  className: "wcf\\data\\user\\UserAction",
                  objectIDs: [userId],
                },
              };
            },
          } as AjaxCallbackObject,
          undefined,
          (data: DatabaseObjectActionResponse) => {
            document.querySelectorAll(".jsUserRow").forEach((userRow: HTMLTableRowElement) => {
              const userId = ~~userRow.dataset.objectId!;
              if (data.objectIDs.includes(userId)) {
                const confirmEmailButton = dropdownMenu.querySelector(".jsConfirmEmailToggle") as HTMLAnchorElement;

                switch (data.actionName) {
                  case "confirmEmail":
                    userRow.dataset.emailConfirmed = "true";
                    confirmEmailButton.textContent = confirmEmailButton.dataset.unconfirmEmailMessage!;
                    break;

                  case "unconfirmEmail":
                    userRow.dataset.emailEonfirmed = "false";
                    confirmEmailButton.textContent = confirmEmailButton.dataset.confirmEmailMessage!;
                    break;

                  default:
                    throw new Error("Unreachable");
                }
              }
            });

            UiNotification.show();
          },
        );
      });
    }
  }

  /**
   * Rebuilds the dropdown by adding wrapper links for legacy buttons,
   * that will eventually receive the click event.
   */
  private rebuild(dropdownMenu: HTMLElement, legacyButtonContainer: HTMLElement): void {
    dropdownMenu.querySelectorAll(".jsLegacyItem").forEach((element) => element.remove());

    // inject buttons
    const items: HTMLLIElement[] = [];
    let deleteButton: HTMLAnchorElement | null = null;
    Array.from(legacyButtonContainer.children).forEach((button: HTMLAnchorElement) => {
      if (button.classList.contains("jsObjectAction") && button.dataset.objectAction === "delete") {
        deleteButton = button;

        return;
      }

      const item = document.createElement("li");
      item.className = "jsLegacyItem";
      item.innerHTML = '<a href="#"></a>';

      const link = item.children[0] as HTMLAnchorElement;
      link.textContent = button.dataset.tooltip || button.title;
      link.addEventListener("click", (event) => {
        event.preventDefault();

        // forward click onto original button
        if (button.nodeName === "A") {
          button.click();
        } else {
          Core.triggerEvent(button, "click");
        }
      });

      items.push(item);
    });

    items.forEach((item) => {
      dropdownMenu.insertAdjacentElement("afterbegin", item);
    });

    if (deleteButton !== null) {
      const dispatchDeleteButton = dropdownMenu.querySelector(".jsDispatchDelete") as HTMLAnchorElement;
      dispatchDeleteButton.addEventListener("click", (event) => {
        event.preventDefault();

        deleteButton!.click();
      });
    }

    // check if there are visible items before each divider
    const listItems = Array.from(dropdownMenu.children) as HTMLElement[];
    listItems.forEach((element) => DomUtil.show(element));

    let hasItem = false;
    listItems.forEach((item) => {
      if (item.classList.contains("dropdownDivider")) {
        if (!hasItem) {
          DomUtil.hide(item);
        }
      } else {
        hasItem = true;
      }
    });
  }

  private refreshUsers(data: RefreshUsersData): void {
    document.querySelectorAll(".jsUserRow").forEach((userRow: HTMLTableRowElement) => {
      const userId = ~~userRow.dataset.objectId!;
      if (data.userIds.includes(userId)) {
        const userStatusIcons = userRow.querySelector(".userStatusIcons") as HTMLElement;

        const banned = Core.stringToBool(userRow.dataset.banned!);
        let iconBanned = userRow.querySelector(".jsUserStatusBanned") as HTMLElement;
        if (banned && iconBanned === null) {
          iconBanned = document.createElement("span");
          iconBanned.className = "icon icon16 fa-lock jsUserStatusBanned jsTooltip";
          iconBanned.title = Language.get("wcf.user.status.banned");

          userStatusIcons.appendChild(iconBanned);
        } else if (!banned && iconBanned !== null) {
          iconBanned.remove();
        }

        const isDisabled = !Core.stringToBool(userRow.dataset.enabled!);
        let iconIsDisabled = userRow.querySelector(".jsUserStatusIsDisabled") as HTMLElement;
        if (isDisabled && iconIsDisabled === null) {
          iconIsDisabled = document.createElement("span");
          iconIsDisabled.className = "icon icon16 fa-power-off jsUserStatusIsDisabled jsTooltip";
          iconIsDisabled.title = Language.get("wcf.user.status.isDisabled");
          userStatusIcons.appendChild(iconIsDisabled);
        } else if (!isDisabled && iconIsDisabled !== null) {
          iconIsDisabled.remove();
        }
      }
    });
  }
}

export = AcpUiUserEditor;
