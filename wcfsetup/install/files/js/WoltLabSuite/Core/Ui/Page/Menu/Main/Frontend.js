/**
 * Provides the menu items for the mobile main menu in the frontend.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Page/Menu/Main/Frontend
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiPageMenuMainFrontend = void 0;
    function normalizeMenuItem(menuItem, depth) {
        const anchor = menuItem.querySelector(".boxMenuLink");
        const title = anchor.querySelector(".boxMenuLinkTitle").textContent;
        let counter = 0;
        const outstandingItems = anchor.querySelector(".boxMenuLinkOutstandingItems");
        if (outstandingItems) {
            counter = parseInt(outstandingItems.textContent.replace(/[^0-9]/, ""), 10);
        }
        const subMenu = menuItem.querySelector("ol");
        let children = [];
        if (subMenu instanceof HTMLOListElement) {
            let childDepth = depth;
            if (childDepth < 2) {
                childDepth = (depth + 1);
            }
            children = Array.from(subMenu.children).map((subMenuItem) => {
                return normalizeMenuItem(subMenuItem, childDepth);
            });
        }
        // `link.href` represents the computed link, not the raw value.
        const href = anchor.getAttribute("href");
        let link = undefined;
        if (href && href !== "#") {
            link = anchor.href;
        }
        const active = menuItem.classList.contains("active");
        return {
            active,
            children,
            counter,
            depth,
            link,
            title,
        };
    }
    class UiPageMenuMainFrontend {
        getMenuItems(container) {
            return Array.from(container.children).map((element) => {
                return normalizeMenuItem(element, 0);
            });
        }
    }
    exports.UiPageMenuMainFrontend = UiPageMenuMainFrontend;
    exports.default = UiPageMenuMainFrontend;
});
