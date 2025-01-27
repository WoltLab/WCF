/**
 * Provides an inline editor for objects with a dropdown menu.
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
    class InlineEditor {
        element;
        dropdownToggle;
        dropdownMenu = null;
        constructor(element, dropdownToggleSelector) {
            this.element = element;
            this.dropdownToggle = this.element.querySelector(dropdownToggleSelector);
            this.dropdownMenu = (0, Builder_1.create)(this.getDropdownOptions());
            this.dropdownToggle.parentElement.appendChild(this.dropdownMenu);
        }
        getPermission(permission) {
            if (!Object.prototype.hasOwnProperty.call(this.element.dataset, permission)) {
                return false;
            }
            return (0, Core_1.stringToBool)(this.element.dataset[permission]);
        }
    }
    exports.InlineEditor = InlineEditor;
});
