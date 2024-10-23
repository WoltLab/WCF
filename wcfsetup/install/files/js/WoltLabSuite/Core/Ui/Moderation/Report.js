/**
 * Provides the dialog to report content.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Component/Dialog", "../../Dom/Util", "../../Helper/Selector", "../../Language", "../Notification"], function (require, exports, tslib_1, Ajax_1, Dialog_1, Util_1, Selector_1, Language, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerLegacyButton = registerLegacyButton;
    exports.setup = setup;
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    async function openReportDialog(element) {
        const objectId = parseInt(element.dataset.objectId || "");
        const objectType = element.dataset.reportContent;
        const response = (await (0, Ajax_1.dboAction)("prepareReport", "wcf\\data\\moderation\\queue\\ModerationQueueReportAction")
            .payload({
            objectID: objectId,
            objectType,
        })
            .dispatch());
        let dialog;
        if (response.alreadyReported) {
            dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.template).asAlert();
        }
        else {
            dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.template).asPrompt();
            dialog.addEventListener("validate", (event) => {
                if (!validateReport(dialog)) {
                    event.preventDefault();
                }
            });
            dialog.addEventListener("primary", () => {
                void submitReport(dialog, objectType, objectId);
            });
        }
        dialog.show(Language.get("wcf.moderation.report.reportContent"));
    }
    function validateReport(dialog) {
        const message = dialog.content.querySelector(".jsReportMessage");
        const dl = message.closest("dl");
        if (message.value.trim() === "") {
            dl.classList.add("formError");
            (0, Util_1.innerError)(message, Language.get("wcf.global.form.error.empty"));
            return false;
        }
        dl.classList.remove("formError");
        (0, Util_1.innerError)(message, false);
        return true;
    }
    async function submitReport(dialog, objectType, objectId) {
        const message = dialog.content.querySelector(".jsReportMessage");
        const value = message.value.trim();
        await (0, Ajax_1.dboAction)("report", "wcf\\data\\moderation\\queue\\ModerationQueueReportAction")
            .payload({
            message: value,
            objectID: objectId,
            objectType,
        })
            .dispatch();
        UiNotification.show();
    }
    function validateButton(element) {
        if (element.dataset.reportContent === "") {
            console.error("Missing the value for [data-report-content]", element);
            return false;
        }
        const objectId = parseInt(element.dataset.objectId || "");
        if (!objectId) {
            console.error("Expected a valid integer for [data-object-id]", element);
            return false;
        }
        return true;
    }
    function registerButton(element) {
        if (validateButton(element)) {
            element.addEventListener("click", (event) => {
                if (element.tagName === "A" || element.dataset.isLegacyButton === "true") {
                    event.preventDefault();
                }
                void openReportDialog(element);
            });
        }
    }
    /**
     * @deprecated 6.0 Use the attribute `[data-report-content]` instead.
     */
    function registerLegacyButton(element, objectType) {
        element.dataset.reportContent = objectType;
        element.dataset.isLegacyButton = "true";
        registerButton(element);
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-report-content]", (element) => registerButton(element));
    }
});
