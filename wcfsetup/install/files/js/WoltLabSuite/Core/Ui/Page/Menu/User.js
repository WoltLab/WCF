/**
 * Provides the touch-friendly user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/User
 */
define(["require", "exports", "tslib", "./Container", "../../../Language", "../../User/Menu/Manager", "../../../Dom/Util", "../../User/Menu/ControlPanel", "../../../Event/Handler"], function (require, exports, tslib_1, Container_1, Language, Manager_1, Util_1, ControlPanel_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.hasValidUserMenu = exports.PageMenuUser = void 0;
    Container_1 = (0, tslib_1.__importDefault)(Container_1);
    Language = (0, tslib_1.__importStar)(Language);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    EventHandler = (0, tslib_1.__importStar)(EventHandler);
    class PageMenuUser {
        constructor() {
            this.legacyUserPanels = new Map();
            this.userMenuProviders = new Map();
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
            fragment.append(this.buildTabMenu());
            return fragment;
        }
        getMenuButton() {
            return this.userMenu;
        }
        refresh() {
            const activeTab = this.tabs.find((element) => element.getAttribute("aria-selected") === "true");
            if (activeTab === undefined) {
                this.openNotifications();
            }
            else {
                // The UI elements in the tab panel are shared and can appear in a different
                // context. The element might have been moved elsewhere while the menu was
                // closed.
                this.attachViewToPanel(activeTab);
            }
        }
        openNotifications() {
            const notifications = this.tabs.find((element) => element.dataset.origin === "userNotifications");
            if (!notifications) {
                throw new Error("Unable to find the notifications tab.");
            }
            this.openTab(notifications);
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
            this.attachViewToPanel(tab);
        }
        attachViewToPanel(tab) {
            const origin = tab.dataset.origin;
            const tabPanel = this.tabPanels.get(tab);
            if (origin === "userMenu") {
                const element = (0, ControlPanel_1.getElement)();
                element.hidden = false;
                if (tabPanel.childElementCount === 0) {
                    tabPanel.append(element);
                }
            }
            else {
                if (tabPanel.childElementCount === 0) {
                    const provider = this.userMenuProviders.get(tab);
                    if (provider) {
                        const view = provider.getView();
                        tabPanel.append(view.getElement());
                        void view.open();
                    }
                    else {
                        const legacyPanel = this.legacyUserPanels.get(tab);
                        legacyPanel.open();
                        const { top } = tabPanel.getBoundingClientRect();
                        const container = legacyPanel.getDropdown().getContainer()[0];
                        container.style.setProperty("--offset-top", `${top}px`);
                    }
                }
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
            const tabContainer = document.createElement("div");
            tabContainer.classList.add("pageMenuUserTabContainer");
            const tabList = document.createElement("div");
            tabList.classList.add("pageMenuUserTabList");
            tabList.setAttribute("role", "tablist");
            tabList.setAttribute("aria-label", Language.get("TODO"));
            tabContainer.append(tabList);
            this.buildControlPanelTab(tabList, tabContainer);
            (0, Manager_1.getUserMenuProviders)().forEach((provider) => {
                const [tab, tabPanel] = this.buildTab(provider);
                tabList.append(tab);
                tabContainer.append(tabPanel);
                this.tabs.push(tab);
                this.tabPanels.set(tab, tabPanel);
                this.userMenuProviders.set(tab, provider);
            });
            this.buildLegacyTabs(tabList, tabContainer);
            return tabContainer;
        }
        buildTab(provider) {
            const panelButton = provider.getPanelButton();
            const button = panelButton.querySelector("a");
            const data = {
                icon: button.querySelector(".icon").outerHTML,
                label: button.dataset.title || button.title,
                origin: panelButton.id,
            };
            return this.buildTabComponents(data);
        }
        buildControlPanelTab(tabList, tabContainer) {
            const panel = document.getElementById("topMenu");
            const userMenu = document.getElementById("userMenu");
            const userMenuButton = userMenu.querySelector("a");
            const data = {
                icon: panel.querySelector(".userPanelAvatar .userAvatarImage").outerHTML,
                label: userMenuButton.dataset.title || userMenuButton.title,
                origin: userMenu.id,
            };
            const [tab, tabPanel] = this.buildTabComponents(data);
            tabList.append(tab);
            tabContainer.append(tabPanel);
            this.tabs.push(tab);
            this.tabPanels.set(tab, tabPanel);
        }
        buildLegacyTabs(tabList, tabContainer) {
            const userPanelItems = document.querySelector(".userPanelItems");
            const legacyPanelData = {
                panels: [],
            };
            EventHandler.fire("com.woltlab.wcf.pageMenu", "legacyMenu", legacyPanelData);
            Array.from(userPanelItems.children)
                .filter((listItem) => {
                const element = legacyPanelData.panels.find((panel) => panel.element === listItem);
                return element !== undefined;
            })
                .map((listItem) => {
                const button = listItem.querySelector("a");
                return {
                    icon: button.querySelector(".icon").outerHTML,
                    label: button.dataset.title || button.title,
                    origin: listItem.id,
                };
            })
                .forEach((data) => {
                const [tab, tabPanel] = this.buildTabComponents(data);
                tabList.append(tab);
                tabContainer.append(tabPanel);
                this.tabs.push(tab);
                this.tabPanels.set(tab, tabPanel);
                const legacyPanel = legacyPanelData.panels.find((panel) => panel.element.id === data.origin);
                this.legacyUserPanels.set(tab, legacyPanel.api);
            });
        }
        buildTabComponents(data) {
            const tabId = Util_1.default.getUniqueId();
            const panelId = Util_1.default.getUniqueId();
            const tab = document.createElement("a");
            tab.classList.add("pageMenuUserTab");
            tab.dataset.origin = data.origin;
            tab.id = tabId;
            tab.setAttribute("aria-controls", panelId);
            tab.setAttribute("aria-selected", "false");
            tab.setAttribute("role", "tab");
            tab.tabIndex = -1;
            tab.setAttribute("aria-label", data.label);
            tab.innerHTML = data.icon;
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
