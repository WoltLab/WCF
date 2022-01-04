define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Language", "../../../../Ui/Dialog", "../../../../Dom/Util"], function (require, exports, tslib_1, Ajax_1, Language, UiDialog, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    UiDialog = (0, tslib_1.__importStar)(UiDialog);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    class AcpUiPackageUpdateManager {
        constructor() {
            this.packages = new Map();
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
                checkbox === null || checkbox === void 0 ? void 0 : checkbox.addEventListener("change", () => {
                    const dl = checkbox.closest("dl");
                    if (checkbox.checked) {
                        dl.classList.remove("disabled");
                    }
                    else {
                        dl.classList.add("disabled");
                    }
                    this.updateSubmitButtonState();
                });
                this.packages.set(element, checkbox);
            });
        }
        updateSubmitButtonState() {
            let isValid = true;
            this.packages.forEach((checkbox) => {
                if (checkbox && !checkbox.checked) {
                    isValid = false;
                }
            });
            this.submitButton.disabled = !isValid;
        }
        getSelectedPackages() {
            const packages = {};
            document.querySelectorAll(".jsPackageUpdate").forEach((element) => {
                const checkbox = element.querySelector('input[type="checkbox"]');
                if (checkbox.checked) {
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
            if (response.type === "authorizationRequired" /* AuthorizationRequired */) {
                this.promptCredentials(response.template);
            }
            else if (response.type === "conflict" /* Conflict */) {
                this.showConflict(response.template);
            }
            else {
                this.startInstallation(response.queueID);
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
                const packageUpdateServerID = +this.submitButton.dataset.packageUpdateServerId;
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
    exports.setup = setup;
});
