/**
 * Handles the object watch button.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import * as EventHandler from "WoltLabSuite/Core/Event/Handler";
import { subscribe } from "WoltLabSuite/Core/Api/Users/ObjectWatches/Subscribe";
import { unsubscribe } from "WoltLabSuite/Core/Api/Users/ObjectWatches/Unsubscribe";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { showDefaultSuccessSnackbar } from "../Snackbar";

async function setSubscription(dropdown: HTMLElement, isSubscribed: boolean): Promise<void> {
  const objectType = dropdown.dataset.objectType!;
  const objectID = parseInt(dropdown.dataset.objectId!, 10);

  if (isSubscribed) {
    await subscribe(objectType, objectID, true);
  } else {
    await unsubscribe(objectType, objectID);
  }

  updateDropdowns(objectType, objectID, isSubscribed);
  updateButtons(objectType, objectID, isSubscribed);

  EventHandler.fire("com.woltlab.wcf.objectWatch", "updatedSubscription");
  showDefaultSuccessSnackbar();
}

function updateDropdowns(objectType: string, objectID: number, isSubscribed: boolean): void {
  document
    .querySelectorAll<HTMLElement>(
      `.userObjectWatchDropdown[data-object-type="${objectType}"][data-object-id="${objectID}"] .userObjectWatchSelect`,
    )
    .forEach((element) => {
      element.classList.toggle("active", (element.dataset.subscribe === "1") === isSubscribed);
    });
}

function updateButtons(objectType: string, objectID: number, isSubscribed: boolean): void {
  document
    .querySelectorAll<HTMLElement>(
      `.userObjectWatchDropdownToggle[data-object-type="${objectType}"][data-object-id="${objectID}"]`,
    )
    .forEach((element) => {
      const icon = element.querySelector("fa-icon")!;
      const label = element.querySelector("span:not(.icon)")!;

      element.classList.toggle("active", isSubscribed);
      icon.setIcon("bookmark", isSubscribed);
      label.textContent = getPhrase(
        isSubscribed ? "wcf.user.objectWatch.button.subscribed" : "wcf.user.objectWatch.button.subscribe",
      );

      element.dataset.isSubscribed = isSubscribed ? "1" : "0";
    });
}

let isSetupCompleted = false;

export function setup(): void {
  if (isSetupCompleted) {
    return;
  }
  isSetupCompleted = true;

  wheneverFirstSeen(".userObjectWatchDropdown", (dropdown) => {
    if (!dropdown.dataset.objectId) {
      throw new Error("Missing objectId for '.userObjectWatchDropdown' element.");
    }
    if (!dropdown.dataset.objectType) {
      throw new Error("Missing objectType for '.userObjectWatchDropdown' element.");
    }

    const setSubscriptionOnce = promiseMutex((isSubscribed: boolean) => setSubscription(dropdown, isSubscribed));

    dropdown.querySelectorAll<HTMLElement>(".userObjectWatchSelect").forEach((element) => {
      if (!element.dataset.subscribe) {
        throw new Error("Missing 'data-subscribe' attribute for '.userObjectWatchSelect' element.");
      }

      const isSubscribed = element.dataset.subscribe === "1";

      element.addEventListener("click", (event) => {
        event.preventDefault();

        setSubscriptionOnce(isSubscribed);
      });
    });
  });
}
