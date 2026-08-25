/**
 * Handles the object watch button.
 *
 * @author	Marcel Werk
 * @copyright	2001-2022 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/User/ObjectWatch` instead.
 */

import { setup as setupObjectWatch } from "WoltLabSuite/Core/Component/User/ObjectWatch";

export function setup(): void {
  setupObjectWatch();
}
