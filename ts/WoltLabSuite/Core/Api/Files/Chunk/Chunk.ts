import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

export type ResponseIncomplete = {
  completed: false;
};
export type ResponseCompleted = {
  completed: true;
  generateThumbnails: boolean;
  fileID: number;
  objectTypeID: number | null;
  mimeType: string;
  link: string;
  data: Record<string, unknown>;
};

export type Response = ResponseIncomplete | ResponseCompleted;

/**
 * Uploads a single chunk of a file.
 *
 * Aborting the request through `signal` rejects the returned promise with an
 * `AbortError`. The server may still have stored the chunk, and when it was
 * the last missing one, it may have created the file without the client ever
 * learning about it. Do not abort the request of the final chunk, wait for it
 * and delete the resulting file instead.
 */
export async function uploadChunk(
  identifier: string,
  sequenceNo: number,
  checksum: string,
  payload: Blob,
  signal?: AbortSignal,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload/${identifier}/chunk/${sequenceNo}`);

  const request = prepareRequest(url)
    .post(payload)
    .withHeader("chunk-checksum-sha256", checksum)
    .disableLoadingIndicator();

  let onAbort: (() => void) | undefined;
  if (signal !== undefined) {
    const abortController = request.getAbortController();
    if (signal.aborted) {
      abortController.abort();
    } else {
      onAbort = () => abortController.abort();
      signal.addEventListener("abort", onAbort, { once: true });
    }
  }

  let response: Response | undefined;
  try {
    response = (await request.fetchAsJson()) as Response | undefined;
  } catch (e) {
    return apiResultFromError(e);
  } finally {
    // The signal outlives the request, it is shared by all chunks of a file.
    if (onAbort !== undefined) {
      signal?.removeEventListener("abort", onAbort);
    }
  }

  if (response === undefined) {
    // `fetchAsJson()` resolves without a value for aborted requests.
    signal?.throwIfAborted();
  }

  return apiResultFromValue(response as Response);
}
