/**
 * Simplified and consistent dropdown creation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Dropdown/Builder
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../Core", "./Simple"], function (require, exports, Core, Simple_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.divider = exports.attach = exports.setItems = exports.appendItems = exports.appendItem = exports.buildItem = exports.create = void 0;
    Core = __importStar(Core);
    Simple_1 = __importDefault(Simple_1);
    const _validIconSizes = [16, 24, 32, 48, 64, 96, 144];
    function validateList(list) {
        if (!(list instanceof HTMLUListElement)) {
            throw new TypeError('Expected a reference to an <ul> element.');
        }
        if (!list.classList.contains('dropdownMenu')) {
            throw new Error('List does not appear to be a dropdown menu.');
        }
    }
    function buildItemFromData(data) {
        const item = document.createElement('li');
        // handle special `divider` type
        if (data === 'divider') {
            item.className = 'dropdownDivider';
            return item;
        }
        if (typeof data.identifier === 'string') {
            item.dataset.identifier = data.identifier;
        }
        const link = document.createElement('a');
        link.href = (typeof data.href === 'string') ? data.href : '#';
        if (typeof data.callback === 'function') {
            link.addEventListener('click', event => {
                event.preventDefault();
                data.callback(link);
            });
        }
        else if (link.href === '#') {
            throw new Error('Expected either a `href` value or a `callback`.');
        }
        if (data.attributes && Core.isPlainObject(data.attributes)) {
            Object.keys(data.attributes).forEach(key => {
                const value = data.attributes[key];
                if (typeof value !== 'string') {
                    throw new Error('Expected only string values.');
                }
                // Support the dash notation for backwards compatibility.
                if (key.indexOf('-') !== -1) {
                    link.setAttribute(`data-${key}`, value);
                }
                else {
                    link.dataset[key] = value;
                }
            });
        }
        item.appendChild(link);
        if (typeof data.icon !== 'undefined' && Core.isPlainObject(data.icon)) {
            if (typeof data.icon.name !== 'string') {
                throw new TypeError('Expected a valid icon name.');
            }
            let size = 16;
            if (typeof data.icon.size === 'number' && _validIconSizes.indexOf(~~data.icon.size) !== -1) {
                size = ~~data.icon.size;
            }
            const icon = document.createElement('span');
            icon.className = 'icon icon' + size + ' fa-' + data.icon.name;
            link.appendChild(icon);
        }
        const label = (typeof data.label === 'string') ? data.label.trim() : '';
        const labelHtml = (typeof data.labelHtml === 'string') ? data.labelHtml.trim() : '';
        if (label === '' && labelHtml === '') {
            throw new TypeError('Expected either a label or a `labelHtml`.');
        }
        const span = document.createElement('span');
        span[label ? 'textContent' : 'innerHTML'] = (label) ? label : labelHtml;
        link.appendChild(document.createTextNode(' '));
        link.appendChild(span);
        return item;
    }
    /**
     * Creates a new dropdown menu, optionally pre-populated with the supplied list of
     * dropdown items. The list element will be returned and must be manually injected
     * into the DOM by the callee.
     */
    function create(items, identifier) {
        const list = document.createElement('ul');
        list.className = 'dropdownMenu';
        if (typeof identifier === 'string') {
            list.dataset.identifier = identifier;
        }
        if (Array.isArray(items) && items.length > 0) {
            appendItems(list, items);
        }
        return list;
    }
    exports.create = create;
    /**
     * Creates a new dropdown item that can be inserted into lists using regular DOM operations.
     */
    function buildItem(item) {
        return buildItemFromData(item);
    }
    exports.buildItem = buildItem;
    /**
     * Appends a single item to the target list.
     */
    function appendItem(list, item) {
        validateList(list);
        list.appendChild(buildItemFromData(item));
    }
    exports.appendItem = appendItem;
    /**
     * Appends a list of items to the target list.
     */
    function appendItems(list, items) {
        validateList(list);
        if (!Array.isArray(items)) {
            throw new TypeError('Expected an array of items.');
        }
        const length = items.length;
        if (length === 0) {
            throw new Error('Expected a non-empty list of items.');
        }
        if (length === 1) {
            appendItem(list, items[0]);
        }
        else {
            const fragment = document.createDocumentFragment();
            items.forEach(item => {
                fragment.appendChild(buildItemFromData(item));
            });
            list.appendChild(fragment);
        }
    }
    exports.appendItems = appendItems;
    /**
     * Replaces the existing list items with the provided list of new items.
     */
    function setItems(list, items) {
        validateList(list);
        list.innerHTML = '';
        appendItems(list, items);
    }
    exports.setItems = setItems;
    /**
     * Attaches the list to a button, visibility is from then on controlled through clicks
     * on the provided button element. Internally calls `Ui/SimpleDropdown.initFragment()`
     * to delegate the DOM management.
     *
     * @param       {Element}               list
     * @param       {Element}               button
     */
    function attach(list, button) {
        validateList(list);
        Simple_1.default.initFragment(button, list);
        button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            Simple_1.default.toggleDropdown(button.id);
        });
    }
    exports.attach = attach;
    /**
     * Helper method that returns the special string `"divider"` that causes a divider to
     * be created.
     */
    function divider() {
        return 'divider';
    }
    exports.divider = divider;
});
