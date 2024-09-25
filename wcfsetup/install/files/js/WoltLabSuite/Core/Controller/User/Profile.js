/**
 * Handles user profile functionalities.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "../../Component/User/List"], function (require, exports, List_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setupUserList(userId, buttonId, className) {
        const button = document.getElementById(buttonId);
        if (button) {
            let userList;
            button.addEventListener("click", () => {
                if (userList === undefined) {
                    userList = new List_1.UserList({
                        className: className,
                        parameters: {
                            userID: userId,
                        },
                    }, button.dataset.dialogTitle);
                }
                userList.open();
            });
        }
    }
    function setupFollowingList(userId) {
        setupUserList(userId, "followingAll", "wcf\\data\\user\\follow\\UserFollowingAction");
    }
    function setupFollowerList(userId) {
        setupUserList(userId, "followerAll", "wcf\\data\\user\\follow\\UserFollowAction");
    }
    function setupVisitorList(userId) {
        setupUserList(userId, "visitorAll", "wcf\\data\\user\\profile\\visitor\\UserProfileVisitorAction");
    }
    function setup(userId) {
        setupFollowingList(userId);
        setupFollowerList(userId);
        setupVisitorList(userId);
    }
});
