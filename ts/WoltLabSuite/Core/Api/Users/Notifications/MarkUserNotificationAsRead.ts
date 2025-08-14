/**
 * Marks a user notification as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";
import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";

type Response = {
  unreadNotifications: number;
};

export async function markUserNotificationAsRead(notificationId: number): Promise<Response> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/users/notifications/${notificationId}/mark-as-read`)
      .post()
      .fetchAsJson();
  });
}
