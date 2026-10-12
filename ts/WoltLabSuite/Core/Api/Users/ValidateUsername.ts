/**
 * Validates the username for the registration process.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

type Response =
  | {
      ok: true;
    }
  | {
      ok: false;
      error: "invalid" | "notUnique";
    };

export async function validateUsername(username: string): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/users/validate-username`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).post({ username }).fetchAsJson();
  });
}
