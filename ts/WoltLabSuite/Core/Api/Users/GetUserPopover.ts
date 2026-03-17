/**
 * Gets the html code for the rendering of a user popover.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

type Response = {
  template: string;
};

export async function getUserPopover(userId: number): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/users/${userId}/popover`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
