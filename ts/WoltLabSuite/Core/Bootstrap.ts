/**
 * Bootstraps WCF's JavaScript.
 * It defines globals needed for backwards compatibility
 * and runs modules that are needed on page load.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2023 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as Core from "./Core";
import DatePicker from "./Date/Picker";
import Devtools from "./Devtools";
import DomChangeListener from "./Dom/Change/Listener";
import * as Environment from "./Environment";
import * as EventHandler from "./Event/Handler";
import * as XsrfToken from "./Form/XsrfToken";
import * as Language from "./Language";
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
import * as UiObjectActionToggle from "./Ui/Object/Action/Toggle";
import { init as initSearch } from "./Ui/Search";
import { PageMenuMainProvider } from "./Ui/Page/Menu/Main/Provider";
import { whenFirstSeen } from "./LazyLoader";
import { adoptPageOverlayContainer, getPageOverlayContainer } from "./Helper/PageOverlay";

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
  dynamicColorScheme: boolean;
  enableMobileMenu: boolean;
  pageMenuMainProvider: PageMenuMainProvider;
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
      colorScheme: "light",
      enableMobileMenu: true,
      pageMenuMainProvider: undefined,
    },
    options,
  ) as BoostrapOptions;

  XsrfToken.setup();

  if (window.ENABLE_DEVELOPER_TOOLS) {
    Devtools._internal_.enable();
  }

  adoptPageOverlayContainer(document.body);

  Environment.setup();
  DatePicker.init();
  UiDropdownSimple.setup();
  UiMobile.setup(options.enableMobileMenu, options.pageMenuMainProvider);
  UiTabMenu.setup();
  UiDialog.setup();
  UiTooltip.setup();
  UiPassword.setup();
  UiEmpty.setup();
  UiObjectAction.setup();
  UiObjectActionDelete.setup();
  UiObjectActionToggle.setup();
  initSearch();

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

  window.requestAnimationFrame(() => {
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.documentElement.style.setProperty("--scrollbar-width", `${scrollbarWidth}px`);
  });

  initA11y();

  DomChangeListener.add("WoltLabSuite/Core/Bootstrap", () => initA11y);

  if (options.dynamicColorScheme) {
    void import("./Controller/Style/ColorScheme").then(({ setup }) => {
      setup();
    });
  }

  whenFirstSeen("[data-report-content]", () => {
    void import("./Ui/Moderation/Report").then(({ setup }) => setup());
  });

  whenFirstSeen("woltlab-core-pagination", () => {
    void import("./Ui/Pagination/JumpToPage").then(({ setup }) => setup());
  });

  whenFirstSeen("woltlab-core-google-maps", () => {
    void import("./Component/GoogleMaps/woltlab-core-google-maps");
  });
  whenFirstSeen("[data-google-maps-geocoding]", () => {
    void import("./Component/GoogleMaps/Geocoding").then(({ setup }) => setup());
  });

  // Move the reCAPTCHA widget overlay to the `pageOverlayContainer`
  // when widget form elements are placed in a dialog.
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (!(node instanceof HTMLElement)) {
          continue;
        }

        if (node.querySelector(".g-recaptcha-bubble-arrow") === null) {
          continue;
        }

        const iframe = node.querySelector("iframe");
        if (!iframe) {
          continue;
        }
        const name = "a-" + iframe.name.split("-")[1];
        const widget = document.querySelector(`iframe[name="${name}"]`);
        if (!widget) {
          continue;
        }
        const dialog = widget.closest("woltlab-core-dialog");
        if (!dialog) {
          continue;
        }

        getPageOverlayContainer().append(node);
        node.classList.add("g-recaptcha-container");
      }
    }
  });
  observer.observe(document.body, {
    childList: true,
  });
}
