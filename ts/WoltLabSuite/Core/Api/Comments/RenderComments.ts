/**
 * Gets the html code for the rendering of comments.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  template: string;
  lastCommentTime: number;
};

export async function renderComments(
  objectTypeId: number,
  objectId: number,
  lastCommentTime: number = 0,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/comments/render`);
  url.searchParams.set("objectTypeID", objectTypeId.toString());
  url.searchParams.set("objectID", objectId.toString());
  url.searchParams.set("lastCommentTime", lastCommentTime.toString());

  let response: Response;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
