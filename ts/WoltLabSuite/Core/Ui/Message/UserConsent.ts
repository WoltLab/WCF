/**
 * Prompts the user for their consent before displaying external media.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Message/UserConsent
 */

import * as Ajax from "../../Ajax";
import * as Core from "../../Core";
import DomChangeListener from "../../Dom/Change/Listener";
import DomUtil from "../../Dom/Util";
import User from "../../User";

class UserConsent {
  private enableAll = false;
  private readonly knownButtons = new WeakSet();

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
      document.querySelectorAll(".jsButtonMessageUserConsentEnable").forEach((button: HTMLAnchorElement) => {
        if (!this.knownButtons.has(button)) {
          this.knownButtons.add(button);

          button.addEventListener("click", (ev) => this.click(ev));
        }
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
    const payload = atob(container.dataset.payload!);

    DomUtil.insertHtml(payload, container, "before");
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
