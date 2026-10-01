import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function deleteFile(fileId: number, uploaderToken?: string): Promise<ApiResult<[]>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/${fileId}`);
  if (uploaderToken !== undefined) {
    url.searchParams.set("uploaderToken", uploaderToken);
  }

  try {
    await prepareRequest(url).delete().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
