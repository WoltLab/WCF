/**
 * Manages the list of options of a poll, each option can either have a single
 * value or a value per language.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Language/Input", "WoltLabSuite/Core/StringUtil", "sortablejs"], function (require, exports, tslib_1, Util_1, Language_1, Input_1, StringUtil_1, sortablejs_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.getOptionListValue = getOptionListValue;
    exports.removeOptionList = removeOptionList;
    sortablejs_1 = tslib_1.__importDefault(sortablejs_1);
    class PollOptionList {
        #languages;
        #list;
        #addButton;
        #maxOptions;
        #template;
        constructor(formField, languages, maxOptions) {
            const template = document.getElementById(`${formField.id}_template`);
            if (!(template instanceof HTMLTemplateElement)) {
                throw new Error(`Cannot find the template '${formField.id}_template' for the poll options.`, {
                    cause: {
                        formField,
                    },
                });
            }
            this.#languages = languages;
            this.#maxOptions = maxOptions;
            this.#template = template;
            this.#list = document.createElement("ol");
            this.#list.classList.add("pollOptionList");
            formField.insertAdjacentElement("afterend", this.#list);
            this.#addButton = document.createElement("button");
            this.#addButton.type = "button";
            this.#addButton.classList.add("button", "small", "pollOptionList__addItem");
            this.#addButton.textContent = (0, Language_1.getPhrase)("wcf.poll.button.addOption");
            this.#addButton.addEventListener("click", () => {
                this.#createRow(undefined, true);
            });
            this.#list.insertAdjacentElement("afterend", this.#addButton);
            const options = formField.value ? JSON.parse(formField.value) : [];
            options.forEach((option) => this.#createRow(option));
            if (options.length === 0) {
                this.#createRow();
                this.#createRow();
            }
            new sortablejs_1.default(this.#list, {
                direction: "vertical",
                animation: 150,
                fallbackOnBody: true,
                draggable: "li",
                handle: ".pollOptionListItem__handle",
            });
        }
        #createRow(option, autoFocus = false, previousRow) {
            if (this.#list.childElementCount >= this.#maxOptions) {
                return;
            }
            const li = document.createElement("li");
            li.classList.add("pollOptionListItem");
            li.dataset.optionId = option ? option.optionID.toString() : "0";
            li.append(this.#template.content.cloneNode(true));
            if (previousRow) {
                previousRow.after(li);
            }
            else {
                this.#list.append(li);
            }
            const valueInput = li.querySelector(".pollOptionListItem__value");
            (0, Util_1.identify)(valueInput);
            li.querySelector(".pollOptionListItem__remove").addEventListener("click", () => {
                const siblingRow = li.nextElementSibling ?? li.previousElementSibling;
                if (this.#hasLanguageSelection()) {
                    (0, Input_1.unregister)(valueInput.id);
                }
                li.remove();
                if (siblingRow === null) {
                    this.#createRow(undefined, true);
                }
                else {
                    siblingRow.querySelector(".pollOptionListItem__value").focus();
                }
                this.#updateAddButton();
            });
            valueInput.addEventListener("keydown", (event) => {
                if (event.key === "Enter") {
                    event.preventDefault();
                    this.#createRow(undefined, true, li);
                }
            });
            const hasI18nValues = option !== undefined && !Object.hasOwn(option.optionValue, 0);
            if (this.#hasLanguageSelection()) {
                // `initI18n()` expects HTML-escaped values, but the values are raw JSON data.
                const i18nValues = hasI18nValues
                    ? Object.fromEntries(Object.entries(option.optionValue).map(([languageId, value]) => [languageId, (0, StringUtil_1.escapeHTML)(value)]))
                    : {};
                (0, Input_1.init)(valueInput.id, i18nValues, this.#languages, false);
            }
            if (!hasI18nValues) {
                valueInput.value = option?.optionValue[0] ?? "";
            }
            if (autoFocus) {
                valueInput.focus();
            }
            this.#updateAddButton();
        }
        #hasLanguageSelection() {
            return Object.keys(this.#languages).length > 1;
        }
        #updateAddButton() {
            this.#addButton.disabled = this.#list.childElementCount >= this.#maxOptions;
        }
        destroy() {
            if (!this.#hasLanguageSelection()) {
                return;
            }
            this.#list.querySelectorAll(".pollOptionListItem__value").forEach((valueInput) => {
                (0, Input_1.unregister)(valueInput.id);
            });
        }
        getData() {
            const data = [];
            this.#list.querySelectorAll(".pollOptionListItem").forEach((li) => {
                const valueInput = li.querySelector(".pollOptionListItem__value");
                data.push({
                    optionID: parseInt(li.dataset.optionId, 10),
                    optionValue: this.#hasLanguageSelection()
                        ? Object.fromEntries((0, Input_1.getValues)(valueInput.id))
                        : { 0: valueInput.value },
                });
            });
            return data;
        }
    }
    const optionLists = new Map();
    function setup(formField, languages, maxOptions) {
        optionLists.set(formField.id, new PollOptionList(formField, languages, maxOptions));
    }
    /**
     * Returns the serialized options of the poll option list with the given id.
     */
    function getOptionListValue(fieldId) {
        const optionList = optionLists.get(fieldId);
        if (optionList === undefined) {
            throw new Error(`Unknown poll option list '${fieldId}'.`);
        }
        return JSON.stringify(optionList.getData());
    }
    /**
     * Removes the poll option list with the given id, e.g. after the dialog has
     * been closed.
     */
    function removeOptionList(fieldId) {
        optionLists.get(fieldId)?.destroy();
        optionLists.delete(fieldId);
    }
});
