/**
 * Provides the UI elements of a user menu.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/View
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../../Date/Util", "../../../StringUtil", "../../../Dom/Change/Listener", "../../../Language", "focus-trap", "perfect-scrollbar", "../../Screen"], function (require, exports, tslib_1, Util_1, StringUtil_1, DomChangeListener, Language, focus_trap_1, perfectScrollbar, UiScreen) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UserMenuView = void 0;
    DomChangeListener = tslib_1.__importStar(DomChangeListener);
    Language = tslib_1.__importStar(Language);
    perfectScrollbar = tslib_1.__importStar(perfectScrollbar);
    UiScreen = tslib_1.__importStar(UiScreen);
    class UserMenuView {
        constructor(provider) {
            this.usePerfectScrollbar = false;
            this.provider = provider;
            this.element = document.createElement("div");
            this.buildElement();
            this.markAllAsReadButton = this.buildButton({
                icon: '<span class="icon icon24 fa-check"></span>',
                link: "#",
                name: "markAllAsRead",
                title: Language.get("wcf.global.button.markAllAsRead"),
            });
            this.focusTrap = (0, focus_trap_1.createFocusTrap)(this.element, {
                allowOutsideClick: true,
                escapeDeactivates: () => {
                    // Intercept the "Escape" key and close the dialog through other means.
                    this.element.dispatchEvent(new Event("shouldClose"));
                    return false;
                },
                fallbackFocus: this.element,
            });
            UiScreen.on("screen-lg", {
                match: () => {
                    this.usePerfectScrollbar = true;
                    this.rebuildScrollbar();
                },
                unmatch: () => {
                    this.usePerfectScrollbar = false;
                    this.rebuildScrollbar();
                },
                setup: () => {
                    this.usePerfectScrollbar = true;
                    this.rebuildScrollbar();
                },
            });
        }
        getElement() {
            return this.element;
        }
        async open() {
            const isStale = this.provider.isStale();
            if (isStale) {
                this.reset();
            }
            this.element.hidden = false;
            this.focusTrap.activate();
            if (isStale) {
                const data = await this.provider.getData();
                this.setContent(data);
            }
        }
        close() {
            this.focusTrap.deactivate();
            this.element.hidden = true;
        }
        getItems() {
            return Array.from(this.getContent().querySelectorAll(".userMenuItem"));
        }
        setContent(data) {
            const content = this.getContent();
            this.markAllAsReadButton.remove();
            if (data.length === 0) {
                content.innerHTML = `<span class="userMenuContentStatus">${this.provider.getEmptyViewMessage()}</span>`;
            }
            else {
                let hasUnreadContent = false;
                const fragment = document.createDocumentFragment();
                data.forEach((itemData) => {
                    if (itemData.isUnread) {
                        hasUnreadContent = true;
                    }
                    fragment.append(this.createItem(itemData));
                });
                content.innerHTML = "";
                content.append(fragment);
                if (hasUnreadContent) {
                    this.element.querySelector(".userMenuButtons").prepend(this.markAllAsReadButton);
                }
                DomChangeListener.trigger();
            }
            this.rebuildScrollbar();
        }
        rebuildScrollbar() {
            const content = this.getContent();
            if (this.usePerfectScrollbar) {
                this.enablePerfectScrollbar(content);
            }
            else {
                this.disablePerfectScrollbar(content);
            }
        }
        enablePerfectScrollbar(content) {
            if (content.dataset.psId) {
                perfectScrollbar.update(content);
            }
            else {
                perfectScrollbar.initialize(content, {
                    suppressScrollX: true,
                });
            }
        }
        disablePerfectScrollbar(content) {
            perfectScrollbar.destroy(content);
        }
        createItem(itemData) {
            const element = document.createElement("div");
            element.classList.add("userMenuItem");
            element.dataset.objectId = itemData.objectId.toString();
            element.dataset.isUnread = itemData.isUnread ? "true" : "false";
            const link = (0, StringUtil_1.escapeHTML)(itemData.link);
            element.innerHTML = `
      <div class="userMenuItemImage">${itemData.image}</div>
      <div class="userMenuItemContent">
        <a href="${link}" class="userMenuItemLink">${itemData.content}</a>
      </div>
      <div class="userMenuItemMeta"></div>
      <div class="userMenuItemUnread">
        <a href="#" class="userMenuItemMarkAsRead" role="button">
          <span class="icon icon24 fa-check jsTooltip" title="${Language.get("wcf.global.button.markAsRead")}"></span>
        </a>
      </div>
    `;
            const time = (0, Util_1.getTimeElement)(new Date(itemData.time * 1000));
            element.querySelector(".userMenuItemMeta").append(time);
            const markAsRead = element.querySelector(".userMenuItemMarkAsRead");
            markAsRead.addEventListener("click", async (event) => {
                event.preventDefault();
                await this.provider.markAsRead(itemData.objectId);
                this.markAsRead(element);
            });
            if (itemData.usernames.length > 0) {
                const content = element.querySelector(".userMenuItemContent");
                const usernames = document.createElement("div");
                usernames.classList.add("userMenuItemUsernames");
                usernames.textContent = itemData.usernames.join(", ");
                content.insertAdjacentElement("afterend", usernames);
                element.classList.add("userMenuItemWithUsernames");
            }
            if (this.provider.hasPlainTitle()) {
                const link = element.querySelector(".userMenuItemLink");
                link.classList.add("userMenuItemLinkPlain");
            }
            return element;
        }
        markAsRead(element) {
            element.dataset.isUnread = "false";
            const unreadItems = this.getContent().querySelectorAll('.userMenuItem[data-is-unread="true"]');
            if (unreadItems.length === 0) {
                this.markAllAsReadButton.remove();
            }
        }
        reset() {
            const content = this.getContent();
            content.innerHTML = `<span class="userMenuContentStatus"><span class="icon icon24 fa-spinner"></span></span>`;
        }
        buildElement() {
            this.element.hidden = true;
            this.element.classList.add("userMenu");
            this.element.dataset.origin = this.provider.getPanelButton().id;
            this.element.tabIndex = -1;
            this.element.innerHTML = `
      <div class="userMenuHeader">
        <div class="userMenuTitle">${this.provider.getTitle()}</div>
        <div class="userMenuButtons"></div>
      </div>
      <div class="userMenuContent userMenuContentScrollable"></div>
    `;
            // Prevent clicks inside the dialog to close it.
            this.element.addEventListener("click", (event) => event.stopPropagation());
            const buttons = this.element.querySelector(".userMenuButtons");
            this.provider.getMenuButtons().forEach((button) => {
                buttons.append(this.buildButton(button));
            });
            const footer = this.provider.getFooter();
            if (footer !== null) {
                this.element.append(this.buildFooter(footer));
            }
            if (this.provider.getIdentifier() === "com.woltlab.wcf.notifications") {
                const provider = this.provider;
                const notificationElement = provider.getDesktopNotifications();
                if (notificationElement) {
                    const header = this.element.querySelector(".userMenuHeader");
                    header.insertAdjacentElement("afterend", notificationElement);
                }
            }
        }
        buildButton(button) {
            const link = document.createElement("a");
            link.setAttribute("role", "button");
            link.classList.add("userMenuButton", "jsTooltip");
            link.title = button.title;
            link.innerHTML = button.icon;
            if (button.name === "markAllAsRead") {
                link.href = "#";
                link.addEventListener("click", (event) => {
                    event.preventDefault();
                    this.markAllAsRead();
                });
            }
            else {
                link.href = button.link;
            }
            return link;
        }
        markAllAsRead() {
            void this.provider.markAllAsRead();
            this.getContent()
                .querySelectorAll(".userMenuItem")
                .forEach((element) => {
                element.dataset.isUnread = "false";
            });
            this.markAllAsReadButton.remove();
        }
        buildFooter(footer) {
            const link = (0, StringUtil_1.escapeHTML)(footer.link);
            const title = (0, StringUtil_1.escapeHTML)(footer.title);
            const element = document.createElement("div");
            element.classList.add("userMenuFooter");
            element.innerHTML = `<a href="${link}" class="userMenuFooterLink">${title}</a>`;
            return element;
        }
        getContent() {
            return this.element.querySelector(".userMenuContent");
        }
    }
    exports.UserMenuView = UserMenuView;
    exports.default = UserMenuView;
});
