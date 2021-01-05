/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/BootstrapFrontend
 */

import * as BackgroundQueue from "./BackgroundQueue";
import * as Bootstrap from "./Bootstrap";
import * as ControllerStyleChanger from "./Controller/Style/Changer";
import * as ControllerPopover from "./Controller/Popover";
import * as UiUserIgnore from "./Ui/User/Ignore";
import * as UiPageHeaderMenu from "./Ui/Page/Header/Menu";
import * as UiMessageUserConsent from "./Ui/Message/UserConsent";

interface BoostrapOptions {
  backgroundQueue: {
    url: string;
    force: boolean;
  };
  enableUserPopover: boolean;
  styleChanger: boolean;
}

/**
 * Initializes user profile popover.
 */
function _initUserPopover(): void {
  ControllerPopover.init({
    className: "userLink",
    dboAction: "wcf\\data\\user\\UserProfileAction",
    identifier: "com.woltlab.wcf.user",
  });

  // @deprecated since 5.3
  ControllerPopover.init({
    attributeName: "data-user-id",
    className: "userLink",
    dboAction: "wcf\\data\\user\\UserProfileAction",
    identifier: "com.woltlab.wcf.user.deprecated",
  });
}

/**
 * Bootstraps general modules and frontend exclusive ones.
 */
export function setup(options: BoostrapOptions): void {
  // Modify the URL of the background queue URL to always target the current domain to avoid CORS.
  options.backgroundQueue.url = window.WSC_API_URL + options.backgroundQueue.url.substr(window.WCF_PATH.length);

  Bootstrap.setup({ enableMobileMenu: true });
  UiPageHeaderMenu.init();

  if (options.styleChanger) {
    ControllerStyleChanger.setup();
  }

  if (options.enableUserPopover) {
    _initUserPopover();
  }

  BackgroundQueue.setUrl(options.backgroundQueue.url);
  if (Math.random() < 0.1 || options.backgroundQueue.force) {
    // invoke the queue roughly every 10th request or on demand
    BackgroundQueue.invoke();
  }

  if (globalThis.COMPILER_TARGET_DEFAULT) {
    UiUserIgnore.init();
  }

  UiMessageUserConsent.init();
}
