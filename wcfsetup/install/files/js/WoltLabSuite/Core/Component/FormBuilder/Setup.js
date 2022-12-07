define(["require", "exports", "tslib", "../../Ajax/Backend", "../../Dom/Util", "../../Form/Builder/Manager"], function (require, exports, tslib_1, Backend_1, DomUtil, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FormBuilderSetup = void 0;
    DomUtil = tslib_1.__importStar(DomUtil);
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    class FormBuilderSetup {
        #endpoint;
        constructor(endpoint) {
            this.#endpoint = endpoint;
        }
        andWhenCompleted(callback) {
            void this.#dispatch(callback);
        }
        async #dispatch(callback) {
            const json = (await (0, Backend_1.prepareRequest)(this.#endpoint).get().fetchAsJson());
            // Prevents a circular dependency.
            const { dialogFactory } = await new Promise((resolve_1, reject_1) => { require(["../Dialog"], resolve_1, reject_1); }).then(tslib_1.__importStar);
            const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();
            dialog.addEventListener("validate", (event) => {
                const validationCallback = FormBuilderManager.getData(json.formId).then(async (data) => {
                    if (data instanceof Promise) {
                        data = await data;
                    }
                    const response = (await (0, Backend_1.prepareRequest)(this.#endpoint).post(data).fetchAsJson());
                    if ("dialog" in response) {
                        DomUtil.setInnerHtml(dialog.content, response.dialog);
                        return false;
                    }
                    else {
                        dialog.addEventListener("primary", () => {
                            if (FormBuilderManager.hasForm(json.formId)) {
                                FormBuilderManager.unregisterForm(json.formId);
                            }
                            callback(response.result);
                            //updateAssignee(response.assignee);
                        });
                        return true;
                    }
                });
                event.detail.push(validationCallback);
            });
            dialog.addEventListener("cancel", () => {
                if (FormBuilderManager.hasForm(json.formId)) {
                    FormBuilderManager.unregisterForm(json.formId);
                }
            });
            dialog.show(json.title);
        }
    }
    exports.FormBuilderSetup = FormBuilderSetup;
    exports.default = FormBuilderSetup;
});
