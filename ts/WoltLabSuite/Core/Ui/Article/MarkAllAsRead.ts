/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../Ajax/Data";
import * as UiNotification from "../Notification";

class UiArticleMarkAllAsRead implements AjaxCallbackObject {
  constructor() {
    document.querySelectorAll(".markAllAsReadButton").forEach((button) => {
      button.addEventListener("click", this.click.bind(this));
    });
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    Ajax.api(this);
  }

  _ajaxSuccess(): void {
    /* remove obsolete badges */
    // main menu
    document.querySelectorAll(".mainMenu .active .badge").forEach((badge) => badge.remove());
    // mobile page menu badge
    document.querySelectorAll(".pageMainMenuMobile .active").forEach((container) => {
      container.closest(".menuOverlayItem")?.querySelector(".badge")?.remove();
    });

    // article list
    document.querySelectorAll(".contentItemList .contentItemBadgeNew").forEach((el) => el.remove());

    UiNotification.show();
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "markAllAsRead",
        className: "wcf\\data\\article\\ArticleAction",
      },
    };
  }
}

export function init(): void {
  new UiArticleMarkAllAsRead();
}
