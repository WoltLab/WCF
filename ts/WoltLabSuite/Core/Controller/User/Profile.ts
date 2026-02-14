/**
 * Handles user profile functionalities.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { dboAction } from "WoltLabSuite/Core/Ajax";
import { UserList } from "../../Component/User/List";
import * as EventHandler from "WoltLabSuite/Core/Event/Handler";
import { insertHtml } from "WoltLabSuite/Core/Dom/Util";
import { trigger as triggerDomChange } from "WoltLabSuite/Core/Dom/Change/Listener";
import { getTabMenu } from "WoltLabSuite/Core/Ui/TabMenu";

function setupUserList(userId: number, buttonId: string, className: string): void {
  const button = document.getElementById(buttonId) as HTMLElement;
  if (button) {
    let userList: UserList;

    button.addEventListener("click", () => {
      if (userList === undefined) {
        userList = new UserList(
          {
            className: className,
            parameters: {
              userID: userId,
            },
          },
          button.dataset.dialogTitle!,
        );
      }
      userList.open();
    });
  }
}

function setupFollowingList(userId: number): void {
  setupUserList(userId, "followingAll", "wcf\\data\\user\\follow\\UserFollowingAction");
}

function setupFollowerList(userId: number): void {
  setupUserList(userId, "followerAll", "wcf\\data\\user\\follow\\UserFollowAction");
}

function setupVisitorList(userId: number): void {
  setupUserList(userId, "visitorAll", "wcf\\data\\user\\profile\\visitor\\UserProfileVisitorAction");
}

const tabContentLoaded = new Map<string, boolean>();

function setupTabMenu(userId: number): void {
  const content = document.getElementById("profileContent");
  if (content === null) {
    // Profile is restricted and the current user cannot see the contents.
    return;
  }

  // Mark the default tab as loaded.
  tabContentLoaded.set(content.dataset.active!, true);

  // Load the content of the active tab, as we do not receive an event for it.
  void loadTabMenuContent(userId, getTabMenu("profileContent")!.getActiveTab().dataset.name!);

  EventHandler.add("com.woltlab.wcf.simpleTabMenu_profileContent", "select", (data) => {
    void loadTabMenuContent(userId, data.activeName);
  });
}

type ResponseGetTabContent = {
  template: string;
};

async function loadTabMenuContent(userId: number, tabName: string): Promise<void> {
  if (tabContentLoaded.has(tabName)) {
    return;
  }

  const response = (await dboAction("getContent", "wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction")
    .payload({
      data: {
        menuItem: tabName,
        userID: userId,
      },
    })
    .dispatch()) as ResponseGetTabContent;

  tabContentLoaded.set(tabName, true);

  insertHtml(response.template, document.querySelector('.tabMenuContent[data-name="' + tabName + '"]')!, "append");
  triggerDomChange();
}

export function setup(userId: number): void {
  setupFollowingList(userId);
  setupFollowerList(userId);
  setupVisitorList(userId);
  setupTabMenu(userId);
}
