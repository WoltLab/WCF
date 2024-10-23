/**
 * Provides the touch-friendly user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "./Container", "../../../Language", "../../User/Menu/Manager", "../../../Dom/Util", "../../User/Menu/ControlPanel", "../../../Event/Handler", "../../Screen"], function (require, exports, tslib_1, Container_1, Language, Manager_1, Util_1, ControlPanel_1, EventHandler, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.PageMenuUser = void 0;
    exports.hasValidUserMenu = hasValidUserMenu;
    Container_1 = tslib_1.__importDefault(Container_1);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    class PageMenuUser {
        activeTab = undefined;
        container;
        legacyUserPanels = new Map();
        observer;
        userMenuProviders = new Map();
        tabOrigins = new Map();
        tabPanels = new Map();
        tabs = [];
        userMenu;
        userMenuButton;
        constructor() {
            this.userMenu = document.querySelector(".userPanel");
            this.userMenuButton = document.querySelector(".pageHeaderUserMobile");
            this.userMenuButton.addEventListener("click", (event) => {
                event.stopPropagation();
                // Clicking too early while the page is still loading
                // causes an incomplete tab menu.
                void isReady.then(() => this.container.toggle());
            });
            this.container = new Container_1.default(this);
            const isReady = new Promise((resolve) => {
                if (document.readyState === "complete") {
                    resolve();
                }
                else {
                    document.addEventListener("readystatechange", () => {
                        if (document.readyState === "complete") {
                            resolve();
                        }
                    });
                }
            });
            (0, Screen_1.on)("screen-lg", {
                match: () => this.detachViewsFromPanel(),
                unmatch: () => this.detachViewsFromPanel(),
            });
            this.observer = new MutationObserver(() => {
                this.refreshTabUnreadIndicators();
            });
        }
        enable() {
            this.userMenuButton.setAttribute("aria-expanded", "false");
            this.refreshUnreadIndicator();
        }
        disable() {
            this.container.close();
            this.userMenuButton.setAttribute("aria-expanded", "false");
        }
        getContent() {
            const fragment = document.createDocumentFragment();
            fragment.append(this.buildTabMenu());
            return fragment;
        }
        getMenuButton() {
            return this.userMenuButton;
        }
        sleep() {
            if (this.activeTab) {
                this.closeTab(this.activeTab);
            }
            this.detachViewsFromPanel();
            this.refreshUnreadIndicator();
        }
        wakeup() {
            if (this.activeTab) {
                // The UI elements in the tab panel are shared and can appear in a different
                // context. The element might have been moved elsewhere while the menu was
                // closed.
                this.openTab(this.activeTab);
            }
            else {
                if (this.isInMaintenanceMode()) {
                    this.openTab(this.tabs[0]);
                }
                else {
                    this.openNotifications();
                }
            }
            this.refreshTabUnreadIndicators();
            this.refreshUnreadIndicator();
        }
        isInMaintenanceMode() {
            return document.body.dataset.application === "wcf" && document.body.dataset.template === "offline";
        }
        openNotifications() {
            const notifications = this.tabs.find((element) => element.dataset.origin === "userNotifications");
            if (!notifications) {
                throw new Error("Unable to find the notifications tab.");
            }
            this.openTab(notifications);
        }
        openTab(tab) {
            this.closeActiveTab();
            tab.setAttribute("aria-selected", "true");
            tab.tabIndex = 0;
            const tabPanel = this.tabPanels.get(tab);
            tabPanel.hidden = false;
            if (document.activeElement !== tab) {
                tab.focus();
            }
            this.attachViewToPanel(tab);
            this.activeTab = tab;
            this.observer.observe(tabPanel, {
                attributeFilter: ["data-is-unread"],
                childList: true,
                subtree: true,
            });
        }
        closeActiveTab() {
            if (!this.activeTab) {
                return;
            }
            this.closeTab(this.activeTab);
            this.activeTab = undefined;
        }
        closeTab(tab) {
            tab.setAttribute("aria-selected", "false");
            tab.tabIndex = -1;
            const tabPanel = this.tabPanels.get(tab);
            tabPanel.hidden = true;
            const legacyPanel = this.legacyUserPanels.get(tab);
            if (legacyPanel) {
                legacyPanel.close();
            }
            this.observer.disconnect();
            this.refreshTabUnreadIndicators();
        }
        attachViewToPanel(tab) {
            const origin = tab.dataset.origin;
            const tabPanel = this.tabPanels.get(tab);
            if (origin === "userMenu") {
                const element = (0, ControlPanel_1.getElement)();
                element.hidden = false;
                if (tabPanel.childElementCount === 0) {
                    this.tabOrigins.set(tabPanel, element.parentElement);
                    tabPanel.append(element);
                }
            }
            else {
                if (tabPanel.childElementCount === 0) {
                    const provider = this.userMenuProviders.get(tab);
                    if (provider) {
                        const view = provider.getView();
                        const element = view.getElement();
                        this.tabOrigins.set(tabPanel, element.parentElement);
                        tabPanel.append(element);
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
        detachViewsFromPanel() {
            this.tabPanels.forEach((tabPanel, tab) => {
                if (tabPanel.childElementCount) {
                    const parent = this.tabOrigins.get(tabPanel);
                    if (parent) {
                        const origin = tab.dataset.origin;
                        if (origin === "userMenu") {
                            const element = tabPanel.children[0];
                            element.hidden = true;
                            parent.append(element);
                        }
                        else {
                            const provider = this.userMenuProviders.get(tab);
                            if (provider) {
                                const view = provider.getView();
                                const element = view.getElement();
                                element.hidden = true;
                                parent.append(element);
                            }
                        }
                    }
                }
            });
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
            tabList.setAttribute("aria-label", Language.get("wcf.menu.user"));
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
            let icon = button.querySelector("fa-icon")?.outerHTML;
            if (icon === undefined) {
                // Fallback for the upgrade to 6.0.
                icon = '<fa-icon size="32" name="question"></fa-icon>';
            }
            const data = {
                icon,
                label: button.dataset.title || button.title,
                origin: panelButton.id,
            };
            return this.buildTabComponents(data);
        }
        buildControlPanelTab(tabList, tabContainer) {
            const userMenu = document.getElementById("userMenu");
            const userMenuButton = userMenu.querySelector("a");
            const data = {
                icon: this.userMenuButton.querySelector(".userAvatarImage").outerHTML,
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
            const tab = document.createElement("button");
            tab.type = "button";
            tab.classList.add("pageMenuUserTab");
            tab.dataset.hasUnreadContent = "false";
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
        refreshUnreadIndicator() {
            const hasUnreadItems = this.userMenu.querySelector(".badge.badgeUpdate") !== null;
            if (hasUnreadItems) {
                this.userMenu.classList.add("pageMenuMobileButtonHasContent");
            }
            else {
                this.userMenu.classList.remove("pageMenuMobileButtonHasContent");
            }
        }
        refreshTabUnreadIndicators() {
            this.userMenuProviders.forEach((provider, tab) => {
                if (provider.hasUnreadContent()) {
                    tab.dataset.hasUnreadContent = "true";
                }
                else {
                    tab.dataset.hasUnreadContent = "false";
                }
            });
        }
    }
    exports.PageMenuUser = PageMenuUser;
    function hasValidUserMenu() {
        const panel = document.getElementById("topMenu");
        return panel.classList.contains("userPanelLoggedIn");
    }
    exports.default = PageMenuUser;
});
