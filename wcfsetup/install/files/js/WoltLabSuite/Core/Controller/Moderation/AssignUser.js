define(["require", "exports", "tslib", "../../Ajax/Backend", "../../Component/Dialog", "../../Form/Builder/Manager"], function (require, exports, tslib_1, Backend_1, Dialog_1, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    function setup(button) {
        button.addEventListener("click", async () => {
            const json = (await (0, Backend_1.prepareRequest)(button.dataset.url).get().fetchAsJson());
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(json.dialog).asPrompt();
            dialog.addEventListener("primary", async () => {
                const data = await FormBuilderManager.getData(json.formId);
                const _response = await (0, Backend_1.prepareRequest)(button.dataset.url).post(data).fetchAsJson();
                // TODO: Show success / update UI
                // TODO: Handle incorrect form inputs
            });
            dialog.addEventListener("cancel", () => {
                if (FormBuilderManager.hasForm(json.formId)) {
                    FormBuilderManager.unregisterForm(json.formId);
                }
            });
            dialog.show("yadayada");
        });
    }
    exports.setup = setup;
});
