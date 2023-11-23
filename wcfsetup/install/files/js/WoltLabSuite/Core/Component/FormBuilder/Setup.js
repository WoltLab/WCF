/**
 * Promise-based API to use the Form Builder API with PSR-15 controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Ajax/Backend", "../../Dom/Util", "../../Form/Builder/Manager"], function (require, exports, tslib_1, Backend_1, DomUtil, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.FormBuilderSetup = void 0;
    DomUtil = tslib_1.__importStar(DomUtil);
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    class FormBuilderSetup {
        async fromEndpoint(url) {
            const json = (await (0, Backend_1.prepareRequest)(url).get().fetchAsJson());
            // Prevents a circular dependency.
            const { dialogFactory } = await new Promise((resolve_1, reject_1) => { require(["../Dialog"], resolve_1, reject_1); }).then(tslib_1.__importStar);
            const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();
            return new Promise((resolve) => {
                dialog.addEventListener("validate", (event) => {
                    const validationCallback = FormBuilderManager.getData(json.formId).then(async (data) => {
                        if (data instanceof Promise) {
                            data = await data;
                        }
                        const response = (await (0, Backend_1.prepareRequest)(url).post(data).fetchAsJson());
                        if ("dialog" in response) {
                            FormBuilderManager.unregisterForm(json.formId);
                            DomUtil.setInnerHtml(dialog.content, response.dialog);
                            return false;
                        }
                        else {
                            dialog.addEventListener("primary", () => {
                                if (FormBuilderManager.hasForm(json.formId)) {
                                    FormBuilderManager.unregisterForm(json.formId);
                                }
                                resolve({
                                    ok: true,
                                    result: response.result,
                                });
                            });
                            return true;
                        }
                    });
                    event.detail.push(validationCallback);
                });
                dialog.addEventListener("afterClose", () => {
                    if (FormBuilderManager.hasForm(json.formId)) {
                        FormBuilderManager.unregisterForm(json.formId);
                    }
                    resolve({
                        ok: false,
                        result: undefined,
                    });
                });
                dialog.show(json.title);
            });
        }
    }
    exports.FormBuilderSetup = FormBuilderSetup;
    exports.default = FormBuilderSetup;
});
