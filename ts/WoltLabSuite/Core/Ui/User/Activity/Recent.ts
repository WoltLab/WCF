/**
 * @woltlabExcludeBundle all
 * @deprecated 6.1 use `WoltLabSuite/Core/Components/User/RecentActivity/Loader` instead
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../Ajax/Data";
import * as Core from "../../../Core";
import * as Language from "../../../Language";
import DomUtil from "../../../Dom/Util";

interface AjaxResponse {
  returnValues: {
    lastEventID: number;
    lastEventTime: number;
    template?: string;
  };
}

class UiUserActivityRecent implements AjaxCallbackObject {
  private readonly containerId: string;
  private readonly list: HTMLUListElement;
  private readonly showMoreItem: HTMLLIElement;

  constructor(containerId: string) {
    this.containerId = containerId;
    const container = document.getElementById(this.containerId)!;
    this.list = container.querySelector(".recentActivityList") as HTMLUListElement;

    const showMoreItem = document.createElement("li");
    showMoreItem.className = "showMore";
    if (this.list.childElementCount) {
      showMoreItem.innerHTML =
        '<button type="button" class="button small">' + Language.get("wcf.user.recentActivity.more") + "</button>";

      const button = showMoreItem.children[0] as HTMLButtonElement;
      button.addEventListener("click", (ev) => this.showMore(ev));
    } else {
      showMoreItem.innerHTML = "<small>" + Language.get("wcf.user.recentActivity.noMoreEntries") + "</small>";
    }

    this.list.appendChild(showMoreItem);
    this.showMoreItem = showMoreItem;

    container.querySelectorAll(".jsRecentActivitySwitchContext .button").forEach((button) => {
      button.addEventListener("click", (event) => {
        event.preventDefault();

        if (!button.classList.contains("active")) {
          this.switchContext();
        }
      });
    });
  }

  private showMore(event: MouseEvent): void {
    event.preventDefault();

    const button = this.showMoreItem.children[0] as HTMLButtonElement;
    button.disabled = true;

    Ajax.api(this, {
      actionName: "load",
      parameters: {
        boxID: ~~this.list.dataset.boxId!,
        filteredByFollowedUsers: Core.stringToBool(this.list.dataset.filteredByFollowedUsers || ""),
        lastEventId: this.list.dataset.lastEventId!,
        lastEventTime: this.list.dataset.lastEventTime!,
        userID: ~~this.list.dataset.userId!,
      },
    });
  }

  private switchContext(): void {
    Ajax.api(
      this,
      {
        actionName: "switchContext",
      },
      () => {
        window.location.hash = `#${this.containerId}`;
        window.location.reload();
      },
    );
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (data.returnValues.template) {
      DomUtil.insertHtml(data.returnValues.template, this.showMoreItem, "before");

      this.list.dataset.lastEventTime = data.returnValues.lastEventTime.toString();
      this.list.dataset.lastEventId = data.returnValues.lastEventID.toString();

      const button = this.showMoreItem.children[0] as HTMLButtonElement;
      button.disabled = false;
    } else {
      this.showMoreItem.innerHTML = "<small>" + Language.get("wcf.user.recentActivity.noMoreEntries") + "</small>";
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        className: "wcf\\data\\user\\activity\\event\\UserActivityEventAction",
      },
    };
  }
}

export = UiUserActivityRecent;
