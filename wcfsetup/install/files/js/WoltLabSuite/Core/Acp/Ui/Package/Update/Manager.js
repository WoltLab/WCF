/**
 * Allows the selection of packages for an update and handles any possible
 * conflicts. Prompts for credentials if necessary.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Language", "../../../../Ui/Dialog", "../../../../Dom/Util"], function (require, exports, tslib_1, Ajax_1, Language, UiDialog, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Language = tslib_1.__importStar(Language);
    UiDialog = tslib_1.__importStar(UiDialog);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiPackageUpdateManager {
        packages = new Map();
        submitButton;
        constructor() {
            this.submitButton = document.getElementById("packageUpdateSubmitButton");
            this.submitButton.addEventListener("click", (event) => {
                event.preventDefault();
                void this.prepareUpdate();
            });
            this.initPackages();
        }
        initPackages() {
            document.querySelectorAll(".packageUpdate").forEach((element) => {
                const checkbox = element.querySelector('input[type="checkbox"]');
                checkbox?.addEventListener("change", () => this.updateSubmitButtonState());
                this.packages.set(element, checkbox);
            });
        }
        updateSubmitButtonState() {
            let disableButton = true;
            this.packages.forEach((checkbox) => {
                if (checkbox === null || checkbox.checked) {
                    disableButton = false;
                }
            });
            this.submitButton.disabled = disableButton;
        }
        getSelectedPackages() {
            const packages = {};
            this.packages.forEach((checkbox, element) => {
                if (checkbox === null || checkbox.checked) {
                    packages[element.dataset.package] = element.dataset.version;
                }
            });
            return packages;
        }
        async prepareUpdate(authData) {
            const packages = this.getSelectedPackages();
            if (Object.keys(packages).length === 0) {
                return;
            }
            const payload = {
                packages,
            };
            if (authData) {
                payload.authData = authData;
            }
            let response;
            try {
                this.submitButton.disabled = true;
                response = (await (0, Ajax_1.dboAction)("prepareUpdate", "wcf\\data\\package\\update\\PackageUpdateAction")
                    .payload(payload)
                    .dispatch());
            }
            finally {
                this.submitButton.disabled = false;
            }
            switch (response.type) {
                case "authorizationRequired" /* ResponseType.AuthorizationRequired */:
                    this.promptCredentials(response.template);
                    break;
                case "conflict" /* ResponseType.Conflict */:
                    this.showConflict(response.template);
                    break;
                case "queue" /* ResponseType.Queue */:
                    this.startInstallation(response.queueID);
                    break;
                default:
                    throw new Error("Unexpected response type");
            }
        }
        promptCredentials(template) {
            UiDialog.open(this, template);
            UiDialog.setTitle(this, Language.get("wcf.acp.package.update.unauthorized"));
        }
        showConflict(template) {
            UiDialog.open(this, template);
            UiDialog.setTitle(this, Language.get("wcf.acp.package.update.excludedPackages"));
        }
        startInstallation(queueId) {
            if (UiDialog.isOpen(this)) {
                UiDialog.close(this);
            }
            const installation = new window.WCF.ACP.Package.Installation(queueId, undefined, false, true);
            installation.prepareInstallation();
        }
        _dialogSetup() {
            return {
                id: "acpUiPackageUpdateManager",
                options: {
                    title: Language.get("wcf.acp.package.update.title"),
                },
                source: null,
            };
        }
        _dialogSubmit() {
            let isValid = true;
            const usernameField = document.getElementById("packageUpdateServerUsername");
            const username = usernameField.value.trim();
            if (username === "") {
                Util_1.default.innerError(usernameField, Language.get("wcf.global.form.error.empty"));
                isValid = false;
            }
            else {
                Util_1.default.innerError(usernameField, false);
            }
            const passwordField = document.getElementById("packageUpdateServerPassword");
            const password = passwordField.value.trim();
            if (password === "") {
                Util_1.default.innerError(passwordField, Language.get("wcf.global.form.error.empty"));
                isValid = false;
            }
            else {
                Util_1.default.innerError(passwordField, false);
            }
            if (isValid) {
                const dialog = document.getElementById("acpUiPackageUpdateManager");
                const submitButton = dialog.querySelector('button[data-type="submit"]');
                const packageUpdateServerID = parseInt(submitButton.dataset.packageUpdateServerId, 10);
                const saveCredentials = document.getElementById("packageUpdateServerSaveCredentials")
                    .checked;
                void this.prepareUpdate({
                    packageUpdateServerID,
                    password,
                    saveCredentials,
                    username,
                });
            }
        }
    }
    let acpUiPackageUpdateManager;
    function setup() {
        if (acpUiPackageUpdateManager === undefined) {
            acpUiPackageUpdateManager = new AcpUiPackageUpdateManager();
        }
    }
});
