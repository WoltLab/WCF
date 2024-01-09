/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as BackgroundQueue from "./BackgroundQueue";
import * as Bootstrap from "./Bootstrap";
import * as ControllerPopover from "./Controller/Popover";
import * as UiUserIgnore from "./Ui/User/Ignore";
import * as UiPageHeaderMenu from "./Ui/Page/Header/Menu";
import * as UiMessageUserConsent from "./Ui/Message/UserConsent";
import * as UiMessageShareDialog from "./Ui/Message/Share/Dialog";
import { ShareProvider, addShareProviders } from "./Ui/Message/Share/Providers";
import * as UiFeedDialog from "./Ui/Feed/Dialog";
import User from "./User";
import UiPageMenuMainFrontend from "./Ui/Page/Menu/Main/Frontend";
import { whenFirstSeen } from "./LazyLoader";
import { prepareRequest } from "./Ajax/Backend";

interface BootstrapOptions {
  backgroundQueue: {
    url: string;
    force: boolean;
  };
  dynamicColorScheme: boolean;
  enableUserPopover: boolean;
  executeCronjobs: string | undefined;
  shareButtonProviders?: ShareProvider[];
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

declare const COMPILER_TARGET_DEFAULT: boolean;

/**
 * Bootstraps general modules and frontend exclusive ones.
 */
export function setup(options: BootstrapOptions): void {
  // Modify the URL of the background queue URL to always target the current domain to avoid CORS.
  options.backgroundQueue.url = window.WSC_API_URL + options.backgroundQueue.url.substr(window.WCF_PATH.length);

  Bootstrap.setup({
    dynamicColorScheme: options.dynamicColorScheme,
    enableMobileMenu: true,
    pageMenuMainProvider: new UiPageMenuMainFrontend(),
  });
  UiPageHeaderMenu.init();

  if (options.styleChanger) {
    void import("./Controller/Style/Changer").then((ControllerStyleChanger) => {
      ControllerStyleChanger.setup();
    });
  }

  if (options.enableUserPopover) {
    _initUserPopover();
  }

  if (options.executeCronjobs !== undefined) {
    void prepareRequest(options.executeCronjobs)
      .get()
      .disableLoadingIndicator()
      .fetchAsResponse()
      .catch(() => {
        /* Ignore errors. */
      });
  }

  BackgroundQueue.setUrl(options.backgroundQueue.url);
  if (Math.random() < 0.1 || options.backgroundQueue.force) {
    // invoke the queue roughly every 10th request or on demand
    BackgroundQueue.invoke();
  }

  if (COMPILER_TARGET_DEFAULT) {
    UiUserIgnore.init();
  }

  UiMessageUserConsent.init();

  if (options.shareButtonProviders) {
    addShareProviders(options.shareButtonProviders);
  }
  UiMessageShareDialog.setup();

  if (User.userId) {
    UiFeedDialog.setup();
  }

  whenFirstSeen("woltlab-core-reaction-summary", () => {
    void import("./Ui/Reaction/SummaryDetails").then(({ setup }) => setup());
  });
  whenFirstSeen("woltlab-core-comment", () => {
    void import("./Component/Comment/woltlab-core-comment");
  });
  whenFirstSeen("woltlab-core-comment-response", () => {
    void import("./Component/Comment/Response/woltlab-core-comment-response");
  });
  whenFirstSeen("[data-follow-user]", () => {
    void import("./Component/User/Follow").then(({ setup }) => setup());
  });
}
