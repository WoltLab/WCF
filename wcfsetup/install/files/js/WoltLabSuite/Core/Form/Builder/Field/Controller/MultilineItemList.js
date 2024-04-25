/**
 * Handles the JavaScript part of a multiline item list form field.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ui/ItemList/LineBreakSeparatedText", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, UiItemList, Language_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getValues = exports.MultilineItemListFormField = void 0;
    UiItemList = tslib_1.__importStar(UiItemList);
    Util_1 = tslib_1.__importDefault(Util_1);
    const _data = new Map();
    class MultilineItemListFormField extends UiItemList.UiItemListLineBreakSeparatedText {
        /**
         @inheritDoc
         */
        constructor(itemList, options) {
            super(itemList, options);
            if (options.submitFieldName != null) {
                _data.set(options.submitFieldName, this);
            }
            else {
                _data.set(this.submitField.id, this);
            }
        }
        getItems() {
            return this.items;
        }
        /**
         * @inheritDoc
         */
        initValues() {
            super.initValues();
            Array.from(this.itemList.children).forEach((el) => {
                el.querySelector(".jsEditItem").addEventListener("click", (ev) => {
                    this.editItem(ev);
                });
            });
        }
        /**
         * Begin the editing of an item.
         */
        editItem(event) {
            if (this.uiDisabled) {
                return;
            }
            const button = event.currentTarget;
            const li = button.closest("li");
            const deleteButton = li.querySelector(".jsDeleteItem");
            const valueSpan = li.querySelector("span");
            const value = button.closest("li").dataset.value;
            //hide old buttons
            Util_1.default.hide(deleteButton);
            Util_1.default.hide(button);
            Util_1.default.hide(valueSpan);
            //insert temporary input field and buttons
            const input = document.createElement("input");
            input.type = "text";
            input.value = value;
            const saveButton = document.createElement("button");
            saveButton.classList.add("jsSaveItem", "jsTooltip");
            saveButton.title = (0, Language_1.getPhrase)("wcf.global.button.save");
            let icon = document.createElement("fa-icon");
            icon.setIcon("save");
            saveButton.append(icon);
            const cancelButton = document.createElement("button");
            cancelButton.classList.add("jsCancelItem", "jsTooltip");
            cancelButton.title = (0, Language_1.getPhrase)("wcf.global.button.cancel");
            icon = document.createElement("fa-icon");
            icon.setIcon("times");
            cancelButton.append(icon);
            const endCallback = () => {
                //remove temporary elements
                input.remove();
                saveButton.remove();
                cancelButton.remove();
                //display old elements
                Util_1.default.show(deleteButton);
                Util_1.default.show(button);
                Util_1.default.show(valueSpan);
            };
            const saveCallback = (saveEvent) => {
                saveEvent.preventDefault();
                if (this.uiDisabled) {
                    return;
                }
                const newValue = input.value.trim();
                if (newValue === "") {
                    Util_1.default.innerError(input.parentElement, (0, Language_1.getPhrase)("wcf.global.form.error.empty"));
                }
                else if (!this.items.has(newValue) || newValue === value) {
                    //remove error message
                    Util_1.default.innerError(input.parentElement, "");
                    //insert new value
                    button.closest("li").dataset.value = newValue;
                    valueSpan.textContent = newValue;
                    this.items.delete(value);
                    this.items.add(newValue);
                    endCallback();
                }
                else {
                    Util_1.default.innerError(input.parentElement, (0, Language_1.getPhrase)("wcf.acp.option.type.lineBreakSeparatedText.error.duplicate", {
                        item: newValue,
                    }), true);
                }
                input.focus();
            };
            input.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    saveCallback(event);
                }
            });
            saveButton.addEventListener("click", (ev) => {
                saveCallback(ev);
            });
            cancelButton.addEventListener("click", () => {
                endCallback();
            });
            li.append(cancelButton);
            li.append(saveButton);
            li.append(input);
            input.focus();
        }
        /**
         * @inheritDoc
         */
        insertItem(item) {
            this.items.add(item);
            const itemElement = document.createElement("li");
            itemElement.dataset.value = item;
            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.classList.add("jsDeleteItem", "jsTooltip");
            deleteButton.title = (0, Language_1.getPhrase)("wcf.global.button.delete");
            deleteButton.addEventListener("click", (ev) => {
                this.deleteItem(ev);
            });
            let icon = document.createElement("fa-icon");
            icon.setIcon("trash");
            deleteButton.append(icon);
            itemElement.append(deleteButton);
            const editButton = document.createElement("button");
            editButton.type = "button";
            editButton.classList.add("jsEditItem", "jsTooltip");
            editButton.title = (0, Language_1.getPhrase)("wcf.global.button.edit");
            editButton.addEventListener("click", (ev) => {
                this.editItem(ev);
            });
            icon = document.createElement("fa-icon");
            icon.setIcon("edit");
            editButton.append(icon);
            itemElement.append(editButton);
            itemElement.append(" ");
            const label = document.createElement("span");
            label.innerText = item;
            itemElement.append(label);
            const nextElement = Array.from(this.itemList.children).find((el) => el.dataset.value > item);
            if (nextElement) {
                this.itemList.insertBefore(itemElement, nextElement);
            }
            else {
                this.itemList.append(itemElement);
            }
            this.showList();
        }
        /**
         * @inheritDoc
         */
        submit() {
            if (this.submitField) {
                this.submitField.value = Array.from(this.items).join("\n");
            }
            else {
                Array.from(this.items).forEach((value) => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = this.options.submitFieldName;
                    input.value = value;
                    this.itemList.parentElement.append(input);
                });
            }
        }
    }
    exports.MultilineItemListFormField = MultilineItemListFormField;
    function getValues(elementId) {
        if (!_data.has(elementId)) {
            throw new Error(`Element id '${elementId}' is unknown.`);
        }
        return _data.get(elementId).getItems();
    }
    exports.getValues = getValues;
});
