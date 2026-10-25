/**
 * Sets whether the recent activities shown to the active user are filtered by the users they follow.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../../Result";

export async function setUserActivityEventFilter(filteredByFollowedUsers: boolean): Promise<void> {
  await fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/users/activity-events/filter`)
      .post({ filteredByFollowedUsers })
      .fetchAsJson();
  });
}
