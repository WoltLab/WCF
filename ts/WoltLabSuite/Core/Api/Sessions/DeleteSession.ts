import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function deleteSession(sessionId: string): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_API_URL}index.php?api/rpc/core/sessions/${sessionId}`).delete().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
