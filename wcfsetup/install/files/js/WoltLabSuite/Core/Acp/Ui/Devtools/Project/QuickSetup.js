/**
 * Handles quick setup of all projects within a path.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Dom/Util", "../../../../Language", "../../../../Ui/Dialog", "../../../../Ui/Notification"], function (require, exports, tslib_1, Ajax, Util_1, Language, Dialog_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    class AcpUiDevtoolsProjectQuickSetup {
        pathInput;
        submitButton;
        /**
         * Initializes the project quick setup handler.
         */
        constructor() {
            document.querySelectorAll(".jsDevtoolsProjectQuickSetupButton").forEach((button) => {
                button.addEventListener("click", (ev) => this.showDialog(ev));
            });
            this.submitButton = document.getElementById("projectQuickSetupSubmit");
            this.submitButton.addEventListener("click", (ev) => this.submit(ev));
            this.pathInput = document.getElementById("projectQuickSetupPath");
            this.pathInput.addEventListener("keypress", (ev) => this.keyPress(ev));
        }
        /**
         * Returns the data used to setup the AJAX request object.
         */
        _ajaxSetup() {
            return {
                data: {
                    actionName: "quickSetup",
                    className: "wcf\\data\\devtools\\project\\DevtoolsProjectAction",
                },
            };
        }
        /**
         * Handles successful AJAX request.
         */
        _ajaxSuccess(data) {
            if (data.returnValues.errorMessage) {
                Util_1.default.innerError(this.pathInput, data.returnValues.errorMessage);
                this.submitButton.disabled = false;
                return;
            }
            Dialog_1.default.close(this);
            UiNotification.show(data.returnValues.successMessage, () => {
                window.location.reload();
            });
        }
        /**
         * Returns the data used to setup the dialog.
         */
        _dialogSetup() {
            return {
                id: "projectQuickSetup",
                options: {
                    onShow: () => this.onDialogShow(),
                    title: Language.get("wcf.acp.devtools.project.quickSetup"),
                },
            };
        }
        /**
         * Handles the `[ENTER]` key to submit the form.
         */
        keyPress(event) {
            if (event.key === "Enter") {
                this.submit(event);
            }
        }
        /**
         * Is called every time the dialog is shown.
         */
        onDialogShow() {
            // reset path input
            this.pathInput.value = "";
            this.pathInput.focus();
            // hide error
            Util_1.default.innerError(this.pathInput, false);
        }
        /**
         * Shows the dialog after clicking on the related button.
         */
        showDialog(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        /**
         * Is called if the dialog form is submitted.
         */
        submit(event) {
            event.preventDefault();
            // check if path is empty
            if (this.pathInput.value === "") {
                Util_1.default.innerError(this.pathInput, Language.get("wcf.global.form.error.empty"));
                return;
            }
            Ajax.api(this, {
                parameters: {
                    path: this.pathInput.value,
                },
            });
            this.submitButton.disabled = true;
        }
    }
    let acpUiDevtoolsProjectQuickSetup;
    /**
     * Initializes the project quick setup handler.
     */
    function init() {
        if (!acpUiDevtoolsProjectQuickSetup) {
            acpUiDevtoolsProjectQuickSetup = new AcpUiDevtoolsProjectQuickSetup();
        }
    }
});
