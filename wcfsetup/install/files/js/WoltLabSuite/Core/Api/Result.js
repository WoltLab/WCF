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
define(["require", "exports", "../Ajax/Error", "../Component/Dialog", "../Core", "../Language", "../StringUtil", "./Error"], function (require, exports, Error_1, Dialog_1, Core_1, Language_1, StringUtil_1, Error_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.apiResultFromValue = apiResultFromValue;
    exports.apiResultFromError = apiResultFromError;
    exports.apiResultFromStatusNotOk = apiResultFromStatusNotOk;
    exports.fromInfallibleApiRequest = fromInfallibleApiRequest;
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
            json = await response.clone().json();
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
                    showErrorDialog(apiError);
                    throw new Error("Trying to unwrap an erroneous result.", { cause: apiError });
                },
            };
        }
        throw e;
    }
    /**
     * Helper method for API requests that are expected to never fail. Infallible
     * requests are those that should only fail if there is an unexpected server
     * error or if the request was the result of a bug in the client.
     */
    async function fromInfallibleApiRequest(request) {
        try {
            return (await request());
        }
        catch (e) {
            const error = await apiResultFromError(e);
            return error.unwrap();
        }
    }
    function showErrorDialog(apiError) {
        const code = (0, StringUtil_1.escapeHTML)(apiError.code);
        const type = (0, StringUtil_1.escapeHTML)(apiError.type);
        const message = apiError.message ? (0, StringUtil_1.escapeHTML)(apiError.message) : "(not set)";
        const param = apiError.param ? "<kbd>" + (0, StringUtil_1.escapeHTML)(apiError.param) + "</kbd>" : "(not set)";
        const html = `
    <dl>
      <dt>Unexpected server error</dt>
      <dd><kbd>${type}</kbd></dd>
    </dl>
    <dl>
      <dt>Error code</dt>
      <dd><kbd>${code}</kbd></dd>
    </dl>
    <dl>
      <dt>Parameter</dt>
      <dd>${param}</dd>
    </dl>
    <dl>
      <dt>Message</dt>
      <dd>${message}</dd>
    </dl>
  `;
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(html).asAlert();
        dialog.show((0, Language_1.getPhrase)("wcf.global.error.title"));
    }
});
