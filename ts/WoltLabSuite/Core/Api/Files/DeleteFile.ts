import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function deleteFile(fileId: number): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_RPC_API_URL}core/files/${fileId}`).delete().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
