/**
 * Creates a new comment.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  commentID: number;
};

export async function createComment(
  objectTypeId: number,
  objectId: number,
  message: string,
  guestToken: string = "",
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/comments`);

  const payload = {
    objectTypeID: objectTypeId,
    objectID: objectId,
    message,
    guestToken,
  };

  let response: Response;
  try {
    response = (await prepareRequest(url).post(payload).fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
