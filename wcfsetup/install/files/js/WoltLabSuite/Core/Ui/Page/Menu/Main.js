/**
 * Provides the touch-friendly main menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/Main
 */
define(["require", "exports", "tslib", "./Container", "../../../Language", "../../../Dom/Util"], function (require, exports, tslib_1, Container_1, Language, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.PageMenuMain = void 0;
    Container_1 = tslib_1.__importDefault(Container_1);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    class PageMenuMain {
        constructor(menuItemProvider) {
            this.mainMenu = document.querySelector(".mainMenu");
            this.menuItemProvider = menuItemProvider;
            this.container = new Container_1.default(this, "left" /* Left */);
            this.callbackOpen = (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.container.toggle();
            };
        }
        enable() {
            this.mainMenu.setAttribute("aria-expanded", "false");
            this.mainMenu.setAttribute("aria-label", Language.get("wcf.menu.page"));
            this.mainMenu.setAttribute("role", "button");
            this.mainMenu.tabIndex = 0;
            this.mainMenu.addEventListener("click", this.callbackOpen);
            this.refreshUnreadIndicator();
        }
        disable() {
            this.container.close();
            this.mainMenu.removeAttribute("aria-expanded");
            this.mainMenu.removeAttribute("aria-label");
            this.mainMenu.removeAttribute("role");
            this.mainMenu.removeAttribute("tabindex");
            this.mainMenu.removeEventListener("click", this.callbackOpen);
        }
        getContent() {
            const container = document.createElement("div");
            container.classList.add("pageMenuMainContainer");
            container.addEventListener("scroll", () => this.updateOverflowIndicator(container), { passive: true });
            container.append(this.buildMainMenu());
            const footerMenu = this.buildFooterMenu();
            if (footerMenu) {
                container.append(footerMenu);
            }
            // Detect changes to the height of the children, for example, when a submenu is being expanded.
            const observer = new ResizeObserver(() => this.updateOverflowIndicator(container));
            Array.from(container.children).forEach((menu) => {
                observer.observe(menu);
            });
            const fragment = document.createDocumentFragment();
            fragment.append(container);
            return fragment;
        }
        getMenuButton() {
            return this.mainMenu;
        }
        sleep() {
            /* Does nothing */
        }
        wakeup() {
            this.refreshUnreadIndicator();
        }
        buildMainMenu() {
            const boxMenu = this.mainMenu.querySelector(".boxMenu");
            const nav = this.buildMenu(boxMenu);
            nav.setAttribute("aria-label", window.PAGE_TITLE);
            nav.setAttribute("role", "navigation");
            this.showActiveMenuItem(nav);
            return nav;
        }
        showActiveMenuItem(menu) {
            const activeMenuItem = menu.querySelector('.pageMenuMainItemLink[aria-current="page"]');
            if (activeMenuItem) {
                let element = activeMenuItem;
                while (element && element.parentElement) {
                    element = element.parentElement.closest(".pageMenuMainItemList");
                    if (element) {
                        element.hidden = false;
                        const button = element.previousElementSibling;
                        button === null || button === void 0 ? void 0 : button.setAttribute("aria-expanded", "true");
                    }
                }
            }
        }
        buildFooterMenu() {
            const box = document.querySelector('.box[data-box-identifier="com.woltlab.wcf.FooterMenu"]');
            if (box === null) {
                return null;
            }
            const boxMenu = box.querySelector(".boxMenu");
            const nav = this.buildMenu(boxMenu);
            nav.classList.add("pageMenuMainNavigationFooter");
            const label = box.querySelector("nav").getAttribute("aria-label");
            nav.setAttribute("aria-label", label);
            return nav;
        }
        buildMenu(boxMenu) {
            const menuItems = this.menuItemProvider.getMenuItems(boxMenu);
            const nav = document.createElement("nav");
            nav.classList.add("pageMenuMainNavigation");
            nav.append(this.buildMenuItemList(menuItems));
            return nav;
        }
        buildMenuItemList(menuItems) {
            const list = document.createElement("ul");
            list.classList.add("pageMenuMainItemList");
            menuItems
                .filter((menuItem) => {
                // Remove links that have no target (`#`) and do not contain any children.
                if (!menuItem.link && menuItem.children.length === 0) {
                    return false;
                }
                return true;
            })
                .forEach((menuItem) => {
                list.append(this.buildMenuItem(menuItem));
            });
            return list;
        }
        buildMenuItem(menuItem) {
            const listItem = document.createElement("li");
            listItem.dataset.depth = menuItem.depth.toString();
            listItem.classList.add("pageMenuMainItem");
            if (menuItem.link) {
                const link = document.createElement("a");
                link.classList.add("pageMenuMainItemLink");
                link.href = menuItem.link;
                link.textContent = menuItem.title;
                if (menuItem.active) {
                    link.setAttribute("aria-current", "page");
                }
                if (menuItem.counter > 0) {
                    const counter = document.createElement("span");
                    counter.classList.add("pageMenuMainItemCounter", "badge", "badgeUpdate");
                    counter.setAttribute("aria-hidden", "true");
                    counter.textContent = menuItem.counter.toString();
                    link.append(counter);
                }
                listItem.append(link);
            }
            else {
                const label = document.createElement("a");
                label.classList.add("pageMenuMainItemLabel");
                label.href = "#";
                label.textContent = menuItem.title;
                label.addEventListener("click", (event) => {
                    event.preventDefault();
                    const button = label.nextElementSibling;
                    button.click();
                });
                // The button to expand the link group is used instead.
                label.setAttribute("aria-hidden", "true");
                listItem.append(label);
            }
            if (menuItem.children.length) {
                listItem.classList.add("pageMenuMainItemExpandable");
                const menuId = Util_1.default.getUniqueId();
                const button = document.createElement("a");
                button.classList.add("pageMenuMainItemToggle");
                button.tabIndex = 0;
                button.setAttribute("role", "button");
                button.setAttribute("aria-expanded", "false");
                button.setAttribute("aria-controls", menuId);
                button.innerHTML = '<span class="icon icon24 fa-angle-down" aria-hidden="true"></span>';
                let ariaLabel = menuItem.title;
                if (menuItem.link) {
                    ariaLabel = Language.get("wcf.menu.page.button.toggle", { title: menuItem.title });
                }
                button.setAttribute("aria-label", ariaLabel);
                const list = this.buildMenuItemList(menuItem.children);
                list.id = menuId;
                list.hidden = true;
                button.addEventListener("click", (event) => {
                    event.preventDefault();
                    this.toggleList(button, list);
                });
                button.addEventListener("keydown", (event) => {
                    if (event.key === "Enter" || event.key === " ") {
                        event.preventDefault();
                        button.click();
                    }
                });
                list.addEventListener("keydown", (event) => {
                    if (event.key === "Escape") {
                        event.preventDefault();
                        event.stopPropagation();
                        this.toggleList(button, list);
                    }
                });
                listItem.append(button, list);
            }
            return listItem;
        }
        toggleList(button, list) {
            if (list.hidden) {
                button.setAttribute("aria-expanded", "true");
                list.hidden = false;
            }
            else {
                button.setAttribute("aria-expanded", "false");
                list.hidden = true;
                if (document.activeElement !== button) {
                    button.focus();
                }
            }
        }
        refreshUnreadIndicator() {
            const hasUnreadItems = this.mainMenu.querySelector(".boxMenuLinkOutstandingItems") !== null;
            if (hasUnreadItems) {
                this.mainMenu.classList.add("pageMenuMobileButtonHasContent");
            }
            else {
                this.mainMenu.classList.remove("pageMenuMobileButtonHasContent");
            }
        }
        updateOverflowIndicator(container) {
            const hasOverflow = container.clientHeight < container.scrollHeight;
            if (hasOverflow) {
                if (container.scrollTop > 0) {
                    container.classList.add("pageMenuMainContainerOverflowTop");
                }
                else {
                    container.classList.remove("pageMenuMainContainerOverflowTop");
                }
                if (container.clientHeight + container.scrollTop < container.scrollHeight) {
                    container.classList.add("pageMenuMainContainerOverflowBottom");
                }
                else {
                    container.classList.remove("pageMenuMainContainerOverflowBottom");
                }
            }
            else {
                container.classList.remove("pageMenuMainContainerOverflowTop", "pageMenuMainContainerOverflowBottom");
            }
        }
    }
    exports.PageMenuMain = PageMenuMain;
    exports.default = PageMenuMain;
});
