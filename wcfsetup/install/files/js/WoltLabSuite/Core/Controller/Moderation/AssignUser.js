define(["require", "exports", "tslib", "../../Component/Dialog", "../../Core", "../../Form/Builder/Manager"], function (require, exports, tslib_1, Dialog_1, Core_1, FormBuilderManager) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    FormBuilderManager = tslib_1.__importStar(FormBuilderManager);
    function setup(button) {
        button.addEventListener("click", async (event) => {
            const response = await fetch(button.dataset.url);
            const json = await response.json();
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(json.dialog).asPrompt();
            dialog.addEventListener("primary", async () => {
                const data = await FormBuilderManager.getData(json.formId);
                const response = await fetch(button.dataset.url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json; charset=UTF-8",
                        "X-XSRF-TOKEN": (0, Core_1.getXsrfToken)(),
                    },
                    body: JSON.stringify(data),
                });
                // TODO: Show success / update UI
                // TODO: Handle incorrect form inputs
            });
            dialog.addEventListener("close", () => {
                // TODO: This appears to be broken
                if (FormBuilderManager.hasForm(json.formId)) {
                    FormBuilderManager.unregisterForm(json.formId);
                }
            });
            dialog.show("yadayada");
            reinsertScripts(dialog.content);
        });
    }
    exports.setup = setup;
    function reinsertScripts(container) {
        const scripts = container.querySelectorAll("script");
        for (let i = 0, length = scripts.length; i < length; i++) {
            const script = scripts[i];
            const newScript = document.createElement("script");
            if (script.src) {
                newScript.src = script.src;
            }
            else {
                newScript.textContent = script.textContent;
            }
            container.appendChild(newScript);
            script.remove();
        }
    }
});
