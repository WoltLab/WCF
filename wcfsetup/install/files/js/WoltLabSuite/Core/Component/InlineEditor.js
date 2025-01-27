/**
 * Provides an inline editor for objects using a drop-down menu.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ui/Dropdown/Builder", "WoltLabSuite/Core/Core"], function (require, exports, Builder_1, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InlineEditor = void 0;
    exports.getInlineEditor = getInlineEditor;
    const inlineEditors = new Map();
    class InlineEditor {
        element;
        dropdownToggle;
        permissions = {};
        dropdownMenu;
        menuItems = [];
        constructor(element, dropdownToggleSelector) {
            this.element = element;
            this.dropdownToggle = this.element.querySelector(dropdownToggleSelector);
            this.dropdownMenu = (0, Builder_1.create)([]);
            (0, Builder_1.attach)(this.dropdownMenu, this.dropdownToggle);
            inlineEditors.set(this.element, this);
        }
        /**
         * Gets the state of a property from the element's dataset as a boolean.
         */
        getState(propertyName) {
            if (!Object.prototype.hasOwnProperty.call(this.element.dataset, propertyName)) {
                return false;
            }
            return (0, Core_1.stringToBool)(this.element.dataset[propertyName]);
        }
        /**
         * Updates the state of the element's dataset with the provided data.
         */
        updateState(data) {
            Object.entries(data).forEach(([key, value]) => {
                this.element.dataset[key] = value ? "1" : "0";
            });
            this.rebuildDropdownMenu();
        }
        /**
         * Sets the permissions for the inline editor.
         */
        setPermissions(permissions) {
            this.permissions = permissions;
            this.rebuildDropdownMenu();
        }
        /**
         * Gets the permissions for the inline editor.
         */
        getPermissions() {
            return this.permissions;
        }
        /**
         * Adds a menu item to the dropdown menu and rebuilds the menu.
         */
        addMenuItem(menuItem) {
            this.menuItems.push(menuItem);
            this.rebuildDropdownMenu();
        }
        /**
         * Adds multiple menu items to the dropdown menu and rebuilds the menu.
         */
        addMenuItems(menuItems) {
            this.menuItems.push(...menuItems);
            this.rebuildDropdownMenu();
        }
        /**
         * Rebuilds the dropdown menu based on the current menu items and their visibility.
         */
        rebuildDropdownMenu() {
            const dropdownMenuItems = this.menuItems
                .filter((item) => {
                return item.visible === undefined || item.visible();
            })
                .map((item) => item.item);
            if (dropdownMenuItems.length === 0) {
                this.dropdownMenu.innerHTML = "";
            }
            else {
                (0, Builder_1.setItems)(this.dropdownMenu, dropdownMenuItems);
            }
        }
    }
    exports.InlineEditor = InlineEditor;
    /**
     * Gets the inline editor instance associated with the given element.
     */
    function getInlineEditor(element) {
        return inlineEditors.get(element);
    }
});
