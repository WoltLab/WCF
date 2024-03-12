define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ApiError = void 0;
    class ApiError {
        type;
        code;
        message;
        param;
        constructor(type, code, message, param) {
            this.type = type;
            this.code = code;
            this.message = message;
            this.param = param;
        }
    }
    exports.ApiError = ApiError;
});
