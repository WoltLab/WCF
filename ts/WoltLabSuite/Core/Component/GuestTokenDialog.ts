/**
 * Handles the creation of guest tokens.
 *
 * @author    Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */

import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import User from "WoltLabSuite/Core/User";

type Response = {
  token: string;
};

export async function getGuestToken(): Promise<string | undefined> {
  const { ok, result } = await dialogFactory().usingFormBuilder().fromEndpoint<Response>(User.guestTokenDialogEndpoint);

  if (ok) {
    return result.token;
  }

  return undefined;
}
