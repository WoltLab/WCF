/**
 * Bootstraps WCF's JavaScript with additions for the ACP usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "../Core";
import { BoostrapOptions, setup as bootstrapSetup } from "../Bootstrap";
import * as UiPageMenu from "./Ui/Page/Menu";
import AcpUiPageMenuMainBackend from "./Ui/Page/Menu/Main/Backend";
import { setup as setupAcpSearch } from "./Ui/Search";

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
        pageMenuMainProvider: new AcpUiPageMenuMainBackend(),
      },
    },
    options,
  ) as AcpBootstrapOptions;

  bootstrapSetup(options.bootstrap);
  UiPageMenu.init();
  setupAcpSearch();
  rewriteLinks();
}

function rewriteLinks(): void {
  document.querySelectorAll("a").forEach((link) => {
    // Legacy links using the `dereferrer.php` service, see https://github.com/WoltLab/WCF/issues/2557
    if (/\/dereferrer\.php$/.test(link.pathname)) {
      const match = /^\?url=([^&=]+)$/.exec(link.search);
      if (match !== null) {
        link.href = unescape(match[1]);
      }
    }

    if (link.classList.contains("externalURL")) {
      link.relList.add("noopener");
    }
  });
}
