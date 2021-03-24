/**
 * Bootstraps WCF's JavaScript.
 * It defines globals needed for backwards compatibility
 * and runs modules that are needed on page load.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Bootstrap
 */

import * as Core from "./Core";
import DatePicker from "./Date/Picker";
import * as DateTimeRelative from "./Date/Time/Relative";
import Devtools from "./Devtools";
import DomChangeListener from "./Dom/Change/Listener";
import * as Environment from "./Environment";
import * as EventHandler from "./Event/Handler";
import * as Language from "./Language";
import * as StringUtil from "./StringUtil";
import UiDialog from "./Ui/Dialog";
import UiDropdownSimple from "./Ui/Dropdown/Simple";
import * as UiMobile from "./Ui/Mobile";
import * as UiPageAction from "./Ui/Page/Action";
import * as UiTabMenu from "./Ui/TabMenu";
import * as UiTooltip from "./Ui/Tooltip";
import * as UiPageJumpTo from "./Ui/Page/JumpTo";
import * as UiPassword from "./Ui/Password";
import * as UiEmpty from "./Ui/Empty";
import * as UiObjectAction from "./Ui/Object/Action";
import * as UiObjectActionDelete from "./Ui/Object/Action/Delete";
import * as UiObjectActionToggle from "./Ui/Object/Action/Toogle";

// perfectScrollbar does not need to be bound anywhere, it just has to be loaded for WCF.js
import "perfect-scrollbar";

// non strict equals by intent
if (window.WCF == null) {
  window.WCF = {};
}
if (window.WCF.Language == null) {
  window.WCF.Language = {};
}
window.WCF.Language.get = Language.get;
window.WCF.Language.add = Language.add;
window.WCF.Language.addObject = Language.addObject;
// WCF.System.Event compatibility
window.__wcf_bc_eventHandler = EventHandler;

export interface BoostrapOptions {
  enableMobileMenu: boolean;
}

function initA11y() {
  document
    .querySelectorAll("nav:not([aria-label]):not([aria-labelledby]):not([role])")
    .forEach((element: HTMLElement) => {
      element.setAttribute("role", "presentation");
    });

  document
    .querySelectorAll("article:not([aria-label]):not([aria-labelledby]):not([role])")
    .forEach((element: HTMLElement) => {
      element.setAttribute("role", "presentation");
    });
}

/**
 * Initializes the core UI modifications and unblocks jQuery's ready event.
 */
export function setup(options: BoostrapOptions): void {
  options = Core.extend(
    {
      enableMobileMenu: true,
    },
    options,
  ) as BoostrapOptions;

  StringUtil.setupI18n({
    decimalPoint: Language.get("wcf.global.decimalPoint"),
    thousandsSeparator: Language.get("wcf.global.thousandsSeparator"),
  });

  if (window.ENABLE_DEVELOPER_TOOLS) {
    Devtools._internal_.enable();
  }

  Environment.setup();
  DateTimeRelative.setup();
  DatePicker.init();
  UiDropdownSimple.setup();
  UiMobile.setup(options.enableMobileMenu);
  UiTabMenu.setup();
  UiDialog.setup();
  UiTooltip.setup();
  UiPassword.setup();
  UiEmpty.setup();
  UiObjectAction.setup();
  UiObjectActionDelete.setup();
  UiObjectActionToggle.setup();

  // Convert forms with `method="get"` into `method="post"`
  document.querySelectorAll("form[method=get]").forEach((form: HTMLFormElement) => {
    form.method = "post";
  });

  if (Environment.browser() === "microsoft") {
    window.onbeforeunload = () => {
      /* Prevent "Back navigation caching" (http://msdn.microsoft.com/en-us/library/ie/dn265017%28v=vs.85%29.aspx) */
    };
  }

  let interval = 0;
  interval = window.setInterval(() => {
    if (typeof window.jQuery === "function") {
      window.clearInterval(interval);

      // The 'jump to top' button triggers a style recalculation/"layout".
      // Placing it at the end of the jQuery queue avoids trashing the
      // layout too early and thus delaying the page initialization.
      window.jQuery(() => {
        UiPageAction.setup();
      });

      // jQuery.browser.mobile is a deprecated legacy property that was used
      // to determine the class of devices being used.
      const jq = window.jQuery as any;
      jq.browser = jq.browser || {};
      jq.browser.mobile = Environment.platform() !== "desktop";

      window.jQuery.holdReady(false);
    }
  }, 20);

  document.querySelectorAll(".pagination").forEach((el: HTMLElement) => UiPageJumpTo.init(el));

  initA11y();

  DomChangeListener.add("WoltLabSuite/Core/Bootstrap", () => initA11y);
}
