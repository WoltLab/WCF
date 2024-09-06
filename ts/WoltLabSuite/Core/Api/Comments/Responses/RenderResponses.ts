/**
 * Gets the html code for the rendering of responses.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

type Response = {
  lastResponseTime: number;
  lastResponseID: number;
  template: string;
};

export async function renderResponses(
  commentId: number,
  lastResponseTime: number,
  lastResponseId: number,
  loadAllResponses: boolean,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/comments/responses/render`);
  url.searchParams.set("commentID", commentId.toString());
  url.searchParams.set("lastResponseTime", lastResponseTime.toString());
  url.searchParams.set("lastResponseID", lastResponseId.toString());
  url.searchParams.set("loadAllResponses", loadAllResponses.toString());

  let response: Response;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
