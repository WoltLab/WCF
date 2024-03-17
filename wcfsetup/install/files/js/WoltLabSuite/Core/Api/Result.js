define(["require", "exports", "../Core", "./Error"], function (require, exports, Core_1, Error_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.apiResultFromStatusNotOk = exports.apiResultFromError = exports.apiResultFromValue = void 0;
    function apiResultFromValue(value) {
        return {
            ok: true,
            value,
            unwrap() {
                return value;
            },
        };
    }
    exports.apiResultFromValue = apiResultFromValue;
    function apiResultFromError(error) {
        return {
            ok: false,
            error,
            unwrap() {
                throw error;
            },
        };
    }
    exports.apiResultFromError = apiResultFromError;
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
            return apiResultFromError(new Error_1.ApiError(json.type, json.code, json.message, json.param, response.status));
        }
        throw e;
    }
    exports.apiResultFromStatusNotOk = apiResultFromStatusNotOk;
});
