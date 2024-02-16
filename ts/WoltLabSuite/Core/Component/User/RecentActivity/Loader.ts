/**
 * Handles the list of recent activities.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { dboAction } from "WoltLabSuite/Core/Ajax";
import { stringToBool } from "WoltLabSuite/Core/Core";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getPhrase } from "WoltLabSuite/Core/Language";

type ResponseLoadMore = {
  lastEventID: number;
  lastEventTime: number;
  template: string;
};

async function loadMore(container: HTMLElement): Promise<void> {
  const response = (await dboAction("load", "wcf\\data\\user\\activity\\event\\UserActivityEventAction")
    .payload({
      lastEventTime: container.dataset.lastEventTime,
      lastEventID: container.dataset.lastEventId || 0,
      userID: container.dataset.userId || 0,
      boxID: container.dataset.boxId || 0,
      filteredByFollowedUsers: stringToBool(container.dataset.filteredByFollowedUsers || ""),
    })
    .dispatch()) as ResponseLoadMore;

  if (response.template) {
    container.dataset.lastEventTime = response.lastEventTime.toString();
    container.dataset.lastEventId = response.lastEventID.toString();

    const fragment = DomUtil.createFragmentFromHtml(response.template);
    container.insertBefore(fragment, container.querySelector(".recentActivityList__showMoreButton"));
  } else {
    container.querySelector(".recentActivityList__showMoreButton")?.remove();
    showNoMoreEntries(container);
  }
}

function showNoMoreEntries(container: HTMLElement): void {
  const div = document.createElement("div");
  div.classList.add("recentActivityList__showMoreButton");
  container.append(div);

  const small = document.createElement("small");
  small.textContent = getPhrase("wcf.user.recentActivity.noMoreEntries");
  div.append(small);
}

function initShowMoreButton(container: HTMLElement): void {
  if (container.querySelector(".recentActivityList__showMoreButton")) {
    return;
  }

  const div = document.createElement("div");
  div.classList.add("recentActivityList__showMoreButton");
  container.append(div);

  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("button", "small");
  button.textContent = getPhrase("wcf.user.recentActivity.more");
  div.append(button);

  button.addEventListener(
    "click",
    promiseMutex(() => loadMore(container)),
  );
}

function initSwitchContextButtons(container: HTMLElement): void {
  container.querySelectorAll(".recentActivityList__switchContextButton").forEach((button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => switchContext(container)),
    );
  });
}

async function switchContext(container: HTMLElement): Promise<void> {
  await dboAction("switchContext", "wcf\\data\\user\\activity\\event\\UserActivityEventAction").dispatch();

  window.location.hash = `#${container.id}`;
  window.location.reload();
}

export function setup(container: HTMLElement): void {
  initShowMoreButton(container);
  initSwitchContextButtons(container);
}
