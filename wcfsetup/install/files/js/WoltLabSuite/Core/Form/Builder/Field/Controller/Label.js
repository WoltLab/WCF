/**
 * Handles the JavaScript part of the label form field.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Controller/Label
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../../Core", "../../../../Dom/Util", "../../../../Language", "../../../../Ui/Dropdown/Simple"], function (require, exports, tslib_1, Core, DomUtil, Language, Simple_1) {
    "use strict";
    Core = (0, tslib_1.__importStar)(Core);
    DomUtil = (0, tslib_1.__importStar)(DomUtil);
    Language = (0, tslib_1.__importStar)(Language);
    Simple_1 = (0, tslib_1.__importDefault)(Simple_1);
    class Label {
        constructor(fieldId, labelId, options) {
            this._formFieldContainer = document.getElementById(fieldId + "Container");
            this._labelChooser = this._formFieldContainer.getElementsByClassName("labelChooser")[0];
            this._options = Core.extend({
                forceSelection: false,
                showWithoutSelection: false,
            }, options);
            this._input = document.createElement("input");
            this._input.type = "hidden";
            this._input.id = fieldId;
            this._input.name = fieldId;
            this._input.value = labelId;
            this._formFieldContainer.appendChild(this._input);
            const labelChooserId = DomUtil.identify(this._labelChooser);
            // init dropdown
            let dropdownMenu = Simple_1.default.getDropdownMenu(labelChooserId);
            if (dropdownMenu === null) {
                Simple_1.default.init(this._labelChooser.getElementsByClassName("dropdownToggle")[0]);
                dropdownMenu = Simple_1.default.getDropdownMenu(labelChooserId);
            }
            let additionalOptionList = null;
            if (this._options.showWithoutSelection || !this._options.forceSelection) {
                additionalOptionList = document.createElement("ul");
                dropdownMenu.appendChild(additionalOptionList);
                const dropdownDivider = document.createElement("li");
                dropdownDivider.classList.add("dropdownDivider");
                additionalOptionList.appendChild(dropdownDivider);
            }
            if (this._options.showWithoutSelection) {
                const listItem = document.createElement("li");
                listItem.dataset.labelId = "-1";
                this._blockScroll(listItem);
                additionalOptionList.appendChild(listItem);
                const span = document.createElement("span");
                listItem.appendChild(span);
                const label = document.createElement("span");
                label.classList.add("badge", "label");
                label.innerHTML = Language.get("wcf.label.withoutSelection");
                span.appendChild(label);
            }
            if (!this._options.forceSelection) {
                const listItem = document.createElement("li");
                listItem.dataset.labelId = "0";
                this._blockScroll(listItem);
                additionalOptionList.appendChild(listItem);
                const span = document.createElement("span");
                listItem.appendChild(span);
                const label = document.createElement("span");
                label.classList.add("badge", "label");
                label.innerHTML = Language.get("wcf.label.none");
                span.appendChild(label);
            }
            dropdownMenu.querySelectorAll("li:not(.dropdownDivider)").forEach((listItem) => {
                listItem.addEventListener("click", (ev) => this._click(ev));
                if (labelId) {
                    if (listItem.dataset.labelId === labelId) {
                        this._selectLabel(listItem);
                    }
                }
            });
        }
        _blockScroll(element) {
            element.addEventListener("wheel", (ev) => ev.preventDefault(), {
                passive: false,
            });
        }
        _click(event) {
            event.preventDefault();
            this._selectLabel(event.currentTarget);
        }
        _selectLabel(label) {
            // save label
            let labelId = label.dataset.labelId;
            if (!labelId) {
                labelId = "0";
            }
            // replace button with currently selected label
            const displayLabel = label.querySelector("span > span");
            const button = this._labelChooser.querySelector(".dropdownToggle > span");
            button.className = displayLabel.className;
            button.textContent = displayLabel.textContent;
            this._input.value = labelId;
        }
    }
    Core.enableLegacyInheritance(Label);
    return Label;
});
