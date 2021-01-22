/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Bootstrap
 */

import * as Core from "../Core";
import { BoostrapOptions, setup as bootstrapSetup } from "../Bootstrap";
import * as UiPageMenu from "./Ui/Page/Menu";

interface AcpBootstrapOptions {
  bootstrap: BoostrapOptions;
}

/**
 * Bootstraps general modules and frontend exclusive ones.
 *
 * @param  {Object=}  options    bootstrap options
 */
export function setup(options: AcpBootstrapOptions): void {
  options = Core.extend(
    {
      bootstrap: {
        enableMobileMenu: true,
      },
    },
    options,
  ) as AcpBootstrapOptions;

  bootstrapSetup(options.bootstrap);
  UiPageMenu.init();
}
