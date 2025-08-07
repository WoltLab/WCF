/**
 * Provides the dialog to report content.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Snackbar", "../../Component/Dialog", "../../Helper/Selector"], function (require, exports, Snackbar_1, Dialog_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerLegacyButton = registerLegacyButton;
    exports.setup = setup;
    let reportEndpoint;
    async function openReportDialog(element) {
        if (!reportEndpoint) {
            throw new Error("Report endpoint is not set. Please call 'setup()' first.");
        }
        const objectId = parseInt(element.dataset.objectId || "");
        const objectType = element.dataset.reportContent;
        const url = new URL(reportEndpoint);
        url.searchParams.set("objectID", objectId.toString());
        url.searchParams.set("objectType", objectType);
        const response = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(url.toString());
        if (response.ok) {
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
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
    function setup(reportEndpointUrl) {
        reportEndpoint = reportEndpointUrl;
        (0, Selector_1.wheneverFirstSeen)("[data-report-content]", (element) => registerButton(element));
    }
});
