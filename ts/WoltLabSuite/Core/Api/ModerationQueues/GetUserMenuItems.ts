/**
 * Retrieves the user menu items for the moderation queues.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { ApiResult, apiResultFromError, apiResultFromValue } from "../Result";
import { UserMenuData } from "WoltLabSuite/Core/Ui/User/Menu/Data/Provider";

type Response = {
  unreadModerationCount: number;
  items: UserMenuData[];
};

export async function getUserMenuItems(): Promise<ApiResult<Response>> {
  let response: Response;

  try {
    response = (await prepareRequest(`${window.WSC_RPC_API_URL}core/moderation-queues/user-menu-items`)
      .get()
      .fetchAsJson()) as Response;
  } catch (e) {
    return apiResultFromError(e);
  }

  return apiResultFromValue(response);
}
