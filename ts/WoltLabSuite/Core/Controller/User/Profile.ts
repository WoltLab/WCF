/**
 * Handles user profile functionalities.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Controller/User/Profile
 */

import { UserList } from "../../Component/User/List";

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

export function setup(userId: number): void {
  setupFollowingList(userId);
  setupFollowerList(userId);
  setupVisitorList(userId);
}
