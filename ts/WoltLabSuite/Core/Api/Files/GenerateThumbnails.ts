import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Thumbnail = {
  identifier: string;
  link: string;
};
type Response = Thumbnail[];

export async function generateThumbnails(fileID: number): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/files/${fileID}/generatethumbnails`);

  let response: Response;
  try {
    response = (await prepareRequest(url).post().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
