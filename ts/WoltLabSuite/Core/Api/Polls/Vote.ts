/**
 * Stores the vote of the active user in a poll.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

export type VoteResponse = {
  // The updated widget of the standalone poll.
  template: string;
};

export async function vote(pollId: number, optionIDs: number[]): Promise<VoteResponse> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/polls/${pollId}/vote`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).post({ optionIDs }).fetchAsJson();
  });
}
