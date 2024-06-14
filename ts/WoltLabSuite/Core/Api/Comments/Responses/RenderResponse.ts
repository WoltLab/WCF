/**
 * Gets the html code for the rendering of a response.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

type Response = {
  template: string;
};

export async function renderResponse(
  responseId: number,
  messageOnly: boolean = false,
  objectTypeId: number | undefined = undefined,
): Promise<ApiResult<Response>> {
  const url = new URL(`${window.WSC_API_URL}index.php?api/rpc/core/comments/responses/${responseId}/render`);
  url.searchParams.set("messageOnly", messageOnly.toString());
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
