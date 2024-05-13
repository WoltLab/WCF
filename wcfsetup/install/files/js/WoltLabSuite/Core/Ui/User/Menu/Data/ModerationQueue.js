/**
 * User menu for moderation queues.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../View", "../Manager"], function (require, exports, tslib_1, Ajax_1, View_1, Manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    View_1 = tslib_1.__importDefault(View_1);
    class UserMenuDataModerationQueue {
        button;
        counter = 0;
        options;
        stale = true;
        view = undefined;
        constructor(button, options) {
            this.button = button;
            this.options = options;
            const badge = button.querySelector(".badge");
            if (badge) {
                const counter = parseInt(badge.textContent.trim());
                if (counter) {
                    this.counter = counter;
                }
            }
            this.button.addEventListener("updateCounter", (event) => {
                this.updateCounter(event.detail.counter);
                this.stale = true;
            });
        }
        getPanelButton() {
            return this.button;
        }
        getMenuButtons() {
            return [
                {
                    icon: '<fa-icon size="24" name="trash-can"></fa-icon>',
                    link: this.options.deletedContentLink,
                    name: "deletedContent",
                    title: this.options.deletedContent,
                },
            ];
        }
        async getData() {
            const data = (await (0, Ajax_1.dboAction)("getModerationQueueData", "wcf\\data\\moderation\\queue\\ModerationQueueAction")
                .disableLoadingIndicator()
                .dispatch());
            this.updateCounter(data.totalCount);
            this.stale = false;
            return data.items;
        }
        getFooter() {
            return {
                link: this.options.showAllLink,
                title: this.options.showAllTitle,
            };
        }
        getTitle() {
            return this.options.title;
        }
        getView() {
            if (this.view === undefined) {
                this.view = new View_1.default(this);
            }
            return this.view;
        }
        getEmptyViewMessage() {
            return this.options.noItems;
        }
        isStale() {
            if (this.stale) {
                return true;
            }
            const unreadItems = this.getView()
                .getItems()
                .filter((item) => item.dataset.isUnread === "true");
            if (this.counter !== unreadItems.length) {
                return true;
            }
            return false;
        }
        getIdentifier() {
            return "com.woltlab.wcf.moderation";
        }
        hasPlainTitle() {
            return true;
        }
        hasUnreadContent() {
            return this.counter > 0;
        }
        async markAsRead(objectId) {
            const response = (await (0, Ajax_1.dboAction)("markAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction")
                .objectIds([objectId])
                .dispatch());
            this.updateCounter(response.totalCount);
        }
        async markAllAsRead() {
            await (0, Ajax_1.dboAction)("markAllAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction").dispatch();
            this.updateCounter(0);
        }
        updateCounter(counter) {
            let badge = this.button.querySelector(".badge");
            if (badge === null && counter > 0) {
                badge = document.createElement("span");
                badge.classList.add("badge", "badgeUpdate");
                this.button.querySelector("a").append(badge);
            }
            if (badge) {
                if (counter === 0) {
                    badge.remove();
                }
                else {
                    badge.textContent = counter.toString();
                }
            }
            this.counter = counter;
        }
    }
    let isInitialized = false;
    function setup(options) {
        if (!isInitialized) {
            const button = document.getElementById("outstandingModeration");
            if (button !== null) {
                const provider = new UserMenuDataModerationQueue(button, options);
                (0, Manager_1.registerProvider)(provider);
            }
            isInitialized = true;
        }
    }
    exports.setup = setup;
});
