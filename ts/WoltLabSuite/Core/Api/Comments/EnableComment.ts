/**
 * Enables a comment.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function enableComment(commentId: number): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_RPC_API_URL}core/comments/${commentId}/enable`).post().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
