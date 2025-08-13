/**
 * Closes a report by marking it as done without further processing.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

export async function closeReport(queueId: number, markAsJustified: boolean): Promise<[]> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/moderation-queues/${queueId}/close`)
      .post({
        markAsJustified,
      })
      .fetchAsJson();
  });
}
