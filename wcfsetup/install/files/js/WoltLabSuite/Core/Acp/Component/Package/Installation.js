/**
 * Processes the installation, update or uninstallation of packages step by step.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || (function () {
    var ownKeys = function(o) {
        ownKeys = Object.getOwnPropertyNames || function (o) {
            var ar = [];
            for (var k in o) if (Object.prototype.hasOwnProperty.call(o, k)) ar[ar.length] = k;
            return ar;
        };
        return ownKeys(o);
    };
    return function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
        __setModuleDefault(result, mod);
        return result;
    };
})();
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Api/Packages/Suggestions/ResolveSuggestedPackages", "WoltLabSuite/Core/Component/Confirmation", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Language"], function (require, exports, Backend_1, ResolveSuggestedPackages_1, Confirmation_1, Dialog_1, Selector_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.startInstallation = startInstallation;
    exports.startDevtoolsInstallation = startDevtoolsInstallation;
    exports.startUninstallation = startUninstallation;
    exports.setupConfirmation = setupConfirmation;
    exports.setupUninstallButtons = setupUninstallButtons;
    const controllers = {
        DevtoolsInstallPackage: "devtools-install-package",
        InstallPackage: "install-package",
        UninstallPackage: "uninstall-package",
    };
    class PackageInstallation {
        #actionName;
        #additionalParameters;
        #allowRollback;
        #dialog;
        #queueId = 0;
        #title;
        constructor(actionName, title, allowRollback, additionalParameters) {
            this.#actionName = actionName;
            this.#additionalParameters = additionalParameters;
            this.#allowRollback = allowRollback;
            this.#title = title;
        }
        async start(parameters) {
            await this.#run({ ...parameters, step: "prepare" });
        }
        async #run(parameters, formData) {
            let nextParameters = parameters;
            while (nextParameters !== undefined) {
                const response = await this.#request(nextParameters, formData);
                if (response === undefined) {
                    return;
                }
                formData = undefined;
                nextParameters = this.#handleResponse(response);
            }
        }
        async #request(parameters, formData) {
            const query = new URLSearchParams(Object.entries(parameters).map(([key, value]) => [key, value.toString()])).toString();
            // The controller name must not be encoded, which `URL.searchParams` would do.
            const request = (0, Backend_1.prepareRequest)(`${window.WSC_API_URL}index.php?${controllers[this.#actionName]}/&${query}`).post(formData);
            // The dialog shows the progress once it is open.
            if (this.#dialog !== undefined) {
                request.disableLoadingIndicator();
            }
            try {
                return (await request.fetchAsJson());
            }
            catch (error) {
                this.#failure();
                throw error;
            }
        }
        #handleResponse(data) {
            if (typeof window._trackPackageStep === "function") {
                window._trackPackageStep(this.#actionName, data);
            }
            const dialog = this.#getDialog(data);
            this.#setIcon("spinner");
            if (data.step === "rollback") {
                dialog.close();
                void startUninstallation(data.packageID);
                return undefined;
            }
            if (data.queueID) {
                this.#queueId = data.queueID;
            }
            const progress = dialog.content.querySelector("#packageInstallationProgress");
            if (data.progress !== undefined) {
                progress.value = data.progress;
                progress.textContent = `${data.progress}%`;
                dialog.content.querySelector("#packageInstallationProgressLabel").textContent = `${data.progress}%`;
            }
            if (data.currentAction) {
                dialog.content.querySelector("#packageInstallationAction").innerHTML = data.currentAction;
            }
            if (data.step === "success") {
                this.#setIcon("check");
                this.#clearInnerContent();
                const button = this.#appendButton((0, Language_1.getPhrase)("wcf.global.button.next"), () => {
                    window.location.href = data.redirectLocation;
                });
                this.#showInnerContent();
                button.focus();
                if (data.suggestedPackages !== undefined) {
                    void this.#offerSuggestedPackages(data.suggestedPackages, data.redirectLocation, button);
                }
                return undefined;
            }
            if (data.innerTemplate !== undefined) {
                this.#showForm(data.innerTemplate, data.step, data.node);
                return undefined;
            }
            this.#clearInnerContent();
            if (data.node) {
                return this.#getStepParameters(data.step, data.node);
            }
            return undefined;
        }
        #getDialog(data) {
            if (this.#dialog === undefined) {
                this.#dialog = (0, Dialog_1.dialogFactory)().fromHtml(data.template).withoutControls();
                // The installation cannot be interrupted, the role suppresses the close button.
                this.#dialog.dialog.setAttribute("role", "alertdialog");
                this.#dialog.addEventListener("close", (event) => {
                    event.preventDefault();
                });
                this.#dialog.show((0, Language_1.getPhrase)(this.#title));
            }
            return this.#dialog;
        }
        #showForm(html, step, node) {
            const content = this.#getInnerContent();
            content.innerHTML = html;
            const submit = () => {
                button.disabled = true;
                this.#setIcon("spinner");
                void this.#run(this.#getStepParameters(step, node), this.#getFormData(content));
            };
            content.querySelectorAll("input").forEach((input) => {
                input.addEventListener("keyup", (event) => {
                    if (event.key === "Enter" && !button.disabled) {
                        submit();
                    }
                });
            });
            this.#dialog.content.querySelector("#packageInstallationProgress").removeAttribute("value");
            this.#setIcon("question");
            const button = this.#appendButton((0, Language_1.getPhrase)("wcf.global.button.next"), () => submit());
            this.#showInnerContent();
        }
        /**
         * Replaces the success message with the suggested packages that can be
         * installed. Failures are ignored, the success message stays in place.
         */
        async #offerSuggestedPackages(suggestedPackages, redirectLocation, nextButton) {
            let template;
            try {
                const result = await (0, ResolveSuggestedPackages_1.resolveSuggestedPackages)(suggestedPackages.packageIDs, suggestedPackages.suggestions);
                if (!result.ok) {
                    return;
                }
                template = result.value.template;
            }
            catch {
                return;
            }
            // The "Next" button is disabled once it has been clicked.
            if (template === "" || nextButton.disabled) {
                return;
            }
            this.#showSuggestedPackages(template, redirectLocation);
        }
        #showSuggestedPackages(template, redirectLocation) {
            const content = this.#getInnerContent();
            content.innerHTML = template;
            const checkboxes = Array.from(content.querySelectorAll('input[type="checkbox"]'));
            const submitButton = content.querySelector('.formSubmit button[data-type="submit"]');
            const skipButton = content.querySelector('.formSubmit button[data-type="skip"]');
            const updateControls = (pending) => {
                checkboxes.forEach((checkbox) => {
                    checkbox.disabled = pending;
                });
                skipButton.disabled = pending;
                submitButton.disabled = pending || !checkboxes.some((checkbox) => checkbox.checked);
            };
            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", () => updateControls(false));
            });
            skipButton.addEventListener("click", () => {
                updateControls(true);
                window.location.href = redirectLocation;
            });
            submitButton.addEventListener("click", () => {
                const packages = {};
                checkboxes.forEach((checkbox) => {
                    if (checkbox.checked) {
                        packages[checkbox.value] = checkbox.dataset.packageVersion;
                    }
                });
                updateControls(true);
                void this.#installSuggestedPackages(packages, () => updateControls(false));
            });
            skipButton.focus();
        }
        async #installSuggestedPackages(packages, restore) {
            let started = false;
            try {
                const { default: PrepareInstallation } = await new Promise((resolve_1, reject_1) => { require(["WoltLabSuite/Core/Acp/Ui/Package/PrepareInstallation"], resolve_1, reject_1); }).then(__importStar);
                const redirectLocation = this.#additionalParameters.redirectLocation === "license" ? "license" : undefined;
                started = await new PrepareInstallation().startMultiple(packages, redirectLocation);
            }
            finally {
                if (started) {
                    this.#dialog.close();
                }
                else {
                    restore();
                }
            }
        }
        #getFormData(content) {
            const formData = new FormData();
            content.querySelectorAll("input").forEach((input) => {
                if ((input.type !== "checkbox" && input.type !== "radio") || input.checked) {
                    formData.append(input.name, input.value);
                }
            });
            return formData;
        }
        #failure() {
            if (this.#dialog === undefined) {
                return;
            }
            this.#dialog.content.querySelector("#packageInstallationProgress").removeAttribute("value");
            this.#setIcon("xmark");
            if (!this.#allowRollback) {
                return;
            }
            this.#clearInnerContent();
            this.#appendButton((0, Language_1.getPhrase)("wcf.acp.package.installation.rollback"), () => {
                this.#setIcon("spinner");
                void this.#run(this.#getStepParameters("rollback"));
            });
            this.#showInnerContent();
        }
        #getStepParameters(step, node) {
            const parameters = {
                ...this.#additionalParameters,
                queueID: this.#queueId,
                step,
            };
            if (node !== undefined) {
                parameters.node = node;
            }
            return parameters;
        }
        #appendButton(label, callback) {
            const button = document.createElement("button");
            button.type = "button";
            button.classList.add("button", "buttonPrimary");
            button.textContent = label;
            button.addEventListener("click", () => {
                button.disabled = true;
                callback();
            });
            const formSubmit = document.createElement("div");
            formSubmit.classList.add("formSubmit");
            formSubmit.append(button);
            this.#getInnerContent().append(formSubmit);
            return button;
        }
        #getInnerContent() {
            return this.#dialog.content.querySelector("#packageInstallationInnerContent");
        }
        #clearInnerContent() {
            this.#dialog.content.querySelector("#packageInstallationInnerContentContainer").hidden = true;
            this.#getInnerContent().innerHTML = "";
        }
        #showInnerContent() {
            this.#dialog.content.querySelector("#packageInstallationInnerContentContainer").hidden = false;
        }
        #setIcon(name) {
            this.#dialog?.content.querySelector(".jsPackageInstallationStatus fa-icon")?.setIcon(name);
        }
    }
    /**
     * Installs or updates the packages of the queue.
     */
    async function startInstallation(queueId, options = {}) {
        const additionalParameters = {};
        if (options.redirectLocation !== undefined) {
            additionalParameters.redirectLocation = options.redirectLocation;
        }
        const installation = new PackageInstallation("InstallPackage", options.isUpdate ? "wcf.acp.package.update.title" : "wcf.acp.package.install.title", options.allowRollback === true, additionalParameters);
        await installation.start({ ...additionalParameters, queueID: queueId });
    }
    /**
     * Installs a devtools project as a package.
     */
    async function startDevtoolsInstallation(queueId, projectId) {
        const additionalParameters = { projectID: projectId };
        const installation = new PackageInstallation("DevtoolsInstallPackage", "wcf.acp.package.install.title", false, additionalParameters);
        await installation.start({ ...additionalParameters, queueID: queueId });
    }
    /**
     * Uninstalls the package.
     */
    async function startUninstallation(packageId) {
        const uninstallation = new PackageInstallation("UninstallPackage", "wcf.acp.package.uninstallation.title", false, {});
        await uninstallation.start({ packageID: packageId });
    }
    /**
     * Handles the buttons on the confirmation page of a package installation.
     */
    function setupConfirmation(queueId, cancelEndpoint, options) {
        const submitButton = document.getElementById("submitButton");
        submitButton?.addEventListener("click", () => {
            submitButton.disabled = true;
            void startInstallation(queueId, options);
        });
        const backButton = document.getElementById("backButton");
        backButton.addEventListener("click", () => {
            backButton.disabled = true;
            void (0, Backend_1.prepareRequest)(cancelEndpoint)
                .post()
                .fetchAsJson()
                .then((response) => {
                window.location.href = response.redirectUrl;
            });
        });
    }
    /**
     * Handles the buttons to uninstall a package.
     */
    function setupUninstallButtons() {
        (0, Selector_1.wheneverFirstSeen)(".jsUninstallButton", (button) => {
            button.addEventListener("click", () => {
                // The message is a phrase that contains HTML.
                const message = new DOMParser().parseFromString(button.dataset.confirmMessage, "text/html");
                void (0, Confirmation_1.confirmationFactory)()
                    .custom(message.body.textContent)
                    .withoutMessage()
                    .then((result) => {
                    if (result) {
                        void startUninstallation(parseInt(button.dataset.objectId, 10));
                    }
                });
            });
        });
    }
});
