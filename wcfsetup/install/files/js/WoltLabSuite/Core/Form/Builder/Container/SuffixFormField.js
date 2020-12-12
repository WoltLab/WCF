/**
 * Handles the dropdowns of form fields with a suffix.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Container/SuffixFormField
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../Ui/Dropdown/Simple", "../../../Event/Handler", "../../../Core"], function (require, exports, tslib_1, Simple_1, EventHandler, Core) {
    "use strict";
    Simple_1 = tslib_1.__importDefault(Simple_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Core = tslib_1.__importStar(Core);
    class SuffixFormField {
        constructor(formId, suffixFieldId) {
            this._formId = formId;
            this._suffixField = document.getElementById(suffixFieldId);
            this._suffixDropdownMenu = Simple_1.default.getDropdownMenu(suffixFieldId + "_dropdown");
            this._suffixDropdownToggle = Simple_1.default.getDropdown(suffixFieldId + "_dropdown").getElementsByClassName("dropdownToggle")[0];
            Array.from(this._suffixDropdownMenu.children).forEach((listItem) => {
                listItem.addEventListener("click", (ev) => this._changeSuffixSelection(ev));
            });
            EventHandler.add("WoltLabSuite/Core/Form/Builder/Manager", "afterUnregisterForm", (data) => this._destroyDropdown(data));
        }
        /**
         * Handles changing the suffix selection.
         */
        _changeSuffixSelection(event) {
            const target = event.currentTarget;
            if (target.classList.contains("disabled")) {
                return;
            }
            Array.from(this._suffixDropdownMenu.children).forEach((listItem) => {
                if (listItem === target) {
                    listItem.classList.add("active");
                }
                else {
                    listItem.classList.remove("active");
                }
            });
            this._suffixField.value = target.dataset.value;
            this._suffixDropdownToggle.innerHTML =
                target.dataset.label + ' <span class="icon icon16 fa-caret-down pointer"></span>';
        }
        /**
         * Destroys the suffix dropdown if the parent form is unregistered.
         */
        _destroyDropdown(data) {
            if (data.formId === this._formId) {
                Simple_1.default.destroy(this._suffixDropdownMenu.id);
            }
        }
    }
    Core.enableLegacyInheritance(SuffixFormField);
    return SuffixFormField;
});
