/**
 * Flexible UI element featuring both a list of items and an input field with suggestion support.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Core", "../Dom/Traverse", "../Language", "./Suggestion", "./Dropdown/Simple", "../Dom/Util"], function (require, exports, tslib_1, Core, DomTraverse, Language, Suggestion_1, Simple_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    exports.getValues = getValues;
    exports.setValues = setValues;
    Core = tslib_1.__importStar(Core);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    Suggestion_1 = tslib_1.__importDefault(Suggestion_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    const _data = new Map();
    /**
     * Creates the DOM structure for target element. If `element` is a `<textarea>`
     * it will be automatically replaced with an `<input>` element.
     */
    function createUI(element, options) {
        const parentElement = element.parentElement;
        const list = document.createElement("ol");
        list.className = "inputItemList" + (element.disabled ? " disabled" : "");
        list.dataset.acceptsNewItems = "true";
        list.dataset.elementId = element.id;
        list.addEventListener("click", (event) => {
            if (event.target === list) {
                element.focus();
            }
        });
        const listItem = document.createElement("li");
        listItem.className = "input";
        list.appendChild(listItem);
        element.addEventListener("input", input);
        element.addEventListener("keydown", keyDown);
        element.addEventListener("keypress", keyPress);
        element.addEventListener("keyup", keyUp);
        element.addEventListener("paste", paste);
        const hasFocus = element === document.activeElement;
        if (hasFocus) {
            element.blur();
        }
        element.addEventListener("blur", blur);
        parentElement.insertBefore(list, element);
        listItem.appendChild(element);
        if (hasFocus) {
            window.setTimeout(() => {
                element.focus();
            }, 1);
        }
        if (options.maxLength !== -1) {
            element.maxLength = options.maxLength;
        }
        const limitReached = document.createElement("span");
        limitReached.className = "inputItemListLimitReached";
        limitReached.textContent = Language.get("wcf.global.form.input.maxItems");
        Util_1.default.hide(limitReached);
        listItem.appendChild(limitReached);
        let shadow = null;
        const values = [];
        if (options.isCSV) {
            shadow = document.createElement("input");
            shadow.className = "itemListInputShadow";
            shadow.type = "hidden";
            shadow.name = element.name;
            element.removeAttribute("name");
            list.parentNode.insertBefore(shadow, list);
            element.value.split(",").forEach((value) => {
                value = value.trim();
                if (value) {
                    values.push(value);
                }
            });
            if (element.nodeName === "TEXTAREA") {
                const inputElement = document.createElement("input");
                inputElement.type = "text";
                element.parentNode.insertBefore(inputElement, element);
                inputElement.id = element.id;
                element.remove();
                element = inputElement;
            }
        }
        return {
            element: element,
            limitReached: limitReached,
            list: list,
            shadow: shadow,
            values: values,
        };
    }
    /**
     * Returns true if the input accepts new items.
     */
    function acceptsNewItems(elementId) {
        const data = _data.get(elementId);
        if (data.options.maxItems === -1) {
            return true;
        }
        return data.list.childElementCount - 1 < data.options.maxItems;
    }
    /**
     * Enforces the maximum number of items.
     */
    function handleLimit(elementId) {
        const data = _data.get(elementId);
        if (acceptsNewItems(elementId)) {
            Util_1.default.show(data.element);
            Util_1.default.hide(data.limitReached);
            data.list.dataset.acceptsNewItems = "true";
        }
        else {
            Util_1.default.hide(data.element);
            Util_1.default.show(data.limitReached);
            data.list.dataset.acceptsNewItems = "false";
        }
    }
    /**
     * Sets the active item list id and handles keyboard access to remove an existing item.
     */
    function keyDown(event) {
        const input = event.currentTarget;
        const lastItem = input.parentElement.previousElementSibling;
        if (event.key === "Backspace") {
            if (input.value.length === 0) {
                if (lastItem !== null) {
                    if (lastItem.classList.contains("active")) {
                        removeItem(lastItem);
                    }
                    else {
                        lastItem.classList.add("active");
                    }
                }
            }
        }
        else if (event.key === "Escape") {
            if (lastItem !== null && lastItem.classList.contains("active")) {
                lastItem.classList.remove("active");
            }
        }
    }
    /**
     * Detects the Enter key to add an item to the list. This must not be
     * part of the `keydown` handler to prevent conflicts with the suggestions.
     */
    function keyPress(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            const input = event.currentTarget;
            if (_data.get(input.id).options.restricted) {
                // restricted item lists only allow results from the dropdown to be picked
                return;
            }
            const value = input.value.trim();
            if (value.length) {
                addItem(input.id, { objectId: 0, value: value });
            }
        }
    }
    /**
     * Detects changes to the value of an input element to find a comma. This was
     * previously checked in the `keypress` event, but Chromium on Android cripples
     * the event to not expose any key whatsoever.
     */
    function input(event) {
        const input = event.currentTarget;
        if (_data.get(input.id).options.restricted) {
            // restricted item lists only allow results from the dropdown to be picked
            return;
        }
        let value = input.value;
        if (value.includes(",")) {
            // Remove the comma and forward it.
            value = value.replace(/,/g, "");
            if (value.length) {
                addItem(input.id, { objectId: 0, value: value });
            }
            else {
                input.value = value;
            }
        }
    }
    /**
     * Splits comma-separated values being pasted into the input field.
     */
    function paste(event) {
        event.preventDefault();
        const text = event.clipboardData.getData("text/plain");
        const element = event.currentTarget;
        const elementId = element.id;
        const maxLength = +element.maxLength;
        text.split(/,/).forEach((item) => {
            item = item.trim();
            if (maxLength !== -1 && item.length > maxLength) {
                // truncating items provides a better UX than throwing an error or silently discarding it
                item = item.substr(0, maxLength);
            }
            if (item.length > 0 && acceptsNewItems(elementId)) {
                addItem(elementId, { objectId: 0, value: item });
            }
        });
    }
    /**
     * Handles the keyup event to unmark an item for deletion.
     */
    function keyUp(event) {
        const input = event.currentTarget;
        if (input.value.length > 0) {
            const lastItem = input.parentElement.previousElementSibling;
            if (lastItem !== null) {
                lastItem.classList.remove("active");
            }
        }
    }
    /**
     * Adds an item to the list.
     */
    function addItem(elementId, value) {
        const data = _data.get(elementId);
        const listItem = document.createElement("li");
        listItem.className = "item";
        const content = document.createElement("span");
        content.className = "content";
        content.dataset.objectId = value.objectId.toString();
        if (value.type) {
            content.dataset.type = value.type;
        }
        content.textContent = value.value;
        listItem.appendChild(content);
        if (!data.element.disabled) {
            const button = document.createElement("button");
            button.type = "button";
            button.innerHTML = '<fa-icon name="xmark"></fa-icon>';
            button.addEventListener("click", removeItem);
            listItem.appendChild(button);
        }
        data.list.insertBefore(listItem, data.listItem);
        data.suggestion.addExcludedValue(value.value);
        data.element.value = "";
        if (!data.element.disabled) {
            handleLimit(elementId);
        }
        let values = syncShadow(data);
        if (typeof data.options.callbackChange === "function") {
            if (values === null) {
                values = getValues(elementId);
            }
            data.options.callbackChange(elementId, values);
        }
    }
    /**
     * Removes an item from the list.
     */
    function removeItem(item, noFocus) {
        if (item instanceof Event) {
            const target = item.currentTarget;
            item = target.parentElement;
        }
        const parent = item.parentElement;
        const elementId = parent.dataset.elementId || "";
        const data = _data.get(elementId);
        if (item.children[0].textContent) {
            data.suggestion.removeExcludedValue(item.children[0].textContent);
        }
        item.remove();
        if (!noFocus) {
            data.element.focus();
        }
        handleLimit(elementId);
        let values = syncShadow(data);
        if (typeof data.options.callbackChange === "function") {
            if (values === null) {
                values = getValues(elementId);
            }
            data.options.callbackChange(elementId, values);
        }
    }
    /**
     * Synchronizes the shadow input field with the current list item values.
     */
    function syncShadow(data) {
        if (!data.options.isCSV) {
            return null;
        }
        if (typeof data.options.callbackSyncShadow === "function") {
            return data.options.callbackSyncShadow(data);
        }
        const values = getValues(data.element.id);
        data.shadow.value = getValues(data.element.id)
            .map((value) => value.value)
            .join(",");
        return values;
    }
    /**
     * Handles the blur event.
     */
    function blur(event) {
        const input = event.currentTarget;
        const data = _data.get(input.id);
        if (data.options.restricted) {
            // restricted item lists only allow results from the dropdown to be picked
            return;
        }
        const value = input.value.trim();
        if (value.length) {
            if (!data.suggestion || !data.suggestion.isActive()) {
                addItem(input.id, { objectId: 0, value: value });
            }
        }
    }
    /**
     * Initializes an item list.
     *
     * The `values` argument must be empty or contain a list of strings or object, e.g.
     * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
     */
    function init(elementId, values, opts) {
        const element = document.getElementById(elementId);
        if (element === null) {
            throw new Error("Expected a valid element id, '" + elementId + "' is invalid.");
        }
        // remove data from previous instance
        if (_data.has(elementId)) {
            const tmp = _data.get(elementId);
            Object.keys(tmp).forEach((key) => {
                const el = tmp[key];
                if (el instanceof Element && el.parentNode) {
                    el.remove();
                }
            });
            Simple_1.default.destroy(elementId);
            _data.delete(elementId);
        }
        const options = Core.extend({
            // search parameters for suggestions
            ajax: {
                actionName: "getSearchResultList",
                className: "",
                parameters: {},
            },
            // list of excluded string values, e.g. `['ignore', 'these strings', 'when', 'searching']`
            excludedSearchValues: [],
            // maximum number of items this list may contain, `-1` for infinite
            maxItems: -1,
            // maximum length of an item value, `-1` for infinite
            maxLength: -1,
            // disallow custom values, only values offered by the suggestion dropdown are accepted
            restricted: false,
            // initial value will be interpreted as comma separated value and submitted as such
            isCSV: false,
            // will be invoked whenever the items change, receives the element id first and list of values second
            callbackChange: null,
            // callback once the form is about to be submitted
            callbackSubmit: null,
            // Callback for the custom shadow synchronization.
            callbackSyncShadow: null,
            // Callback to set values during the setup.
            callbackSetupValues: null,
            // value may contain the placeholder `{$objectId}`
            submitFieldName: "",
        }, opts);
        const form = DomTraverse.parentByTag(element, "FORM");
        if (form !== null) {
            if (!options.isCSV) {
                if (!options.submitFieldName.length && typeof options.callbackSubmit !== "function") {
                    throw new Error("Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'.");
                }
                form.addEventListener("submit", () => {
                    if (acceptsNewItems(elementId)) {
                        const value = _data.get(elementId).element.value.trim();
                        if (value.length) {
                            addItem(elementId, { objectId: 0, value: value });
                        }
                    }
                    const values = getValues(elementId);
                    if (options.submitFieldName.length) {
                        values.forEach((value) => {
                            const input = document.createElement("input");
                            input.type = "hidden";
                            input.name = options.submitFieldName.replace("{$objectId}", value.objectId.toString());
                            input.value = value.value;
                            form.appendChild(input);
                        });
                    }
                    else {
                        options.callbackSubmit(form, values);
                    }
                });
            }
            else {
                form.addEventListener("submit", () => {
                    if (acceptsNewItems(elementId)) {
                        const value = _data.get(elementId).element.value.trim();
                        if (value.length) {
                            addItem(elementId, { objectId: 0, value: value });
                        }
                    }
                });
            }
        }
        const data = createUI(element, options);
        const suggestion = new Suggestion_1.default(elementId, {
            ajax: options.ajax,
            callbackSelect: addItem,
            excludedSearchValues: options.excludedSearchValues,
        });
        _data.set(elementId, {
            dropdownMenu: null,
            element: data.element,
            limitReached: data.limitReached,
            list: data.list,
            listItem: data.element.parentElement,
            options: options,
            shadow: data.shadow,
            suggestion: suggestion,
        });
        if (options.callbackSetupValues) {
            values = options.callbackSetupValues();
        }
        else {
            values = data.values.length ? data.values : values;
        }
        if (Array.isArray(values)) {
            values.forEach((value) => {
                if (typeof value === "string") {
                    value = { objectId: 0, value: value };
                }
                addItem(elementId, value);
            });
        }
    }
    /**
     * Returns the list of current values.
     */
    function getValues(elementId) {
        const data = _data.get(elementId);
        if (!data) {
            throw new Error("Element id '" + elementId + "' is unknown.");
        }
        const values = [];
        data.list.querySelectorAll(".item > span").forEach((span) => {
            values.push({
                objectId: +(span.dataset.objectId || ""),
                value: span.textContent.trim(),
                type: span.dataset.type,
            });
        });
        return values;
    }
    /**
     * Sets the list of current values.
     */
    function setValues(elementId, values) {
        const data = _data.get(elementId);
        if (!data) {
            throw new Error("Element id '" + elementId + "' is unknown.");
        }
        // remove all existing items first
        DomTraverse.childrenByClass(data.list, "item").forEach((item) => {
            removeItem(item, true);
        });
        // add new items
        values.forEach((value) => {
            addItem(elementId, value);
        });
    }
});
