/**
 * Handles the 'mark as read' action for articles.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Article/MarkAllAsRead
 */

import * as Ajax from "../../Ajax";
import { AjaxCallbackObject, CallbackSetup } from "../../Ajax/Data";

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
    const badge = document.querySelector(".mainMenu .active .badge");
    if (badge) badge.remove();

    // article list
    document.querySelectorAll(".articleList .newMessageBadge").forEach((el) => el.remove());
  }

  _ajaxSetup(): ReturnType<CallbackSetup> {
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
