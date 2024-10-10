define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/DeleteObject", "../../Confirmation", "WoltLabSuite/Core/Ui/Notification"], function (require, exports, tslib_1, DeleteObject_1, Confirmation_1, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    UiNotification = tslib_1.__importStar(UiNotification);
    async function handleDelete(row, objectName, endpoint) {
        const confirmationResult = await (0, Confirmation_1.confirmationFactory)().delete(objectName);
        if (!confirmationResult) {
            return;
        }
        const result = await (0, DeleteObject_1.deleteObject)(endpoint);
        if (!result.ok) {
            return;
        }
        row.remove();
        // TODO: This shows a generic success message and should be replaced with a more specific message.
        UiNotification.show();
    }
    function setup(table) {
        table.addEventListener("action", (event) => {
            if (event.detail.action === "delete") {
                void handleDelete(event.target, event.detail.objectName, event.detail.endpoint);
            }
        });
    }
});
