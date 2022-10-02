define(["require", "exports", "./Confirmation/Custom", "./Confirmation/Delete", "./Confirmation/SoftDelete"], function (require, exports, Custom_1, Delete_1, SoftDelete_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.confirmationFactory = void 0;
    class ConfirmationSetup {
        custom(question) {
            return new Custom_1.ConfirmationCustom(question);
        }
        delete(question) {
            return new Delete_1.ConfirmationDelete(question);
        }
        restore(question) {
            return this.custom(question);
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
