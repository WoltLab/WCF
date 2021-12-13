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
            this.tabPanels = new Map();
            this.tabs = [];
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
        refresh() {
            this.openNotifications();
        }
        openNotifications() {
            const notifications = this.tabs.find((element) => element.dataset.origin === "userNotifications");
            if (!notifications) {
                throw new Error("Unable to find the notifications tab.");
            }
            notifications.click();
        }
        openTab(tab) {
            if (tab.getAttribute("aria-selected") === "true") {
                return;
            }
            const activeTab = this.tabs.find((element) => element.getAttribute("aria-selected") === "true");
            if (activeTab) {
                activeTab.setAttribute("aria-selected", "false");
                activeTab.tabIndex = -1;
                const activePanel = this.tabPanels.get(activeTab);
                activePanel.hidden = true;
            }
            tab.setAttribute("aria-selected", "true");
            tab.tabIndex = 0;
            const tabPanel = this.tabPanels.get(tab);
            tabPanel.hidden = false;
            if (document.activeElement !== tab) {
                tab.focus();
            }
        }
        keydown(event) {
            const tab = event.currentTarget;
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                this.openTab(tab);
                return;
            }
            const navigationKeyEvents = ["ArrowLeft", "ArrowRight", "End", "Home"];
            if (!navigationKeyEvents.includes(event.key)) {
                return;
            }
            event.preventDefault();
            const currentIndex = this.tabs.indexOf(tab);
            const lastIndex = this.tabs.length - 1;
            let index;
            if (event.key === "ArrowLeft") {
                if (currentIndex === 0) {
                    index = lastIndex;
                }
                else {
                    index = currentIndex - 1;
                }
            }
            else if (event.key === "ArrowRight") {
                if (currentIndex === lastIndex) {
                    index = 0;
                }
                else {
                    index = currentIndex + 1;
                }
            }
            else if (event.key === "End") {
                index = lastIndex;
            }
            else {
                index = 0;
            }
            this.tabs[index].focus();
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
                this.tabs.push(tab);
                this.tabPanels.set(tab, tabPanel);
            });
            // TODO: Inject legacy user panel items.
            return [tabList, tabPanelContainer];
        }
        buildTab(provider) {
            const tabId = Util_1.default.getUniqueId();
            const panelId = Util_1.default.getUniqueId();
            const tab = document.createElement("a");
            tab.classList.add("pageMenuUserTab");
            tab.dataset.origin = provider.getPanelButton().id;
            tab.id = tabId;
            tab.setAttribute("aria-controls", panelId);
            tab.setAttribute("aria-selected", "false");
            tab.setAttribute("role", "tab");
            tab.tabIndex = -1;
            const button = provider.getPanelButton().querySelector("a");
            tab.setAttribute("aria-label", button.dataset.title || button.title);
            tab.innerHTML = button.querySelector(".icon").outerHTML;
            tab.addEventListener("click", (event) => {
                event.preventDefault();
                this.openTab(tab);
            });
            tab.addEventListener("keydown", (event) => this.keydown(event));
            const panel = document.createElement("div");
            panel.classList.add("pageMenuUserTabPanel");
            panel.id = panelId;
            panel.hidden = true;
            panel.setAttribute("aria-labelledby", tabId);
            panel.setAttribute("role", "tabpanel");
            panel.tabIndex = 0;
            panel.textContent = "panel for #" + provider.getPanelButton().id;
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
