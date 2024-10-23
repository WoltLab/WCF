/**
 * Executes user notification tests.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Language", "../../../../Ui/Dialog", "../../../../Dom/Util"], function (require, exports, tslib_1, Ajax, Language, Dialog_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
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
         * Returns the data used to setup the AJAX request object.
         */
        _ajaxSetup() {
            return {
                data: {
                    actionName: "testEvent",
                    className: "wcf\\data\\user\\notification\\event\\UserNotificationEventAction",
                },
            };
        }
        /**
         * Handles successful AJAX request.
         */
        _ajaxSuccess(data) {
            Dialog_1.default.open(this, data.returnValues.template);
            Dialog_1.default.setTitle(this, this.titles.get(~~data.returnValues.eventID));
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
            // restore buttons
            this.buttons.forEach((button) => {
                button.innerHTML = Language.get("wcf.acp.devtools.notificationTest.button.test");
                button.disabled = false;
            });
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
        test(event) {
            const button = event.currentTarget;
            button.innerHTML = '<fa-icon name="spinner" solid></fa-icon>';
            this.buttons.forEach((button) => (button.disabled = true));
            Ajax.api(this, {
                parameters: {
                    eventID: ~~button.dataset.eventId,
                },
            });
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
