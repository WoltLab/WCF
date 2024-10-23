/**
 * Simplified and consistent dropdown creation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../Core", "./Simple"], function (require, exports, tslib_1, Core, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.create = create;
    exports.buildItem = buildItem;
    exports.appendItem = appendItem;
    exports.appendItems = appendItems;
    exports.setItems = setItems;
    exports.attach = attach;
    exports.divider = divider;
    Core = tslib_1.__importStar(Core);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    const _validIconSizes = [16, 24, 32, 48, 64, 96, 144];
    function validateList(list) {
        if (!(list instanceof HTMLUListElement)) {
            throw new TypeError("Expected a reference to an <ul> element.");
        }
        if (!list.classList.contains("dropdownMenu")) {
            throw new Error("List does not appear to be a dropdown menu.");
        }
    }
    function buildItemFromData(data) {
        const item = document.createElement("li");
        // handle special `divider` type
        if (data === "divider") {
            item.className = "dropdownDivider";
            return item;
        }
        if (typeof data.identifier === "string") {
            item.dataset.identifier = data.identifier;
        }
        const link = document.createElement("a");
        link.href = typeof data.href === "string" ? data.href : "#";
        if (typeof data.callback === "function") {
            link.addEventListener("click", (event) => {
                event.preventDefault();
                data.callback(link);
            });
        }
        else if (link.href === "#") {
            throw new Error("Expected either a `href` value or a `callback`.");
        }
        if (data.attributes && Core.isPlainObject(data.attributes)) {
            Object.keys(data.attributes).forEach((key) => {
                const value = data.attributes[key];
                if (typeof value !== "string") {
                    throw new Error("Expected only string values.");
                }
                // Support the dash notation for backwards compatibility.
                if (key.indexOf("-") !== -1) {
                    link.setAttribute(`data-${key}`, value);
                }
                else {
                    link.dataset[key] = value;
                }
            });
        }
        item.appendChild(link);
        if (typeof data.icon !== "undefined" && Core.isPlainObject(data.icon)) {
            if (typeof data.icon.name !== "string") {
                throw new TypeError("Expected a valid icon name.");
            }
            let size = 16;
            if (typeof data.icon.size === "number" && _validIconSizes.indexOf(~~data.icon.size) !== -1) {
                size = ~~data.icon.size;
            }
            const icon = document.createElement("fa-icon");
            icon.size = size;
            icon.setIcon(data.icon.name, data.icon.forceSolid ? true : false);
            link.appendChild(icon);
        }
        const label = typeof data.label === "string" ? data.label.trim() : "";
        const labelHtml = typeof data.labelHtml === "string" ? data.labelHtml.trim() : "";
        if (label === "" && labelHtml === "") {
            throw new TypeError("Expected either a label or a `labelHtml`.");
        }
        const span = document.createElement("span");
        span[label ? "textContent" : "innerHTML"] = label ? label : labelHtml;
        link.appendChild(document.createTextNode(" "));
        link.appendChild(span);
        return item;
    }
    /**
     * Creates a new dropdown menu, optionally pre-populated with the supplied list of
     * dropdown items. The list element will be returned and must be manually injected
     * into the DOM by the callee.
     */
    function create(items, identifier) {
        const list = document.createElement("ul");
        list.className = "dropdownMenu";
        if (typeof identifier === "string") {
            list.dataset.identifier = identifier;
        }
        if (Array.isArray(items) && items.length > 0) {
            appendItems(list, items);
        }
        return list;
    }
    /**
     * Creates a new dropdown item that can be inserted into lists using regular DOM operations.
     */
    function buildItem(item) {
        return buildItemFromData(item);
    }
    /**
     * Appends a single item to the target list.
     */
    function appendItem(list, item) {
        validateList(list);
        list.appendChild(buildItemFromData(item));
    }
    /**
     * Appends a list of items to the target list.
     */
    function appendItems(list, items) {
        validateList(list);
        if (!Array.isArray(items)) {
            throw new TypeError("Expected an array of items.");
        }
        const length = items.length;
        if (length === 0) {
            throw new Error("Expected a non-empty list of items.");
        }
        if (length === 1) {
            appendItem(list, items[0]);
        }
        else {
            const fragment = document.createDocumentFragment();
            items.forEach((item) => {
                fragment.appendChild(buildItemFromData(item));
            });
            list.appendChild(fragment);
        }
    }
    /**
     * Replaces the existing list items with the provided list of new items.
     */
    function setItems(list, items) {
        validateList(list);
        list.innerHTML = "";
        appendItems(list, items);
    }
    /**
     * Attaches the list to a button, visibility is from then on controlled through clicks
     * on the provided button element. Internally calls `Ui/SimpleDropdown.initFragment()`
     * to delegate the DOM management.
     */
    function attach(list, button) {
        validateList(list);
        Simple_1.default.initFragment(button, list);
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            Simple_1.default.toggleDropdown(button.id);
        });
    }
    /**
     * Helper method that returns the special string `"divider"` that causes a divider to
     * be created.
     */
    function divider() {
        return "divider";
    }
});
