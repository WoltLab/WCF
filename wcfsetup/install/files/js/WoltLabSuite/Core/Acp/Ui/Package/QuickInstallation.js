/**
 * Initiates a package installation based on the StoreCode provided in the
 * package installation screen.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Ajax/Status", "../../../Core", "../../../Language", "../../../Dom/Util", "../../../Ui/Dialog", "WoltLabSuite/Core/Ajax/Error"], function (require, exports, tslib_1, Ajax_1, AjaxStatus, Core_1, Language, Util_1, Dialog_1, Error_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    AjaxStatus = tslib_1.__importStar(AjaxStatus);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    let codeInput;
    function detectCode(versionNumber) {
        const value = codeInput.value.trim();
        if (value === "") {
            (0, Util_1.innerError)(codeInput, false);
            return;
        }
        let isValid = false;
        if (value.startsWith("WoltLab_StoreCode_Do_Not_Share_")) {
            const decodedValue = window.atob(value.replace(/^WoltLab_StoreCode_Do_Not_Share_/, ""));
            let maybeJson;
            try {
                maybeJson = JSON.parse(decodedValue);
            }
            catch {
                // Skip invalid values.
            }
            if ((0, Core_1.isPlainObject)(maybeJson)) {
                const json = maybeJson;
                if (json.package && json.password && json.username) {
                    isValid = true;
                    void prepareInstallation(json, versionNumber);
                }
            }
        }
        if (isValid) {
            (0, Util_1.innerError)(codeInput, false);
        }
        else {
            (0, Util_1.innerError)(codeInput, Language.get("wcf.acp.package.quickInstallation.code.error.invalid"));
        }
    }
    let refreshedPackageDatabase = undefined;
    function refreshPackageDatabase() {
        if (refreshedPackageDatabase === undefined) {
            refreshedPackageDatabase = (0, Ajax_1.dboAction)("refreshDatabase", "wcf\\data\\package\\update\\PackageUpdateAction")
                .disableLoadingIndicator()
                .dispatch();
        }
        return refreshedPackageDatabase;
    }
    async function prepareInstallation(data, versionNumber) {
        try {
            AjaxStatus.show();
            await refreshPackageDatabase();
        }
        finally {
            AjaxStatus.hide();
        }
        let response;
        try {
            response = (await (0, Ajax_1.dboAction)("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
                .payload({
                packages: {
                    [data.package]: "",
                },
                authData: {
                    username: data.username,
                    password: data.password,
                    saveCredentials: false,
                    isStoreCode: true,
                },
            })
                .dispatch());
        }
        catch (e) {
            if (e instanceof Error_1.StatusNotOk) {
                try {
                    const json = await e.response.clone().json();
                    if (typeof json.message === "string" && json.message.startsWith("Cannot find the package '")) {
                        codeInput.value = "";
                        (0, Util_1.innerError)(codeInput, Language.getPhrase("wcf.acp.package.error.incompatibleStoreProduct", { versionNumber }));
                        return;
                    }
                }
                catch {
                    throw e;
                }
            }
            throw e;
        }
        if ("queueID" in response) {
            if (response.queueID === null) {
                codeInput.value = "";
                (0, Util_1.innerError)(codeInput, Language.get("wcf.acp.package.error.uniqueAlreadyInstalled"));
            }
            else {
                const installation = new window.WCF.ACP.Package.Installation(response.queueID, undefined, false);
                installation.prepareInstallation();
            }
        }
        else if ("template" in response) {
            Dialog_1.default.open({
                _dialogSetup() {
                    return {
                        id: "quickInstallationError",
                        options: {
                            title: Language.get("wcf.global.error.title"),
                        },
                        source: null,
                    };
                },
            }, response.template);
        }
        else {
            throw new Error("Unreachable");
        }
    }
    function setup(versionNumber) {
        codeInput = document.getElementById("quickInstallationCode");
        codeInput.addEventListener("focus", () => {
            // Refresh the package database when focusing the input to hide the latency of the package
            // server querying from the user, because the refresh runs, while the user is busy with
            // pasting the StoreCode into the input.
            void refreshPackageDatabase();
        });
        codeInput.addEventListener("input", () => {
            detectCode(versionNumber);
        });
    }
});
