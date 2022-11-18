define(["require", "exports", "tslib", "../../Ajax/Backend", "../../Component/Dialog", "../../Dom/Util", "../../Form/Builder/Manager"], function (require, exports, tslib_1, Backend_1, Dialog_1, Util_1, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    async function showDialog(url) {
        const json = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(json.dialog).asPrompt();
        dialog.addEventListener("validate", (event) => {
            const callback = FormBuilderManager.getData(json.formId).then(async (data) => {
                if (data instanceof Promise) {
                    data = await data;
                }
                const response = (await (0, Backend_1.prepareRequest)(url).post(data).fetchAsJson());
                if ("dialog" in response) {
                    Util_1.default.setInnerHtml(dialog.content, response.dialog);
                    return false;
                }
                else {
                    dialog.addEventListener("primary", () => {
                        if (FormBuilderManager.hasForm(json.formId)) {
                            FormBuilderManager.unregisterForm(json.formId);
                        }
                        updateAssignee(response.assignee);
                    }, { once: true });
                    return true;
                }
            });
            event.detail.push(callback);
        });
        dialog.addEventListener("cancel", () => {
            if (FormBuilderManager.hasForm(json.formId)) {
                FormBuilderManager.unregisterForm(json.formId);
            }
        });
        dialog.show("TODO: title");
    }
    function updateAssignee(assignee) {
        const span = document.getElementById("moderationAssignedUser");
        if (assignee === null) {
            span.textContent = "TODO: nobody";
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
