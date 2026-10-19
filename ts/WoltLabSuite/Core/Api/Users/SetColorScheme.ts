/**
 * Sets the preferred color scheme of the active user.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";
import type { ColorSchemePreference } from "../../Controller/Style/ColorScheme";

export function setColorScheme(colorScheme: ColorSchemePreference): Promise<[]> {
  return fromInfallibleApiRequest(() => {
    return prepareRequest(`${window.WSC_RPC_API_URL}core/users/color-scheme`)
      .post({ colorScheme })
      .disableLoadingIndicator()
      .fetchAsJson();
  });
}
