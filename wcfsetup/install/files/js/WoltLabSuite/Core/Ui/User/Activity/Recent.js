/**
 * @woltlabExcludeBundle all
 * @deprecated 6.1 use `WoltLabSuite/Core/Components/User/RecentActivity/Loader` instead
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Language", "../../../Dom/Util"], function (require, exports, tslib_1, Ajax, Core, Language, Util_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    class UiUserActivityRecent {
        containerId;
        list;
        showMoreItem;
        constructor(containerId) {
            this.containerId = containerId;
            const container = document.getElementById(this.containerId);
            this.list = container.querySelector(".recentActivityList");
            const showMoreItem = document.createElement("li");
            showMoreItem.className = "showMore";
            if (this.list.childElementCount) {
                showMoreItem.innerHTML =
                    '<button type="button" class="button small">' + Language.get("wcf.user.recentActivity.more") + "</button>";
                const button = showMoreItem.children[0];
                button.addEventListener("click", (ev) => this.showMore(ev));
            }
            else {
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
        showMore(event) {
            event.preventDefault();
            const button = this.showMoreItem.children[0];
            button.disabled = true;
            Ajax.api(this, {
                actionName: "load",
                parameters: {
                    boxID: ~~this.list.dataset.boxId,
                    filteredByFollowedUsers: Core.stringToBool(this.list.dataset.filteredByFollowedUsers || ""),
                    lastEventId: this.list.dataset.lastEventId,
                    lastEventTime: this.list.dataset.lastEventTime,
                    userID: ~~this.list.dataset.userId,
                },
            });
        }
        switchContext() {
            Ajax.api(this, {
                actionName: "switchContext",
            }, () => {
                window.location.hash = `#${this.containerId}`;
                window.location.reload();
            });
        }
        _ajaxSuccess(data) {
            if (data.returnValues.template) {
                Util_1.default.insertHtml(data.returnValues.template, this.showMoreItem, "before");
                this.list.dataset.lastEventTime = data.returnValues.lastEventTime.toString();
                this.list.dataset.lastEventId = data.returnValues.lastEventID.toString();
                const button = this.showMoreItem.children[0];
                button.disabled = false;
            }
            else {
                this.showMoreItem.innerHTML = "<small>" + Language.get("wcf.user.recentActivity.noMoreEntries") + "</small>";
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\user\\activity\\event\\UserActivityEventAction",
                },
            };
        }
    }
    return UiUserActivityRecent;
});
