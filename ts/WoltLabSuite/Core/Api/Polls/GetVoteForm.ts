/**
 * Gets the rendered vote form of a poll.
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

export async function getVoteForm(pollId: number): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/polls/${pollId}/vote-form`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
