/**
 * Gets the html code for the rendering of a comment.
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
  response: string | undefined;
};

export async function renderComment(
  commentId: number,
  responseId: number | undefined = undefined,
  messageOnly: boolean = false,
  objectTypeId: number | undefined = undefined,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/comments/${commentId}/render`);
  url.searchParams.set("messageOnly", messageOnly.toString());
  if (responseId !== undefined) {
    url.searchParams.set("responseID", responseId.toString());
  }
  if (objectTypeId !== undefined) {
    url.searchParams.set("objectTypeID", objectTypeId.toString());
  }

  let response: Response;
  try {
    response = (await prepareRequest(url).get().fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
