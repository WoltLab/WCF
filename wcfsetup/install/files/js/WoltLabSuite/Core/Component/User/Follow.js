/**
 * Handles the user follow buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Language"], function (require, exports, Backend_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function toggleFollow(button) {
        if (button.dataset.following != "1") {
            button.dataset.following = "1";
            button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.unfollow");
            button.querySelector("fa-icon")?.setIcon("circle-minus");
            void (0, Backend_1.prepareRequest)(button.dataset.endpoint)
                .post({
                action: "follow",
            })
                .fetchAsResponse();
        }
        else {
            button.dataset.following = "0";
            button.dataset.tooltip = (0, Language_1.getPhrase)("wcf.user.button.follow");
            button.querySelector("fa-icon")?.setIcon("circle-plus");
            void (0, Backend_1.prepareRequest)(button.dataset.endpoint)
                .post({
                action: "unfollow",
            })
                .fetchAsResponse();
        }
    }
    function setup() {
        document.querySelectorAll(".jsFollowButton").forEach((button) => {
            button.addEventListener("click", () => {
                toggleFollow(button);
            });
        });
    }
    exports.setup = setup;
});
