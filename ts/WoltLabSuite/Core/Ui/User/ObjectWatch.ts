/**
 * Handles the object watch button.
 *
 * @author	Marcel Werk
 * @copyright	2001-2022 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import * as Ajax from "../../Ajax";
import * as UiNotification from "../Notification";
import * as Language from "../../Language";
import * as EventHandler from "../../Event/Handler";

const dropdowns = new Map<number, Set<HTMLElement>>();

async function click(element: HTMLElement): Promise<void> {
  const dropdown = element.closest(".userObjectWatchDropdown") as HTMLElement;
  const subscribe = parseInt(element.dataset.subscribe!, 10);
  const objectID = parseInt(dropdown.dataset.objectId!, 10);
  const objectType = dropdown.dataset.objectType!;

  await Ajax.dboAction("saveSubscription", "wcf\\data\\user\\object\\watch\\UserObjectWatchAction")
    .payload({
      enableNotification: 1,
      objectID,
      objectType,
      subscribe,
    })
    .dispatch();

  if (dropdowns.has(objectID)) {
    dropdowns.get(objectID)!.forEach((element) => {
      element.querySelectorAll(".userObjectWatchSelect").forEach((li: HTMLElement) => {
        if (parseInt(li.dataset.subscribe!, 10) === subscribe) {
          li.classList.add("active");
        } else {
          li.classList.remove("active");
        }
      });
    });
  }

  document
    .querySelectorAll(`.userObjectWatchDropdownToggle[data-object-type="${objectType}"][data-object-id="${objectID}"]`)
    .forEach((element: HTMLElement) => {
      const icon = element.querySelector("fa-icon")!;
      const label = element.querySelector("span:not(.icon)")!;

      if (subscribe) {
        element.classList.add("active");
        icon.setIcon("bookmark", true);
        label.textContent = Language.get(`wcf.user.objectWatch.button.subscribed`);
      } else {
        element.classList.remove("active");
        icon.setIcon("bookmark");
        label.textContent = Language.get("wcf.user.objectWatch.button.subscribe");
      }

      element.dataset.isSubscribed = subscribe.toString();
    });

  EventHandler.fire("com.woltlab.wcf.objectWatch", "updatedSubscription");
  UiNotification.show();
}

export function setup(): void {
  document.querySelectorAll(".userObjectWatchDropdown").forEach((element: HTMLElement) => {
    if (!element.dataset.objectId) {
      throw new Error("Missing objectId for '.userObjectWatchDropdown' element.");
    }

    const objectId = parseInt(element.dataset.objectId, 10);

    if (!dropdowns.has(objectId)) {
      dropdowns.set(objectId, new Set());
    }

    dropdowns.get(objectId)!.add(element);

    element.querySelectorAll(".userObjectWatchSelect").forEach((element: HTMLElement) => {
      if (!element.dataset.subscribe) {
        throw new Error("Missing 'data-subscribe' attribute for '.userObjectWatchSelect' element.");
      }

      element.addEventListener("click", (event) => {
        event.preventDefault();
        void click(element);
      });
    });
  });
}
