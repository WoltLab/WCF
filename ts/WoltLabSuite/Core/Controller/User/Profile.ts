/**
 * Handles user profile functionalities.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as EventHandler from "WoltLabSuite/Core/Event/Handler";
import { insertHtml } from "WoltLabSuite/Core/Dom/Util";
import { trigger as triggerDomChange } from "WoltLabSuite/Core/Dom/Change/Listener";
import { getTabMenu } from "WoltLabSuite/Core/Ui/TabMenu";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { renderUserProfileMenuContent } from "WoltLabSuite/Core/Api/Users/RenderUserProfileMenuContent";
import { setup as setupProfileEditor } from "WoltLabSuite/Core/Component/User/Profile/Editor";

function setupUserList(userId: number, buttonId: string, className: string): void {
  const button = document.getElementById(buttonId) as HTMLElement;
  if (!button) {
    return;
  }

  button.addEventListener(
    "click",
    promiseMutex(() => {
      return dialogFactory()
        .usingListView()
        .fromPreset(button.dataset.dialogTitle!, className, new Map([["userID", userId.toString()]]));
    }),
  );
}

function setupFollowingList(userId: number): void {
  setupUserList(userId, "followingAll", "wcf\\system\\listView\\user\\FollowingListView");
}

function setupFollowerList(userId: number): void {
  setupUserList(userId, "followerAll", "wcf\\system\\listView\\user\\FollowerListView");
}

function setupVisitorList(userId: number): void {
  setupUserList(userId, "visitorAll", "wcf\\system\\listView\\user\\UserProfileVisitorListView");
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

async function loadTabMenuContent(userId: number, tabName: string): Promise<void> {
  if (tabContentLoaded.has(tabName)) {
    return;
  }

  const response = await renderUserProfileMenuContent(userId, tabName);

  tabContentLoaded.set(tabName, true);

  insertHtml(response.template, document.querySelector('.tabMenuContent[data-name="' + tabName + '"]')!, "append");
  triggerDomChange();
}

/**
 * Replaces the content of the given tab with its current content.
 */
async function reloadTabMenuContent(userId: number, tabName: string): Promise<void> {
  const container = document.querySelector<HTMLElement>('.tabMenuContent[data-name="' + tabName + '"]');
  if (container === null) {
    return;
  }

  tabContentLoaded.delete(tabName);
  container.innerHTML = "";

  await loadTabMenuContent(userId, tabName);
}

function setupEditor(userId: number, editOnInit: boolean): void {
  const button = document.querySelector<HTMLElement>(".jsButtonEditProfile");
  if (button === null) {
    return;
  }

  setupProfileEditor(button, editOnInit, async () => {
    // Reload the tab before selecting it, otherwise the select event would load
    // the content of a tab that has not been loaded yet a second time.
    await reloadTabMenuContent(userId, "about");
    getTabMenu("profileContent")?.select("about");
  });
}

export function setup(userId: number, editOnInit: boolean = false): void {
  setupFollowingList(userId);
  setupFollowerList(userId);
  setupVisitorList(userId);
  setupTabMenu(userId);
  setupEditor(userId, editOnInit);
}
