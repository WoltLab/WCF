import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

export type ResponseIncomplete = {
  completed: false;
};
export type ResponseCompleted = {
  completed: true;
  generateThumbnails: boolean;
  fileID: number;
  typeName: string;
  mimeType: string;
  link: string;
  data: Record<string, unknown>;
};

export type Response = ResponseIncomplete | ResponseCompleted;

export async function uploadChunk(
  identifier: string,
  sequenceNo: number,
  checksum: string,
  payload: Blob,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/files/upload/${identifier}/chunk/${sequenceNo}`);

  let response: Response;
  try {
    response = (await prepareRequest(url)
      .post(payload)
      .withHeader("chunk-checksum-sha256", checksum)
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
