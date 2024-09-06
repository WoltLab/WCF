import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

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
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/upload`);

  const payload = {
    filename,
    fileSize,
    fileHash,
    objectType,
    context,
  };

  let response: Response;
  try {
    response = (await prepareRequest(url).post(payload).fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
