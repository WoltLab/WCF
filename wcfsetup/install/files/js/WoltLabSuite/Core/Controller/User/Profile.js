/**
 * Handles user profile functionalities.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Event/Handler", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Dom/Change/Listener", "WoltLabSuite/Core/Ui/TabMenu", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, tslib_1, Ajax_1, EventHandler, Util_1, Listener_1, TabMenu_1, PromiseMutex_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    EventHandler = tslib_1.__importStar(EventHandler);
    function setupUserList(userId, buttonId, className) {
        const button = document.getElementById(buttonId);
        if (!button) {
            return;
        }
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => {
            return (0, Dialog_1.dialogFactory)()
                .usingListView()
                .fromPreset(button.dataset.dialogTitle, className, new Map([["userID", userId.toString()]]));
        }));
    }
    function setupFollowingList(userId) {
        setupUserList(userId, "followingAll", "wcf\\system\\listView\\user\\FollowingListView");
    }
    function setupFollowerList(userId) {
        setupUserList(userId, "followerAll", "wcf\\system\\listView\\user\\FollowerListView");
    }
    function setupVisitorList(userId) {
        setupUserList(userId, "visitorAll", "wcf\\system\\listView\\user\\UserProfileVisitorListView");
    }
    const tabContentLoaded = new Map();
    function setupTabMenu(userId) {
        const content = document.getElementById("profileContent");
        if (content === null) {
            // Profile is restricted and the current user cannot see the contents.
            return;
        }
        // Mark the default tab as loaded.
        tabContentLoaded.set(content.dataset.active, true);
        // Load the content of the active tab, as we do not receive an event for it.
        void loadTabMenuContent(userId, (0, TabMenu_1.getTabMenu)("profileContent").getActiveTab().dataset.name);
        EventHandler.add("com.woltlab.wcf.simpleTabMenu_profileContent", "select", (data) => {
            void loadTabMenuContent(userId, data.activeName);
        });
    }
    async function loadTabMenuContent(userId, tabName) {
        if (tabContentLoaded.has(tabName)) {
            return;
        }
        const response = (await (0, Ajax_1.dboAction)("getContent", "wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction")
            .payload({
            data: {
                menuItem: tabName,
                userID: userId,
            },
        })
            .dispatch());
        tabContentLoaded.set(tabName, true);
        (0, Util_1.insertHtml)(response.template, document.querySelector('.tabMenuContent[data-name="' + tabName + '"]'), "append");
        (0, Listener_1.trigger)();
    }
    function setup(userId) {
        setupFollowingList(userId);
        setupFollowerList(userId);
        setupVisitorList(userId);
        setupTabMenu(userId);
    }
});
