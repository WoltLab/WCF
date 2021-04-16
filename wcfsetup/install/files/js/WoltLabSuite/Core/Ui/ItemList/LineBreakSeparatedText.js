/**
 * UI element that shows individual lines of text as distinct list items but saves them as line
 * break-separated text.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ItemList/LineBreakSeparatedText
 * @since 5.4
 */
define(["require", "exports", "tslib", "../Confirmation", "../../Language", "../../Dom/Util"], function (require, exports, tslib_1, UiConfirmation, Language, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiItemListLineBreakSeparatedText = void 0;
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Language = tslib_1.__importStar(Language);
    Util_1 = tslib_1.__importDefault(Util_1);
    class UiItemListLineBreakSeparatedText {
        constructor(itemList, options) {
            this.clearButton = undefined;
            this.itemInput = undefined;
            this.items = new Set();
            this.itemList = itemList;
            this.options = options;
            this.itemList.closest("form").addEventListener("submit", () => this.submit());
            this.initValues();
            this.buildUi();
        }
        /**
         * Adds an item to the list after clicking on the "add" button.
         */
        addItem(event) {
            event.preventDefault();
            const item = this.itemInput.value.trim();
            if (item === "") {
                Util_1.default.innerError(this.itemInput.parentElement, Language.get("wcf.global.form.error.empty"));
            }
            else if (!this.items.has(item)) {
                this.insertItem(item);
                this.resetInput();
            }
            else {
                Util_1.default.innerError(this.itemInput.parentElement, Language.get("wcf.acp.option.type.lineBreakSeparatedText.error.duplicate", {
                    item,
                }), true);
            }
            this.itemInput.focus();
        }
        /**
         * Builds the user interface during setup.
         */
        buildUi() {
            const container = document.createElement("div");
            container.classList.add("itemListFilter");
            this.itemList.insertAdjacentElement("beforebegin", container);
            container.appendChild(this.itemList);
            const inputAddon = document.createElement("div");
            inputAddon.classList.add("inputAddon");
            container.appendChild(inputAddon);
            this.itemInput = document.createElement("input");
            this.itemInput.classList.add("long");
            this.itemInput.type = "text";
            this.itemInput.placeholder = Language.get("wcf.acp.option.type.lineBreakSeparatedText.placeholder");
            this.itemInput.addEventListener("keydown", (ev) => this.keydown(ev));
            this.itemInput.addEventListener("paste", (ev) => this.paste(ev));
            inputAddon.appendChild(this.itemInput);
            const addButton = document.createElement("a");
            addButton.href = "#";
            addButton.classList.add("button", "inputSuffix", "jsTooltip");
            addButton.title = Language.get("wcf.global.button.add");
            addButton.innerHTML = '<span class="icon icon16 fa-plus"></span>';
            addButton.addEventListener("click", (ev) => this.addItem(ev));
            inputAddon.appendChild(addButton);
            this.clearButton = document.createElement("a");
            this.clearButton.href = "#";
            this.clearButton.classList.add("button", "inputSuffix", "jsTooltip");
            this.clearButton.title = Language.get("wcf.global.button.delete");
            this.clearButton.innerHTML = '<span class="icon icon16 fa-times"></span>';
            this.clearButton.addEventListener("click", (ev) => this.clearList(ev));
            inputAddon.appendChild(this.clearButton);
            if (this.items.size === 0) {
                Util_1.default.hide(this.clearButton);
            }
        }
        /**
         * Clears the item list after clicking on the clear button.
         */
        clearList(ev) {
            ev.preventDefault();
            UiConfirmation.show({
                confirm: () => {
                    this.itemList.innerHTML = "";
                    this.items.clear();
                    this.hideList();
                },
                message: Language.get("wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage"),
                messageIsHtml: true,
            });
        }
        /**
         * Deletes an item from the list after clicking on its delete icon.
         */
        deleteItem(event) {
            const button = event.currentTarget;
            const item = button.closest("li").dataset.value;
            UiConfirmation.show({
                confirm: () => {
                    button.closest("li").remove();
                    if (this.itemList.childElementCount === 0) {
                        this.hideList();
                    }
                    this.items.delete(item);
                },
                message: Language.get("wcf.button.delete.confirmMessage", {
                    objectTitle: item,
                }),
                messageIsHtml: true,
            });
        }
        /**
         * Hides the item list and clear button.
         */
        hideList() {
            Util_1.default.hide(this.itemList);
            Util_1.default.hide(this.clearButton);
        }
        /**
         * Adds the initial values to the list.
         */
        initValues() {
            Array.from(this.itemList.children).forEach((el) => {
                this.items.add(el.dataset.value);
                el.querySelector(".jsDeleteItem").addEventListener("click", (ev) => this.deleteItem(ev));
            });
        }
        /**
         * Inserts the given item to the list.
         */
        insertItem(item) {
            this.items.add(item);
            const itemElement = document.createElement("li");
            itemElement.dataset.value = item;
            const deleteButton = document.createElement("span");
            deleteButton.classList.add("icon", "icon16", "fa-times", "jsDeleteItem", "jsTooltip", "pointer");
            deleteButton.title = Language.get("wcf.global.button.delete");
            deleteButton.addEventListener("click", (ev) => this.deleteItem(ev));
            itemElement.append(deleteButton);
            itemElement.append(document.createTextNode(" "));
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
         * Adds an item to the list when pressing "Enter" in the input field.
         */
        keydown(event) {
            if (event.key === "Enter") {
                this.addItem(event);
            }
        }
        /**
         * Adds multiple items at one to the list when pasting multiple lines of text into the input
         * field.
         */
        paste(event) {
            const items = event.clipboardData.getData("text/plain").split("\n");
            if (items.length > 1) {
                event.preventDefault();
                items.forEach((item) => this.insertItem(item));
                this.resetInput();
            }
        }
        /**
         * Resets the input field.
         */
        resetInput() {
            Util_1.default.innerError(this.itemInput.parentElement, "");
            this.itemInput.value = "";
        }
        /**
         * Shows the item list and clear button.
         */
        showList() {
            Util_1.default.show(this.itemList);
            Util_1.default.show(this.clearButton);
        }
        /**
         * Adds a hidden input field with the data to the form before it is submitted.
         */
        submit() {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = this.options.submitFieldName;
            input.value = Array.from(this.items).join("\n");
            this.itemList.parentElement.append(input);
        }
    }
    exports.UiItemListLineBreakSeparatedText = UiItemListLineBreakSeparatedText;
    exports.default = UiItemListLineBreakSeparatedText;
});
