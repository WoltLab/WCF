/**
 * Provides the touch-friendly user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/User
 */
define(["require", "exports", "tslib", "./Container", "../../../Language", "../../User/Menu/Manager", "../../../Dom/Util"], function (require, exports, tslib_1, Container_1, Language, Manager_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.hasValidUserMenu = exports.PageMenuUser = void 0;
    Container_1 = (0, tslib_1.__importDefault)(Container_1);
    Language = (0, tslib_1.__importStar)(Language);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    class PageMenuUser {
        constructor() {
            this.userMenu = document.querySelector(".userPanel");
            this.container = new Container_1.default(this);
            this.callbackOpen = (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.container.toggle();
            };
        }
        enable() {
            this.userMenu.setAttribute("aria-expanded", "false");
            this.userMenu.setAttribute("role", "button");
            this.userMenu.tabIndex = 0;
            this.userMenu.addEventListener("click", this.callbackOpen);
        }
        disable() {
            this.container.close();
            this.userMenu.removeAttribute("aria-expanded");
            this.userMenu.removeAttribute("role");
            this.userMenu.removeAttribute("tabindex");
            this.userMenu.removeEventListener("click", this.callbackOpen);
        }
        getContent() {
            const fragment = document.createDocumentFragment();
            fragment.append(...this.buildTabMenu());
            return fragment;
        }
        getMenuButton() {
            return this.userMenu;
        }
        buildTabMenu() {
            const tabList = document.createElement("div");
            tabList.classList.add("pageMenuUserTabList");
            tabList.setAttribute("role", "tablist");
            tabList.setAttribute("aria-label", Language.get("TODO"));
            const tabPanelContainer = document.createElement("div");
            // TODO: Inject the control panel first.
            (0, Manager_1.getUserMenuProviders)().forEach((provider) => {
                const [tab, tabPanel] = this.buildTab(provider);
                tabList.append(tab);
                tabPanelContainer.append(tabPanel);
            });
            // TODO: Inject legacy user panel items.
            return [tabList, tabPanelContainer];
        }
        buildTab(provider) {
            const tabId = Util_1.default.getUniqueId();
            const panelId = Util_1.default.getUniqueId();
            const tab = document.createElement("a");
            tab.classList.add("pageMenuUserTab");
            tab.id = tabId;
            tab.setAttribute("aria-controls", panelId);
            tab.setAttribute("aria-selected", "false");
            tab.setAttribute("role", "tab");
            tab.tabIndex = -1;
            const button = provider.getPanelButton().querySelector("a");
            tab.setAttribute("aria-label", button.dataset.title || button.title);
            tab.innerHTML = button.querySelector(".icon").outerHTML;
            const panel = document.createElement("div");
            panel.classList.add("pageMenuUserTabPanel");
            panel.id = panelId;
            panel.hidden = true;
            panel.setAttribute("aria-labelledby", tabId);
            panel.setAttribute("role", "tabpanel");
            panel.tabIndex = 0;
            return [tab, panel];
        }
    }
    exports.PageMenuUser = PageMenuUser;
    function hasValidUserMenu() {
        return true;
    }
    exports.hasValidUserMenu = hasValidUserMenu;
    exports.default = PageMenuUser;
});
