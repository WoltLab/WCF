/**
 * Handles the list of recent activities.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Core", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language"], function (require, exports, tslib_1, Ajax_1, Core_1, Util_1, PromiseMutex_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    async function loadMore(container) {
        const response = (await (0, Ajax_1.dboAction)("load", "wcf\\data\\user\\activity\\event\\UserActivityEventAction")
            .payload({
            lastEventTime: container.dataset.lastEventTime,
            lastEventID: container.dataset.lastEventId || 0,
            userID: container.dataset.userId || 0,
            boxID: container.dataset.boxId || 0,
            filteredByFollowedUsers: (0, Core_1.stringToBool)(container.dataset.filteredByFollowedUsers || ""),
        })
            .dispatch());
        if (response.template) {
            container.dataset.lastEventTime = response.lastEventTime.toString();
            container.dataset.lastEventId = response.lastEventID.toString();
            const fragment = Util_1.default.createFragmentFromHtml(response.template);
            container.insertBefore(fragment, container.querySelector(".recentActivityList__showMoreButton"));
        }
        else {
            container.querySelector(".recentActivityList__showMoreButton")?.remove();
            showNoMoreEntries(container);
        }
    }
    function showNoMoreEntries(container) {
        const div = document.createElement("div");
        div.classList.add("recentActivityList__showMoreButton");
        container.append(div);
        const small = document.createElement("small");
        small.textContent = (0, Language_1.getPhrase)("wcf.user.recentActivity.noMoreEntries");
        div.append(small);
    }
    function initShowMoreButton(container) {
        if (container.querySelector(".recentActivityList__showMoreButton")) {
            return;
        }
        const div = document.createElement("div");
        div.classList.add("recentActivityList__showMoreButton");
        container.append(div);
        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small");
        button.textContent = (0, Language_1.getPhrase)("wcf.user.recentActivity.more");
        div.append(button);
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => loadMore(container)));
    }
    function initSwitchContextButtons(container) {
        container.querySelectorAll(".recentActivityList__switchContextButton").forEach((button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => switchContext(container)));
        });
    }
    async function switchContext(container) {
        await (0, Ajax_1.dboAction)("switchContext", "wcf\\data\\user\\activity\\event\\UserActivityEventAction").dispatch();
        window.location.hash = `#${container.id}`;
        window.location.reload();
    }
    function setup(container) {
        initShowMoreButton(container);
        initSwitchContextButtons(container);
    }
    exports.setup = setup;
});
