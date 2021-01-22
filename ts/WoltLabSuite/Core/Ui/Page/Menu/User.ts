/**
 * Provides the touch-friendly fullscreen user menu.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Menu/User
 */

import * as Core from "../../../Core";
import * as EventHandler from "../../../Event/Handler";
import * as Language from "../../../Language";
import UiPageMenuAbstract from "./Abstract";

interface EventPayload {
  count: number;
  identifier: string;
}

class UiPageMenuUser extends UiPageMenuAbstract {
  /**
   * Initializes the touch-friendly fullscreen user menu.
   */
  constructor() {
    // check if user menu is actually empty
    const menu = document.querySelector("#pageUserMenuMobile > .menuOverlayItemList")!;
    if (menu.childElementCount === 1 && menu.children[0].classList.contains("menuOverlayTitle")) {
      const userPanel = document.querySelector("#pageHeader .userPanel")!;
      userPanel.classList.add("hideUserPanel");
      return;
    }

    super("com.woltlab.wcf.UserMenuMobile", "pageUserMenuMobile", "#pageHeader .userPanel");

    EventHandler.add("com.woltlab.wcf.userMenu", "updateBadge", (data) => this.updateBadge(data));

    this.button.setAttribute("aria-label", Language.get("wcf.menu.user"));
    this.button.setAttribute("role", "button");
  }

  close(event?: Event): boolean {
    // The user menu is not initialized if there are no items to display.
    if (this.menu === undefined) {
      return false;
    }

    const dropdown = window.WCF.Dropdown.Interactive.Handler.getOpenDropdown();
    if (dropdown) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }

      dropdown.close();

      return true;
    }

    return super.close(event);
  }

  private updateBadge(data: EventPayload): void {
    this.menu.querySelectorAll(".menuOverlayItemBadge").forEach((item: HTMLElement) => {
      if (item.dataset.badgeIdentifier === data.identifier) {
        let badge = item.querySelector(".badge");
        if (data.count) {
          if (badge === null) {
            badge = document.createElement("span");
            badge.className = "badge badgeUpdate";
            item.appendChild(badge);
          }

          badge.textContent = data.count.toString();
        } else if (badge !== null) {
          badge.remove();
        }

        this.updateButtonState();
      }
    });
  }
}

Core.enableLegacyInheritance(UiPageMenuUser);

export = UiPageMenuUser;
