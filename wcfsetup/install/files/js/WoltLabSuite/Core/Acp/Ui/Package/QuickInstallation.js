/**
 * Initiates a package installation based on the StoreCode provided in the
 * package installation screen.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Package/QuickInstallation
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Language", "../../../Dom/Util", "../../../Ui/Dialog"], function (require, exports, tslib_1, Ajax_1, Core_1, Language, Util_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Language = (0, tslib_1.__importStar)(Language);
    Dialog_1 = (0, tslib_1.__importDefault)(Dialog_1);
    let codeInput;
    function detectCode() {
        const value = codeInput.value.trim();
        let isValid = false;
        if (value.length > 0) {
            const decodedValue = window.atob(value);
            let maybeJson;
            try {
                maybeJson = JSON.parse(decodedValue);
            }
            catch (_a) {
                // Skip invalid values.
            }
            if ((0, Core_1.isPlainObject)(maybeJson)) {
                const json = maybeJson;
                if (json.package && json.password && json.username) {
                    isValid = true;
                    void prepareInstallation(json);
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
    async function prepareInstallation(data) {
        const response = (await (0, Ajax_1.dboAction)("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
            .payload({
            packages: {
                [data.package]: "",
            },
            authData: {
                username: data.username,
                password: data.password,
                saveCredentials: false,
            },
        })
            .dispatch());
        if (response.queueID) {
            const installation = new window.WCF.ACP.Package.Installation(response.queueID, undefined, false);
            installation.prepareInstallation();
        }
        else if (response.returnValues) {
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
            }, response.returnValues.template);
        }
    }
    function setup() {
        codeInput = document.getElementById("quickInstallationCode");
        codeInput.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                event.preventDefault();
                detectCode();
            }
        });
        codeInput.addEventListener("paste", (event) => {
            event.preventDefault();
            const value = event.clipboardData.getData("text/plain");
            codeInput.value = value;
            detectCode();
        });
    }
    exports.setup = setup;
});
