/**
 * Represents the result of a request to an API endpoint and provides functions
 * to create the result itself. Unwrapping the result through `.unwrap()` is
 * useful in situations where there should formally never an error.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "../Ajax/Error", "../Core", "./Error"], function (require, exports, Error_1, Core_1, Error_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.apiResultFromValue = apiResultFromValue;
    exports.apiResultFromError = apiResultFromError;
    exports.apiResultFromStatusNotOk = apiResultFromStatusNotOk;
    function apiResultFromValue(value) {
        return {
            ok: true,
            value,
            unwrap() {
                return value;
            },
        };
    }
    async function apiResultFromError(error) {
        if (error instanceof Error_1.StatusNotOk) {
            return apiResultFromStatusNotOk(error);
        }
        throw error;
    }
    async function apiResultFromStatusNotOk(e) {
        const { response } = e;
        if (response === undefined) {
            // Aborted requests do not have a return value.
            throw e;
        }
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw e;
        }
        let json;
        try {
            json = await response.json();
        }
        catch {
            throw e;
        }
        if ((0, Core_1.isPlainObject)(json) &&
            Object.hasOwn(json, "type") &&
            (json.type === "api_error" || json.type === "invalid_request_error") &&
            typeof json.code === "string" &&
            typeof json.message === "string" &&
            typeof json.param === "string") {
            const apiError = new Error_2.ApiError(json.type, json.code, json.message, json.param, response.status);
            return {
                ok: false,
                error: apiError,
                unwrap() {
                    throw new Error("Trying to unwrap an erroneous result.", { cause: apiError });
                },
            };
        }
        throw e;
    }
});
