/**
 * Gets the rendered widget of a standalone poll.
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

export async function getWidget(pollId: number): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/polls/${pollId}/widget`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
