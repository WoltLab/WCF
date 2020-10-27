/**
 * Provides a simple toggle to show or hide certain elements when the
 * target element is checked.
 *
 * Be aware that the list of elements to show or hide accepts selectors
 * which will be passed to `elBySel()`, causing only the first matched
 * element to be used. If you require a whole list of elements identified
 * by a single selector to be handled, please provide the actual list of
 * elements instead.
 *
 * Usage:
 *
 * new UiToggleInput('input[name="foo"][value="bar"]', {
 *      show: ['#showThisContainer', '.makeThisVisibleToo'],
 *      hide: ['.notRelevantStuff', document.getElementById('fooBar')]
 * });
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Toggle/Input
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../Dom/Util"], function (require, exports, Util_1) {
    "use strict";
    Util_1 = __importDefault(Util_1);
    class UiToggleInput {
        /**
         * Initializes a new input toggle.
         */
        constructor(elementSelector, options) {
            const element = document.querySelector(elementSelector);
            if (element === null) {
                throw new Error("Unable to find element by selector '" + elementSelector + "'.");
            }
            const type = (element.nodeName === 'INPUT') ? element.type : '';
            if (type !== 'checkbox' && type !== 'radio') {
                throw new Error("Illegal element, expected input[type='checkbox'] or input[type='radio'].");
            }
            this.element = element;
            this.hide = this.getElements('hide', Array.isArray(options.hide) ? options.hide : []);
            this.hide = this.getElements('show', Array.isArray(options.show) ? options.show : []);
            this.element.addEventListener('change', this.change.bind(this));
            this.updateVisibility(this.show, this.element.checked);
            this.updateVisibility(this.hide, !this.element.checked);
        }
        getElements(type, items) {
            const elements = [];
            items.forEach(item => {
                let element = null;
                if (typeof item === 'string') {
                    element = document.querySelector(item);
                    if (element === null) {
                        throw new Error(`Unable to find an element with the selector '${item}'.`);
                    }
                }
                else if (item instanceof HTMLElement) {
                    element = item;
                }
                else {
                    throw new TypeError(`The array '${type}' may only contain string selectors or DOM elements.`);
                }
                elements.push(element);
            });
            return elements;
        }
        /**
         * Triggered when element is checked / unchecked.
         */
        change(event) {
            const target = event.currentTarget;
            const showElements = target.checked;
            this.updateVisibility(this.show, showElements);
            this.updateVisibility(this.hide, !showElements);
        }
        /**
         * Loops through the target elements and shows / hides them.
         */
        updateVisibility(elements, showElement) {
            elements.forEach(element => {
                Util_1.default[showElement ? 'show' : 'hide'](element);
            });
        }
    }
    return UiToggleInput;
});
