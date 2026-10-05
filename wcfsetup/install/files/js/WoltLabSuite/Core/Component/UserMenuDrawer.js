/**
 * Shows the control panel and the user menus (notifications, moderation, conversations, …)
 * as tabs inside the user drawer of the `system_pageHeader` on small screens.
 *
 * The tabs are built on every open from the registered user menus, their panels borrow
 * the elements that are shown as dropdowns on large screens.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
define(["require", "exports", "tslib", "../Ui/User/Menu/Manager", "../Ui/User/Menu/ControlPanel", "../Ui/Screen", "../Dom/Util", "./Drawer"], function (require, exports, tslib_1, Manager_1, ControlPanel_1, Screen_1, Util_1, Drawer_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    const CONTROL_PANEL = "userMenu";
    let drawer;
    let tabList;
    let panel;
    let tabs = [];
    let activeTab = undefined;
    let borrowed = undefined;
    function getProviders() {
        // The notifications come first, the others keep the order of the user panel.
        return Array.from((0, Manager_1.getUserMenuProviders)()).sort((a, b) => {
            if (a.getIdentifier() === "com.woltlab.wcf.notifications") {
                return -1;
            }
            if (b.getIdentifier() === "com.woltlab.wcf.notifications") {
                return 1;
            }
            return a.getPanelButton().compareDocumentPosition(b.getPanelButton()) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
        });
    }
    function createTab(origin, provider, icon) {
        const link = origin.querySelector("a");
        const button = document.createElement("button");
        button.type = "button";
        button.id = Util_1.default.getUniqueId();
        button.classList.add("userMenuDrawerTab");
        button.dataset.origin = origin.id;
        button.setAttribute("role", "tab");
        button.setAttribute("aria-controls", panel.id);
        button.setAttribute("aria-selected", "false");
        button.tabIndex = -1;
        button.innerHTML = icon;
        const label = document.createElement("span");
        label.classList.add("userMenuDrawerTabLabel");
        // `jsTooltip` moves the title into `data-tooltip`.
        label.textContent = link.dataset.tooltip || link.title;
        button.append(label);
        const tab = { button, origin, provider };
        button.addEventListener("click", () => selectTab(tab));
        button.addEventListener("keydown", (event) => keydown(event, tab));
        return tab;
    }
    function refreshCounters() {
        for (const { button, origin } of tabs) {
            const counter = origin.querySelector(".badge")?.textContent?.trim() ?? "";
            let badge = button.querySelector(".userMenuDrawerTabCount");
            if (counter === "") {
                badge?.remove();
            }
            else {
                if (badge === null) {
                    badge = document.createElement("span");
                    badge.classList.add("userMenuDrawerTabCount");
                    button.append(badge);
                }
                badge.textContent = counter;
            }
        }
    }
    function buildTabs() {
        tabList.innerHTML = "";
        tabs = [];
        const controlPanel = document.getElementById(CONTROL_PANEL);
        tabs.push(createTab(controlPanel, undefined, '<fa-icon size="24" name="user"></fa-icon>'));
        for (const provider of getProviders()) {
            const origin = provider.getPanelButton();
            const icon = origin.querySelector("fa-icon")?.outerHTML ?? '<fa-icon size="24" name="question"></fa-icon>';
            tabs.push(createTab(origin, provider, icon));
        }
        tabList.append(...tabs.map(({ button }) => button));
        refreshCounters();
    }
    function returnBorrowedElement() {
        if (borrowed === undefined) {
            return;
        }
        if (borrowed.provider !== undefined) {
            // Also releases the focus trap of the view.
            borrowed.provider.getView().close();
        }
        else {
            borrowed.element.hidden = true;
        }
        borrowed.parent.append(borrowed.element);
        borrowed = undefined;
    }
    function selectTab(tab) {
        if (tab === activeTab) {
            return;
        }
        returnBorrowedElement();
        for (const { button } of tabs) {
            const selected = button === tab.button;
            button.setAttribute("aria-selected", selected ? "true" : "false");
            button.tabIndex = selected ? 0 : -1;
        }
        panel.setAttribute("aria-labelledby", tab.button.id);
        activeTab = tab;
        const element = tab.provider ? tab.provider.getView().getElement() : (0, ControlPanel_1.getElement)();
        // A view that was never opened as a dropdown is not part of the document yet.
        borrowed = { element, parent: element.parentElement ?? (0, Manager_1.getContainer)(), provider: tab.provider };
        panel.append(element);
        if (tab.provider) {
            void tab.provider.getView().open(false);
        }
        else {
            element.hidden = false;
        }
    }
    function keydown(event, tab) {
        const index = tabs.indexOf(tab);
        const previous = index === 0 ? tabs.length - 1 : index - 1;
        const next = index === tabs.length - 1 ? 0 : index + 1;
        const isRtl = document.documentElement.dir === "rtl";
        let target;
        switch (event.key) {
            case "ArrowLeft":
                target = isRtl ? next : previous;
                break;
            case "ArrowRight":
                target = isRtl ? previous : next;
                break;
            case "Home":
                target = 0;
                break;
            case "End":
                target = tabs.length - 1;
                break;
            default:
                return;
        }
        event.preventDefault();
        tabs[target].button.focus();
    }
    function onOpen(detail) {
        buildTabs();
        selectTab(tabs.find(({ origin }) => origin.id === detail?.tab) ?? tabs[0]);
    }
    function onClose() {
        returnBorrowedElement();
        activeTab = undefined;
    }
    function setup() {
        const element = document.getElementById("userMenuDrawer");
        if (element === null) {
            return;
        }
        drawer = element;
        tabList = drawer.querySelector(".userMenuDrawerTabs");
        panel = drawer.querySelector(".userMenuDrawerPanel");
        panel.id = Util_1.default.getUniqueId();
        drawer.addEventListener("drawer:open", (event) => onOpen(event.detail));
        drawer.addEventListener("drawer:close", () => onClose());
        // The counters of the user panel are updated by polling and push notifications.
        new MutationObserver(() => {
            if (activeTab !== undefined) {
                refreshCounters();
            }
        }).observe(document.querySelector(".userPanelItems"), { characterData: true, childList: true, subtree: true });
        // The bell opens the notifications in the drawer instead of the dropdown on small screens.
        const notifications = document.getElementById("userNotifications");
        notifications?.addEventListener("click", (event) => {
            if ((0, Screen_1.is)("screen-lg")) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            (0, Drawer_1.open)(drawer.id, {
                opener: notifications.querySelector("a"),
                detail: { tab: notifications.id },
            });
        }, { capture: true });
    }
});
