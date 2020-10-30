/**
 * Provides the touch-friendly fullscreen main menu.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Page/Menu/Main
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../Language", "./Abstract"], function (require, exports, tslib_1, Util_1, Language, Abstract_1) {
    "use strict";
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Abstract_1 = tslib_1.__importDefault(Abstract_1);
    class UiPageMenuMain extends Abstract_1.default {
        /**
         * Initializes the touch-friendly fullscreen main menu.
         */
        constructor() {
            super("com.woltlab.wcf.MainMenuMobile", "pageMainMenuMobile", "#pageHeader .mainMenu");
            this.hasItems = false;
            this.title = document.getElementById("pageMainMenuMobilePageOptionsTitle");
            if (this.title !== null) {
                this.navigationList = document.querySelector(".jsPageNavigationIcons");
            }
            this.button.setAttribute("aria-label", Language.get("wcf.menu.page"));
            this.button.setAttribute("role", "button");
        }
        open(event) {
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
                Util_1.default.show(this.title);
            }
            else {
                Util_1.default.hide(this.title);
            }
            return true;
        }
        close(event) {
            if (!super.close(event)) {
                return false;
            }
            if (this.hasItems) {
                Util_1.default.hide(this.title);
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
    return UiPageMenuMain;
});
