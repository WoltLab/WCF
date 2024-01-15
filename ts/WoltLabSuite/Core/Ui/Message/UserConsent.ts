/**
 * Prompts the user for their consent before displaying external media.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import User from "../../User";

class UserConsent {
  private enableAll = false;

  constructor() {
    if (window.sessionStorage.getItem(`${Core.getStoragePrefix()}user-consent`) === "all") {
      this.enableAll = true;
    }

    this.registerEventListeners();

    DomChangeListener.add("WoltLabSuite/Core/Ui/Message/UserConsent", () => this.registerEventListeners());
  }

  private registerEventListeners(): void {
    if (this.enableAll) {
      this.enableAllExternalMedia();
    } else {
      wheneverFirstSeen(".jsButtonMessageUserConsentEnable", (button) => {
        button.addEventListener("click", (event) => this.click(event));
      });
    }
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    this.enableAll = true;

    this.enableAllExternalMedia();

    if (User.userId) {
      Ajax.apiOnce({
        data: {
          actionName: "saveUserConsent",
          className: "wcf\\data\\user\\UserAction",
        },
        silent: true,
      });
    } else {
      window.sessionStorage.setItem(`${Core.getStoragePrefix()}user-consent`, "all");
    }
  }

  private enableExternalMedia(container: HTMLElement): void {
    if (container.dataset.target) {
      document.getElementById(container.dataset.target)!.hidden = false;
    } else {
      const payload = atob(container.dataset.payload!);
      DomUtil.insertHtml(payload, container, "before");
    }

    container.remove();
  }

  private enableAllExternalMedia(): void {
    document.querySelectorAll(".messageUserConsent").forEach((el: HTMLElement) => this.enableExternalMedia(el));
  }
}

let userConsent: UserConsent;

export function init(): void {
  if (!userConsent) {
    userConsent = new UserConsent();
  }
}
