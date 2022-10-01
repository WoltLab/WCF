define(["require", "exports", "./Confirmation/Delete", "./Confirmation/SoftDelete"], function (require, exports, Delete_1, SoftDelete_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.confirmationFactory = void 0;
    class ConfirmationSetup {
        delete(question) {
            return new Delete_1.ConfirmationDelete(question);
        }
        softDelete(question) {
            return new SoftDelete_1.ConfirmationSoftDelete(question);
        }
    }
    function confirmationFactory() {
        return new ConfirmationSetup();
    }
    exports.confirmationFactory = confirmationFactory;
});
