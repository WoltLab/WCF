/**
 * Executes user notification tests.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Language", "../../../../Ui/Dialog", "../../../../Dom/Util", "WoltLabSuite/Core/Api/Devtools/Notifications/TestNotificationEvent"], function (require, exports, tslib_1, Language_1, Dialog_1, Util_1, TestNotificationEvent_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiDevtoolsNotificationTest {
        buttons;
        titles = new Map();
        /**
         * Initializes the user notification test handler.
         */
        constructor() {
            this.buttons = Array.from(document.querySelectorAll(".jsTestEventButton"));
            this.buttons.forEach((button) => {
                button.addEventListener("click", (ev) => this.test(ev));
                const eventId = ~~button.dataset.eventId;
                const title = button.dataset.title;
                this.titles.set(eventId, title);
            });
        }
        /**
         * Shows the rendered test results of the event.
         */
        showResults(eventId, template) {
            Dialog_1.default.open(this, template);
            Dialog_1.default.setTitle(this, this.titles.get(eventId));
            const dialog = Dialog_1.default.getDialog(this).dialog;
            dialog.querySelectorAll(".formSubmit button").forEach((button) => {
                button.addEventListener("click", (ev) => this.changeView(ev));
            });
            // fix some margin issues
            const errors = Array.from(dialog.querySelectorAll(".error"));
            if (errors.length === 1) {
                errors[0].style.setProperty("margin-top", "0px");
                errors[0].style.setProperty("margin-bottom", "20px");
            }
            dialog.querySelectorAll(".notificationTestSection").forEach((section) => {
                section.style.setProperty("margin-top", "0px");
            });
            document.getElementById("notificationTestDialog").parentElement.scrollTop = 0;
        }
        /**
         * Changes the view after clicking on one of the buttons.
         */
        changeView(event) {
            const button = event.currentTarget;
            const dialog = Dialog_1.default.getDialog(this).dialog;
            dialog.querySelectorAll(".notificationTestSection").forEach((section) => Util_1.default.hide(section));
            const containerId = button.id.replace("Button", "");
            Util_1.default.show(document.getElementById(containerId));
            const primaryButton = dialog.querySelector(".formSubmit .buttonPrimary");
            primaryButton.classList.remove("buttonPrimary");
            button.classList.add("buttonPrimary");
            document.getElementById("notificationTestDialog").parentElement.scrollTop = 0;
        }
        /**
         * Returns the data used to setup the dialog.
         */
        _dialogSetup() {
            return {
                id: "notificationTestDialog",
                source: null,
            };
        }
        /**
         * Executes a test after clicking on a test button.
         */
        async test(event) {
            const button = event.currentTarget;
            const eventId = ~~button.dataset.eventId;
            button.innerHTML = '<fa-icon name="spinner" solid></fa-icon>';
            this.buttons.forEach((button) => (button.disabled = true));
            try {
                const { template } = await (0, TestNotificationEvent_1.testNotificationEvent)(eventId);
                this.showResults(eventId, template);
            }
            finally {
                this.buttons.forEach((button) => {
                    button.innerHTML = (0, Language_1.getPhrase)("wcf.acp.devtools.notificationTest.button.test");
                    button.disabled = false;
                });
            }
        }
    }
    let acpUiDevtoolsNotificationTest;
    /**
     * Initializes the user notification test handler.
     */
    function init() {
        if (!acpUiDevtoolsNotificationTest) {
            acpUiDevtoolsNotificationTest = new AcpUiDevtoolsNotificationTest();
        }
    }
});
