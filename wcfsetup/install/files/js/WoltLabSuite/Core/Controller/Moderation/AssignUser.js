define(["require", "exports", "../../Component/Dialog", "../../Language"], function (require, exports, Dialog_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    async function showDialog(url) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url);
        if (ok) {
            updateAssignee(result.assignee);
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
    function setup(button) {
        button.addEventListener("click", () => {
            void showDialog(button.dataset.url);
        });
    }
    exports.setup = setup;
});
