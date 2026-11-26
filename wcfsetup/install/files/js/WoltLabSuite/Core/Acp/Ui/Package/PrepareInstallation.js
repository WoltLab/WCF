/**
 * Prepares the installation of a package and starts it. Prompts for the
 * authentication credentials on rejection and reports conflicting packages.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Acp/Component/Package/Installation"], function (require, exports, tslib_1, Ajax_1, Dialog_1, Util_1, Language_1, Installation_1) {
    "use strict";
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiPackagePrepareInstallation {
        #pending = false;
        async start(identifier, version, redirectLocation) {
            await this.#install({ [identifier]: version }, redirectLocation);
        }
        /**
         * Resolves with `true` once the installation has been started.
         */
        async #install(packages, redirectLocation) {
            if (this.#pending) {
                throw new Error("There is already a pending installation.");
            }
            this.#pending = true;
            let authData = undefined;
            let credentialsDialog = undefined;
            try {
                while (true) {
                    let response;
                    try {
                        response = await this.#prepare(packages, authData);
                    }
                    finally {
                        credentialsDialog?.close();
                        credentialsDialog = undefined;
                    }
                    // The request was aborted by leaving the page.
                    if (response === undefined) {
                        return false;
                    }
                    switch (response.type) {
                        case "authorizationRequired" /* ResponseType.AuthorizationRequired */: {
                            const credentials = await this.#promptCredentials(response.template);
                            if (credentials === undefined) {
                                return false;
                            }
                            authData = credentials.authData;
                            credentialsDialog = credentials.dialog;
                            break;
                        }
                        case "conflict" /* ResponseType.Conflict */:
                            await this.#showConflict(response.template);
                            return false;
                        case "queue" /* ResponseType.Queue */:
                            if (response.queueID === null) {
                                return false;
                            }
                            void (0, Installation_1.startInstallation)(response.queueID, { redirectLocation });
                            return true;
                        default:
                            throw new Error("Unexpected response type");
                    }
                }
            }
            finally {
                this.#pending = false;
            }
        }
        async #prepare(packages, authData) {
            const payload = { packages };
            if (authData !== undefined) {
                payload.authData = authData;
            }
            return (await (0, Ajax_1.dboAction)("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
                .payload(payload)
                .dispatch());
        }
        /**
         * Resolves with the submitted credentials and their dialog, which stays open
         * until the caller closes it, or with `undefined` if the dialog was closed.
         */
        #promptCredentials(template) {
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(template).withoutControls();
            return new Promise((resolve) => {
                dialog.addEventListener("afterClose", () => resolve(undefined));
                // The templates for a paid upgrade or a required purchase do not offer a form.
                const submitButton = dialog.content.querySelector('.formSubmit > button[data-type="submit"]');
                submitButton?.addEventListener("click", () => {
                    const authData = this.#getAuthData(dialog, parseInt(submitButton.dataset.packageUpdateServerId, 10));
                    if (authData === undefined) {
                        return;
                    }
                    submitButton.disabled = true;
                    dialog.addEventListener("close", (event) => event.preventDefault());
                    resolve({ authData, dialog });
                });
                dialog.show((0, Language_1.getPhrase)("wcf.acp.package.update.unauthorized"));
            });
        }
        #getAuthData(dialog, packageUpdateServerID) {
            const usernameInput = dialog.content.querySelector("#packageUpdateServerUsername");
            const passwordInput = dialog.content.querySelector("#packageUpdateServerPassword");
            const saveCredentials = dialog.content.querySelector("#packageUpdateServerSaveCredentials");
            Util_1.default.innerError(usernameInput, false);
            Util_1.default.innerError(passwordInput, false);
            const username = usernameInput.value.trim();
            if (username === "") {
                Util_1.default.innerError(usernameInput, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return undefined;
            }
            const password = passwordInput.value.trim();
            if (password === "") {
                Util_1.default.innerError(passwordInput, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                return undefined;
            }
            return {
                packageUpdateServerID,
                password,
                saveCredentials: saveCredentials.checked,
                username,
            };
        }
        #showConflict(template) {
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(template).asAlert();
            return new Promise((resolve) => {
                dialog.addEventListener("afterClose", () => resolve());
                dialog.show((0, Language_1.getPhrase)("wcf.acp.package.update.excludedPackages"));
            });
        }
    }
    return AcpUiPackagePrepareInstallation;
});
