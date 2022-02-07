define(["require", "exports", "tslib", "./Abstract"], function (require, exports, tslib_1, Abstract_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Results = void 0;
    Abstract_1 = (0, tslib_1.__importDefault)(Abstract_1);
    class Results extends Abstract_1.default {
        getButtonSelector() {
            return ".showResultsButton";
        }
        getActionName() {
            return "getResult";
        }
        success(data) {
            this.setInnerContainer(data.template);
        }
    }
    exports.Results = Results;
    exports.default = Results;
});
