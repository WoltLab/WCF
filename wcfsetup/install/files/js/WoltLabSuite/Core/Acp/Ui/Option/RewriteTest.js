/**
 * Automatic URL rewrite support testing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog", "../../../Dom/Util", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, tslib_1, Language, Dialog_1, Util_1, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class RewriteTest {
        apps;
        buttonStartTest = document.getElementById("rewriteTestStart");
        callbackChange;
        passed = false;
        urlOmitIndexPhp;
        /**
         * Initializes the rewrite test, but aborts early if URL rewriting was
         * enabled at page init.
         */
        constructor(apps) {
            const urlOmitIndexPhp = document.getElementById("url_omit_index_php");
            // This configuration part is unavailable when running in enterprise mode.
            if (urlOmitIndexPhp === null) {
                return;
            }
            this.urlOmitIndexPhp = urlOmitIndexPhp;
            if (this.urlOmitIndexPhp.checked) {
                // option is already enabled, ignore it
                return;
            }
            this.callbackChange = (ev) => this.onChange(ev);
            this.urlOmitIndexPhp.addEventListener("change", this.callbackChange);
            this.apps = apps;
        }
        /**
         * Forces the rewrite test when attempting to enable the URL rewriting.
         */
        onChange(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        /**
         * Runs the actual rewrite test.
         */
        async runTest(event) {
            if (event instanceof Event) {
                event.preventDefault();
            }
            if (this.buttonStartTest.classList.contains("disabled")) {
                return;
            }
            this.buttonStartTest.classList.add("disabled");
            this.setStatus("running");
            const tests = Array.from(this.apps).map(([app, url]) => {
                return (0, Backend_1.prepareRequest)(url)
                    .get()
                    .disableLoadingIndicator()
                    .fetchAsJson()
                    .then((data) => {
                    if (!Object.prototype.hasOwnProperty.call(data, "core_rewrite_test") ||
                        data.core_rewrite_test !== "passed") {
                        return false;
                    }
                    else {
                        return true;
                    }
                }, () => false)
                    .then((pass) => {
                    return { app, pass };
                });
            });
            const results = await Promise.all(tests);
            const passed = results.every((result) => result.pass);
            // Delay the status update to prevent UI flicker.
            await new Promise((resolve) => window.setTimeout(resolve, 500));
            if (passed) {
                this.passed = true;
                this.setStatus("success");
                this.urlOmitIndexPhp.removeEventListener("change", this.callbackChange);
                await new Promise((resolve) => window.setTimeout(resolve, 1000));
                if (Dialog_1.default.isOpen(this)) {
                    Dialog_1.default.close(this);
                }
            }
            else {
                this.buttonStartTest.classList.remove("disabled");
                const testFailureResults = document.getElementById("dialogRewriteTestFailureResults");
                testFailureResults.innerHTML = results
                    .map((result) => {
                    return `<li><span class="badge label ${result.pass ? "green" : "red"}">${Language.get("wcf.acp.option.url_omit_index_php.test.status." + (result.pass ? "success" : "failure"))}</span> ${result.app}</li>`;
                })
                    .join("");
                this.setStatus("failure");
            }
        }
        /**
         * Displays the appropriate dialog message.
         */
        setStatus(status) {
            const containers = [
                document.getElementById("dialogRewriteTestRunning"),
                document.getElementById("dialogRewriteTestSuccess"),
                document.getElementById("dialogRewriteTestFailure"),
            ];
            containers.forEach((element) => Util_1.default.hide(element));
            let i = 0;
            if (status === "success") {
                i = 1;
            }
            else if (status === "failure") {
                i = 2;
            }
            Util_1.default.show(containers[i]);
        }
        _dialogSetup() {
            return {
                id: "dialogRewriteTest",
                options: {
                    onClose: () => {
                        if (!this.passed) {
                            const urlOmitIndexPhpNo = document.getElementById("url_omit_index_php_no");
                            urlOmitIndexPhpNo.checked = true;
                        }
                    },
                    onSetup: () => {
                        this.buttonStartTest.addEventListener("click", (ev) => {
                            void this.runTest(ev);
                        });
                    },
                    onShow: () => this.runTest(),
                    title: Language.get("wcf.acp.option.url_omit_index_php"),
                },
            };
        }
    }
    let rewriteTest;
    function init(apps) {
        if (!rewriteTest) {
            rewriteTest = new RewriteTest(apps);
        }
    }
});
