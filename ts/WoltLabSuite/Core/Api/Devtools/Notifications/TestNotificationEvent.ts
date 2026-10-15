/**
 * Tests a user notification event by rendering all possible notifications.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 * @woltlabExcludeBundle all
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../../Result";

type Response = {
  template: string;
};

export async function testNotificationEvent(eventId: number): Promise<Response> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/devtools/notifications/${eventId}/test`).post().fetchAsJson();
  });
}
