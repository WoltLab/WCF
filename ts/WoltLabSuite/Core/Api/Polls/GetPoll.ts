/**
 * Gets the data of a standalone poll that is required to embed it.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

export type PollData = {
  pollID: number;
  question: string;
  votes: number;
  time: number;
  endTime: number;
  isFinished: boolean;
};

export async function getPoll(pollId: number): Promise<PollData> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/polls/${pollId}`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
