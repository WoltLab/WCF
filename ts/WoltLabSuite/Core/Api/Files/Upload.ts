import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";
import type { Exif } from "WoltLabSuite/Core/Image/ExifUtil";

type Response = {
  identifier: string;
  numberOfChunks: number;
};

export async function upload(
  filename: string,
  fileSize: number,
  fileHash: string,
  objectType: string,
  context: string,
  exifBytes: Exif | null = null,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload`);

  let exifData: string | null = null;
  if (exifBytes !== null) {
    exifData = new TextDecoder().decode(exifBytes);
  }

  const payload = {
    filename,
    fileSize,
    fileHash,
    objectType,
    context,
    exifData,
  };

  let response: Response;
  try {
    response = (await prepareRequest(url).post(payload).disableLoadingIndicator().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
