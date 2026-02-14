define(["require", "exports", "tslib", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Language/Input", "sortablejs"], function (require, exports, tslib_1, Util_1, Language_1, Input_1, sortablejs_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    class SelectOptions {
        #languages;
        #list;
        #template;
        constructor(formField, languages) {
            if (formField.form === null) {
                throw new Error("Cannot create select options for an form field that is not part of a form.", {
                    cause: {
                        formField,
                    },
                });
            }
            this.#languages = languages;
            this.#template = document.getElementById(`${formField.id}_template`);
            this.#list = this.#createUi(formField);
            this.#createListItems(formField);
            this.#initializeSorting();
            formField.form.addEventListener("submit", () => {
                this.#setHiddenValue(formField);
            });
        }
        #createUi(formField) {
            const ul = document.createElement("ul");
            ul.classList.add("selectOptionsList");
            formField.insertAdjacentElement("afterend", ul);
            const addButton = this.#createAddButton();
            addButton.addEventListener("click", () => {
                this.#createRow(undefined, true);
            });
            ul.insertAdjacentElement("afterend", addButton);
            return ul;
        }
        #createListItems(formField) {
            if (formField.value) {
                const data = JSON.parse(formField.value);
                data.forEach((option) => {
                    this.#createRow(option);
                });
            }
            else {
                this.#createRow();
            }
        }
        #createAddButton() {
            const button = document.createElement("button");
            button.type = "button";
            button.classList.add("button", "small", "selectOptionsListItem__addItem");
            button.textContent = (0, Language_1.getPhrase)("wcf.form.selectOptions.addItem");
            return button;
        }
        #createRow(option, autoFocus = false) {
            const li = document.createElement("li");
            li.classList.add("selectOptionsListItem");
            li.append(this.#template.content.cloneNode(true));
            this.#list.append(li);
            const removeButton = li.querySelector(".selectOptionsListItem__remove");
            removeButton.addEventListener("click", () => {
                li.remove();
                if (!this.#list.childElementCount) {
                    this.#createRow();
                }
            });
            const keyInput = li.querySelector(".selectOptionsListItem__key");
            keyInput.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    this.#createRow(undefined, true);
                }
            });
            keyInput.value = option ? option.key : "";
            const valueInput = li.querySelector(".selectOptionsListItem__value");
            valueInput.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    this.#createRow(undefined, true);
                }
            });
            const hasI18nValues = option && !Object.hasOwn(option.value, 0);
            (0, Input_1.init)((0, Util_1.identify)(valueInput), hasI18nValues ? option.value : {}, this.#languages, false);
            if (!hasI18nValues) {
                valueInput.value = option?.value[0] ?? "";
            }
            if (autoFocus) {
                keyInput.focus();
            }
        }
        #initializeSorting() {
            new sortablejs_1.default(this.#list, {
                direction: "vertical",
                animation: 150,
                fallbackOnBody: true,
                draggable: "li",
                handle: ".selectOptionsListItem__handle",
            });
        }
        #setHiddenValue(formField) {
            const data = [];
            this.#list.querySelectorAll(".selectOptionsListItem").forEach((li) => {
                const key = li.querySelector(".selectOptionsListItem__key").value;
                const valueInput = li.querySelector(".selectOptionsListItem__value");
                data.push({
                    key,
                    value: Object.fromEntries((0, Input_1.getValues)(valueInput.id)),
                });
            });
            formField.value = JSON.stringify(data);
        }
    }
    function setup(formField, languages) {
        new SelectOptions(formField, languages);
    }
});
