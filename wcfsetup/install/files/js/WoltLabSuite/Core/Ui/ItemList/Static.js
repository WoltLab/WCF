/**
 * Flexible UI element featuring both a list of items and an input field.
 *
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/ItemList/Static
 */
define(['Core', 'Dictionary', 'Language', 'Dom/Traverse', 'EventKey', 'Ui/SimpleDropdown'], function (Core, Dictionary, Language, DomTraverse, EventKey, UiSimpleDropdown) {
    "use strict";
    var _activeId = '';
    var _data = new Dictionary();
    var _didInit = false;
    var _callbackKeyDown = null;
    var _callbackKeyPress = null;
    var _callbackKeyUp = null;
    var _callbackPaste = null;
    var _callbackRemoveItem = null;
    var _callbackBlur = null;
    /**
     * @exports	WoltLabSuite/Core/Ui/ItemList/Static
     */
    return {
        /**
         * Initializes an item list.
         *
         * The `values` argument must be empty or contain a list of strings or object, e.g.
         * `['foo', 'bar']` or `[{ objectId: 1337, value: 'baz'}, {...}]`
         *
         * @param	{string}	elementId	input element id
         * @param	{Array}		values		list of existing values
         * @param	{Object}	options		option list
         */
        init: function (elementId, values, options) {
            var element = elById(elementId);
            if (element === null) {
                throw new Error("Expected a valid element id, '" + elementId + "' is invalid.");
            }
            // remove data from previous instance
            if (_data.has(elementId)) {
                var tmp = _data.get(elementId);
                for (var key in tmp) {
                    if (tmp.hasOwnProperty(key)) {
                        var el = tmp[key];
                        if (el instanceof Element && el.parentNode) {
                            elRemove(el);
                        }
                    }
                }
                UiSimpleDropdown.destroy(elementId);
                _data.delete(elementId);
            }
            options = Core.extend({
                // maximum number of items this list may contain, `-1` for infinite
                maxItems: -1,
                // maximum length of an item value, `-1` for infinite
                maxLength: -1,
                // initial value will be interpreted as comma separated value and submitted as such
                isCSV: false,
                // will be invoked whenever the items change, receives the element id first and list of values second
                callbackChange: null,
                // callback once the form is about to be submitted
                callbackSubmit: null,
                // value may contain the placeholder `{$objectId}`
                submitFieldName: ''
            }, options);
            var form = DomTraverse.parentByTag(element, 'FORM');
            if (form !== null) {
                if (options.isCSV === false) {
                    if (!options.submitFieldName.length && typeof options.callbackSubmit !== 'function') {
                        throw new Error("Expected a valid function for option 'callbackSubmit', a non-empty value for option 'submitFieldName' or enabling the option 'submitFieldCSV'.");
                    }
                    form.addEventListener('submit', (function () {
                        var values = this.getValues(elementId);
                        if (options.submitFieldName.length) {
                            var input;
                            for (var i = 0, length = values.length; i < length; i++) {
                                input = elCreate('input');
                                input.type = 'hidden';
                                input.name = options.submitFieldName.replace('{$objectId}', values[i].objectId);
                                input.value = values[i].value;
                                form.appendChild(input);
                            }
                        }
                        else {
                            options.callbackSubmit(form, values);
                        }
                    }).bind(this));
                }
            }
            this._setup();
            var data = this._createUI(element, options);
            _data.set(elementId, {
                dropdownMenu: null,
                element: data.element,
                list: data.list,
                listItem: data.element.parentNode,
                options: options,
                shadow: data.shadow
            });
            values = (data.values.length) ? data.values : values;
            if (Array.isArray(values)) {
                var value;
                var forceRemoveIcon = !data.element.disabled;
                for (var i = 0, length = values.length; i < length; i++) {
                    value = values[i];
                    if (typeof value === 'string') {
                        value = { objectId: 0, value: value };
                    }
                    this._addItem(elementId, value, forceRemoveIcon);
                }
            }
        },
        /**
         * Returns the list of current values.
         *
         * @param	{string}	elementId	input element id
         * @return	{Array}		list of objects containing object id and value
         */
        getValues: function (elementId) {
            if (!_data.has(elementId)) {
                throw new Error("Element id '" + elementId + "' is unknown.");
            }
            var data = _data.get(elementId);
            var values = [];
            elBySelAll('.item > span', data.list, function (span) {
                values.push({
                    objectId: ~~elData(span, 'object-id'),
                    value: span.textContent
                });
            });
            return values;
        },
        /**
         * Sets the list of current values.
         *
         * @param	{string}	elementId	input element id
         * @param	{Array}		values		list of objects containing object id and value
         */
        setValues: function (elementId, values) {
            if (!_data.has(elementId)) {
                throw new Error("Element id '" + elementId + "' is unknown.");
            }
            var data = _data.get(elementId);
            // remove all existing items first
            var i, length;
            var items = DomTraverse.childrenByClass(data.list, 'item');
            for (i = 0, length = items.length; i < length; i++) {
                this._removeItem(null, items[i], true);
            }
            // add new items
            for (i = 0, length = values.length; i < length; i++) {
                this._addItem(elementId, values[i]);
            }
        },
        /**
         * Binds static event listeners.
         */
        _setup: function () {
            if (_didInit) {
                return;
            }
            _didInit = true;
            _callbackKeyDown = this._keyDown.bind(this);
            _callbackKeyPress = this._keyPress.bind(this);
            _callbackKeyUp = this._keyUp.bind(this);
            _callbackPaste = this._paste.bind(this);
            _callbackRemoveItem = this._removeItem.bind(this);
            _callbackBlur = this._blur.bind(this);
        },
        /**
         * Creates the DOM structure for target element. If `element` is a `<textarea>`
         * it will be automatically replaced with an `<input>` element.
         *
         * @param	{Element}	element		input element
         * @param	{Object}	options		option list
         */
        _createUI: function (element, options) {
            var list = elCreate('ol');
            list.className = 'inputItemList' + (element.disabled ? ' disabled' : '');
            elData(list, 'element-id', element.id);
            list.addEventListener(WCF_CLICK_EVENT, function (event) {
                if (event.target === list) {
                    //noinspection JSUnresolvedFunction
                    element.focus();
                }
            });
            var listItem = elCreate('li');
            listItem.className = 'input';
            list.appendChild(listItem);
            element.addEventListener('keydown', _callbackKeyDown);
            element.addEventListener('keypress', _callbackKeyPress);
            element.addEventListener('keyup', _callbackKeyUp);
            element.addEventListener('paste', _callbackPaste);
            element.addEventListener('blur', _callbackBlur);
            element.parentNode.insertBefore(list, element);
            listItem.appendChild(element);
            if (options.maxLength !== -1) {
                elAttr(element, 'maxLength', options.maxLength);
            }
            var shadow = null, values = [];
            if (options.isCSV) {
                shadow = elCreate('input');
                shadow.className = 'itemListInputShadow';
                shadow.type = 'hidden';
                //noinspection JSUnresolvedVariable
                shadow.name = element.name;
                element.removeAttribute('name');
                list.parentNode.insertBefore(shadow, list);
                //noinspection JSUnresolvedVariable
                var value, tmp = element.value.split(',');
                for (var i = 0, length = tmp.length; i < length; i++) {
                    value = tmp[i].trim();
                    if (value.length) {
                        values.push(value);
                    }
                }
                if (element.nodeName === 'TEXTAREA') {
                    var inputElement = elCreate('input');
                    inputElement.type = 'text';
                    element.parentNode.insertBefore(inputElement, element);
                    inputElement.id = element.id;
                    elRemove(element);
                    element = inputElement;
                }
            }
            return {
                element: element,
                list: list,
                shadow: shadow,
                values: values
            };
        },
        /**
         * Enforces the maximum number of items.
         *
         * @param	{string}	elementId	input element id
         */
        _handleLimit: function (elementId) {
            var data = _data.get(elementId);
            if (data.options.maxItems === -1) {
                return;
            }
            if (data.list.childElementCount - 1 < data.options.maxItems) {
                if (data.element.disabled) {
                    data.element.disabled = false;
                    data.element.removeAttribute('placeholder');
                }
            }
            else if (!data.element.disabled) {
                data.element.disabled = true;
                elAttr(data.element, 'placeholder', Language.get('wcf.global.form.input.maxItems'));
            }
        },
        /**
         * Sets the active item list id and handles keyboard access to remove an existing item.
         *
         * @param	{object}	event		event object
         */
        _keyDown: function (event) {
            var input = event.currentTarget;
            var lastItem = input.parentNode.previousElementSibling;
            _activeId = input.id;
            if (event.keyCode === 8) {
                // 8 = [BACKSPACE]
                if (input.value.length === 0) {
                    if (lastItem !== null) {
                        if (lastItem.classList.contains('active')) {
                            this._removeItem(null, lastItem);
                        }
                        else {
                            lastItem.classList.add('active');
                        }
                    }
                }
            }
            else if (event.keyCode === 27) {
                // 27 = [ESC]
                if (lastItem !== null && lastItem.classList.contains('active')) {
                    lastItem.classList.remove('active');
                }
            }
        },
        /**
         * Handles the `[ENTER]` and `[,]` key to add an item to the list.
         *
         * @param	{Event}         event		event object
         */
        _keyPress: function (event) {
            if (EventKey.Enter(event) || EventKey.Comma(event)) {
                event.preventDefault();
                var value = event.currentTarget.value.trim();
                if (value.length) {
                    this._addItem(event.currentTarget.id, { objectId: 0, value: value });
                }
            }
        },
        /**
         * Splits comma-separated values being pasted into the input field.
         *
         * @param       {Event}         event
         * @protected
         */
        _paste: function (event) {
            var text = '';
            if (typeof window.clipboardData === 'object') {
                // IE11
                text = window.clipboardData.getData('Text');
            }
            else {
                text = event.clipboardData.getData('text/plain');
            }
            text.split(/,/).forEach((function (item) {
                item = item.trim();
                if (item.length !== 0) {
                    this._addItem(event.currentTarget.id, { objectId: 0, value: item });
                }
            }).bind(this));
            event.preventDefault();
        },
        /**
         * Handles the keyup event to unmark an item for deletion.
         *
         * @param	{object}	event		event object
         */
        _keyUp: function (event) {
            var input = event.currentTarget;
            if (input.value.length > 0) {
                var lastItem = input.parentNode.previousElementSibling;
                if (lastItem !== null) {
                    lastItem.classList.remove('active');
                }
            }
        },
        /**
         * Adds an item to the list.
         *
         * @param	{string}	elementId		input element id
         * @param	{object}	value			item value
         * @param	{?boolean}	forceRemoveIcon		if `true`, the icon to remove the item will be added in every case
         */
        _addItem: function (elementId, value, forceRemoveIcon) {
            var data = _data.get(elementId);
            var listItem = elCreate('li');
            listItem.className = 'item';
            var content = elCreate('span');
            content.className = 'content';
            elData(content, 'object-id', value.objectId);
            content.textContent = value.value;
            listItem.appendChild(content);
            if (forceRemoveIcon || !data.element.disabled) {
                var button = elCreate('a');
                button.className = 'icon icon16 fa-times';
                button.addEventListener(WCF_CLICK_EVENT, _callbackRemoveItem);
                listItem.appendChild(button);
            }
            data.list.insertBefore(listItem, data.listItem);
            data.element.value = '';
            if (!data.element.disabled) {
                this._handleLimit(elementId);
            }
            var values = this._syncShadow(data);
            if (typeof data.options.callbackChange === 'function') {
                if (values === null)
                    values = this.getValues(elementId);
                data.options.callbackChange(elementId, values);
            }
        },
        /**
         * Removes an item from the list.
         *
         * @param	{?object}	event		event object
         * @param	{Element?}	item		list item
         * @param	{boolean?}	noFocus		input element will not be focused if true
         */
        _removeItem: function (event, item, noFocus) {
            item = (event === null) ? item : event.currentTarget.parentNode;
            var parent = item.parentNode;
            //noinspection JSCheckFunctionSignatures
            var elementId = elData(parent, 'element-id');
            var data = _data.get(elementId);
            parent.removeChild(item);
            if (!noFocus)
                data.element.focus();
            this._handleLimit(elementId);
            var values = this._syncShadow(data);
            if (typeof data.options.callbackChange === 'function') {
                if (values === null)
                    values = this.getValues(elementId);
                data.options.callbackChange(elementId, values);
            }
        },
        /**
         * Synchronizes the shadow input field with the current list item values.
         *
         * @param	{object}	data		element data
         */
        _syncShadow: function (data) {
            if (!data.options.isCSV)
                return null;
            var value = '', values = this.getValues(data.element.id);
            for (var i = 0, length = values.length; i < length; i++) {
                value += (value.length ? ',' : '') + values[i].value;
            }
            data.shadow.value = value;
            return values;
        },
        /**
         * Handles the blur event.
         *
         * @param	{object}	event		event object
         */
        _blur: function (event) {
            var data = _data.get(event.currentTarget.id);
            var currentTarget = event.currentTarget;
            window.setTimeout(function () {
                var value = currentTarget.value.trim();
                if (value.length) {
                    this._addItem(currentTarget.id, { objectId: 0, value: value });
                }
            }.bind(this), 100);
        }
    };
});
