/**
 * Attempts to download the requested package from the file and prompts for the
 * authentication credentials on rejection.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/PrepareInstallation
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Language", "../../../Ui/Dialog", "../../../Dom/Util"], function (require, exports, tslib_1, Ajax, Core, Language, Dialog_1, Util_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiPackagePrepareInstallation {
        constructor() {
            this.identifier = "";
            this.version = "";
        }
        start(identifier, version) {
            this.identifier = identifier;
            this.version = version;
            this.prepare({});
        }
        prepare(authData) {
            const packages = {};
            packages[this.identifier] = this.version;
            Ajax.api(this, {
                parameters: {
                    authData: authData,
                    packages: packages,
                },
            });
        }
        submit(packageUpdateServerId) {
            const usernameInput = document.getElementById("packageUpdateServerUsername");
            const passwordInput = document.getElementById("packageUpdateServerPassword");
            Util_1.default.innerError(usernameInput, false);
            Util_1.default.innerError(passwordInput, false);
            const username = usernameInput.value.trim();
            if (username === "") {
                Util_1.default.innerError(usernameInput, Language.get("wcf.global.form.error.empty"));
            }
            else {
                const password = passwordInput.value.trim();
                if (password === "") {
                    Util_1.default.innerError(passwordInput, Language.get("wcf.global.form.error.empty"));
                }
                else {
                    const saveCredentials = document.getElementById("packageUpdateServerSaveCredentials");
                    this.prepare({
                        packageUpdateServerID: packageUpdateServerId,
                        password,
                        saveCredentials: saveCredentials.checked,
                        username,
                    });
                }
            }
        }
        _ajaxSuccess(data) {
            if (data.returnValues.queueID) {
                if (Dialog_1.default.isOpen(this)) {
                    Dialog_1.default.close(this);
                }
                const installation = new window.WCF.ACP.Package.Installation(data.returnValues.queueID, undefined, false);
                installation.prepareInstallation();
            }
            else if (data.returnValues.template) {
                Dialog_1.default.open(this, data.returnValues.template);
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "prepareInstallation",
                    className: "wcf\\data\\package\\update\\PackageUpdateAction",
                },
            };
        }
        _dialogSetup() {
            return {
                id: "packageDownloadAuthorization",
                options: {
                    onSetup: (content) => {
                        const button = content.querySelector(".formSubmit > button");
                        button.addEventListener("click", (event) => {
                            event.preventDefault();
                            const packageUpdateServerId = ~~button.dataset.packageUpdateServerId;
                            this.submit(packageUpdateServerId);
                        });
                    },
                    title: Language.get("wcf.acp.package.update.unauthorized"),
                },
                source: null,
            };
        }
    }
    Core.enableLegacyInheritance(AcpUiPackagePrepareInstallation);
    return AcpUiPackagePrepareInstallation;
});
