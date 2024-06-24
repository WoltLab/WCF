/**
 * Deletes a comment response.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../../Result";

export async function deleteResponse(responseId: number): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_API_URL}index.php?api/rpc/core/comments/responses/${responseId}`)
      .delete()
      .fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
