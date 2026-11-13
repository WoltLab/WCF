define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.uploadChunk = uploadChunk;
    /**
     * Uploads a single chunk of a file.
     *
     * Aborting the request through `signal` rejects the returned promise with an
     * `AbortError`. The server may still have stored the chunk, and when it was
     * the last missing one, it may have created the file without the client ever
     * learning about it. Do not abort the request of the final chunk, wait for it
     * and delete the resulting file instead.
     */
    async function uploadChunk(identifier, sequenceNo, checksum, payload, signal) {
        const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload/${identifier}/chunk/${sequenceNo}`);
        const request = (0, Backend_1.prepareRequest)(url)
            .post(payload)
            .withHeader("chunk-checksum-sha256", checksum)
            .disableLoadingIndicator();
        let onAbort;
        if (signal !== undefined) {
            const abortController = request.getAbortController();
            if (signal.aborted) {
                abortController.abort();
            }
            else {
                onAbort = () => abortController.abort();
                signal.addEventListener("abort", onAbort, { once: true });
            }
        }
        let response;
        try {
            response = (await request.fetchAsJson());
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        finally {
            // The signal outlives the request, it is shared by all chunks of a file.
            if (onAbort !== undefined) {
                signal?.removeEventListener("abort", onAbort);
            }
        }
        if (response === undefined) {
            // `fetchAsJson()` resolves without a value for aborted requests.
            signal?.throwIfAborted();
        }
        return (0, Result_1.apiResultFromValue)(response);
    }
});
