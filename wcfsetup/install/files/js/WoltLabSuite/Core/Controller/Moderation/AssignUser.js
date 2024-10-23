/**
 * Assign a user to a moderation queue.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "../../Component/Dialog", "../../Language", "../../Ui/Notification"], function (require, exports, PromiseMutex_1, Dialog_1, Language_1, Notification_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function showDialog(url) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url);
        if (ok) {
            updateAssignee(result.assignee);
            updateStatus(result.status);
            (0, Notification_1.show)();
        }
    }
    function updateAssignee(assignee) {
        const span = document.getElementById("moderationAssignedUser");
        if (assignee === null) {
            span.textContent = (0, Language_1.getPhrase)("wcf.moderation.assignedUser.nobody");
        }
        else {
            const link = document.createElement("a");
            link.href = assignee.link;
            link.dataset.objectId = assignee.userID.toString();
            link.classList.add("userLink");
            link.innerHTML = assignee.username;
            span.innerHTML = "";
            span.append(link);
        }
    }
    function updateStatus(status) {
        document.getElementById("moderationQueueStatus").textContent = status;
    }
    function setup(button) {
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => showDialog(button.dataset.url)));
    }
});
