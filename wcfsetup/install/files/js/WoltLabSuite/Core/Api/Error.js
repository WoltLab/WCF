/**
 * Represents an error from a failed request to an API endpoint.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ApiError = void 0;
    class ApiError {
        type;
        code;
        message;
        param;
        statusCode;
        constructor(type, code, message, param, statusCode) {
            this.type = type;
            this.code = code;
            this.message = message;
            this.param = param;
            this.statusCode = statusCode;
        }
        getValidationError() {
            if (this.type !== "invalid_request_error" || this.statusCode !== 400) {
                return undefined;
            }
            return new ValidationError(this.code, this.message, this.param);
        }
    }
    exports.ApiError = ApiError;
    class ValidationError {
        code;
        message;
        param;
        constructor(code, message, param) {
            this.code = code;
            this.message = message;
            this.param = param;
        }
    }
});
