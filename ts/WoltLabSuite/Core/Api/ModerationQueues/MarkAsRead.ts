/**
 * Marks a moderation queue as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";

type Response = {
  unreadModerationItems: number;
};

export async function markAsRead(queueId: number): Promise<ApiResult<Response>> {
  let response: Response;

  try {
    response = (await prepareRequest(`${window.WSC_RPC_API_URL}core/moderation-queues/${queueId}/mark-as-read`)
      .post()
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
