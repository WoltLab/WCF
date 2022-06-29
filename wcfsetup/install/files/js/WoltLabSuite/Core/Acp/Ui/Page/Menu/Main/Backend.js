/**
 * Provides the menu items for the mobile main menu in the admin panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Page/Menu/Main/Backend
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AcpUiPageMenuMainBackend = void 0;
    function getSubMenuItems(subMenu, menuItem) {
        const categoryList = subMenu.querySelector(`.acpPageSubMenuCategoryList[data-menu-item="${menuItem}"]`);
        return Array.from(categoryList.querySelectorAll(".acpPageSubMenuCategory")).map((category) => {
            const title = category.querySelector("span").textContent;
            const children = getMenuItems(category);
            return {
                active: false,
                children,
                counter: 0,
                depth: 1,
                identifier: false,
                title,
            };
        });
    }
    function getMenuItems(category) {
        return Array.from(category.querySelectorAll(".acpPageSubMenuLink")).map((link) => {
            const children = getMenuItemActions(link);
            let active = link.classList.contains("active");
            if (children.length === 0 && link.parentElement.classList.contains("active")) {
                active = true;
            }
            return {
                active,
                children,
                counter: 0,
                depth: 2,
                identifier: false,
                link: link.href,
                title: link.textContent,
            };
        });
    }
    function getMenuItemActions(link) {
        const listItem = link.parentElement;
        if (!listItem.classList.contains("acpPageSubMenuLinkWrapper")) {
            return [];
        }
        return Array.from(listItem.querySelectorAll(".acpPageSubMenuIcon")).map((action) => {
            return {
                active: action.classList.contains("active"),
                children: [],
                counter: 0,
                depth: 2,
                identifier: false,
                link: action.href,
                title: action.dataset.tooltip || action.title,
            };
        });
    }
    class AcpUiPageMenuMainBackend {
        getMenuItems(_container) {
            const menu = document.getElementById("acpPageMenu");
            const subMenu = document.getElementById("acpPageSubMenu");
            const menuItems = Array.from(menu.querySelectorAll(".acpPageMenuLink")).map((link) => {
                const menuItem = link.dataset.menuItem;
                const title = link.querySelector(".acpPageMenuItemLabel").textContent;
                const children = getSubMenuItems(subMenu, menuItem);
                return {
                    active: false,
                    children,
                    counter: 0,
                    depth: 0,
                    identifier: false,
                    title,
                };
            });
            return menuItems;
        }
    }
    exports.AcpUiPageMenuMainBackend = AcpUiPageMenuMainBackend;
    exports.default = AcpUiPageMenuMainBackend;
});
