/**
 * Provides the touch-friendly fullscreen main menu.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Menu/Main
 */

import * as Core from "../../../Core";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";
import UiPageMenuAbstract from "./Abstract";

class UiPageMenuMain extends UiPageMenuAbstract {
  private hasItems = false;
  private readonly navigationList: HTMLOListElement;
  private readonly title: HTMLElement;

  /**
   * Initializes the touch-friendly fullscreen main menu.
   */
  constructor() {
    super("com.woltlab.wcf.MainMenuMobile", "pageMainMenuMobile", "#pageHeader .mainMenu");

    this.title = document.getElementById("pageMainMenuMobilePageOptionsTitle") as HTMLElement;
    if (this.title !== null) {
      this.navigationList = document.querySelector(".jsPageNavigationIcons") as HTMLOListElement;
    }

    this.button.setAttribute("aria-label", Language.get("wcf.menu.page"));
    this.button.setAttribute("role", "button");
  }

  open(event?: MouseEvent): boolean {
    if (!super.open(event)) {
      return false;
    }

    if (this.title === null) {
      return true;
    }

    this.hasItems = this.navigationList && this.navigationList.childElementCount > 0;

    if (this.hasItems) {
      while (this.navigationList.childElementCount) {
        const item = this.navigationList.children[0];

        item.classList.add("menuOverlayItem", "menuOverlayItemOption");
        item.addEventListener("click", (ev) => {
          ev.stopPropagation();

          this.close();
        });

        const link = item.children[0];
        link.classList.add("menuOverlayItemLink");
        link.classList.add("box24");

        link.children[1].classList.remove("invisible");
        link.children[1].classList.add("menuOverlayItemTitle");

        this.title.insertAdjacentElement("afterend", item);
      }

      DomUtil.show(this.title);
    } else {
      DomUtil.hide(this.title);
    }

    return true;
  }

  close(event?: Event): boolean {
    if (!super.close(event)) {
      return false;
    }

    if (this.hasItems) {
      DomUtil.hide(this.title);

      let item = this.title.nextElementSibling;
      while (item && item.classList.contains("menuOverlayItemOption")) {
        item.classList.remove("menuOverlayItem", "menuOverlayItemOption");
        item.removeEventListener("click", (ev) => {
          ev.stopPropagation();

          this.close();
        });

        const link = item.children[0];
        link.classList.remove("menuOverlayItemLink");
        link.classList.remove("box24");

        link.children[1].classList.add("invisible");
        link.children[1].classList.remove("menuOverlayItemTitle");

        this.navigationList.appendChild(item);

        item = item.nextElementSibling;
      }
    }

    return true;
  }
}

Core.enableLegacyInheritance(UiPageMenuMain);

export = UiPageMenuMain;
