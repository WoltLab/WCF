/**
 * Mark all moderation queue as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

export async function markAllModerationQueuesAsRead(): Promise<ApiResult<[]>> {
  try {
    await prepareRequest(`${window.WSC_RPC_API_URL}core/moderation-queues/mark-all-as-read`).post().fetchAsJson();
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue([]);
}
