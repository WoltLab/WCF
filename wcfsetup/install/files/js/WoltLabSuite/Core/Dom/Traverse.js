/**
 * Provides helper functions to traverse the DOM.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.childBySel = childBySel;
    exports.childByClass = childByClass;
    exports.childByTag = childByTag;
    exports.childrenBySel = childrenBySel;
    exports.childrenByClass = childrenByClass;
    exports.childrenByTag = childrenByTag;
    exports.parentBySel = parentBySel;
    exports.parentByClass = parentByClass;
    exports.parentByTag = parentByTag;
    exports.next = next;
    exports.nextBySel = nextBySel;
    exports.nextByClass = nextByClass;
    exports.nextByTag = nextByTag;
    exports.prev = prev;
    exports.prevBySel = prevBySel;
    exports.prevByClass = prevByClass;
    exports.prevByTag = prevByTag;
    const _test = new Map([
        [0 /* Type.None */, () => true],
        [1 /* Type.Selector */, (element, selector) => element.matches(selector)],
        [2 /* Type.ClassName */, (element, className) => element.classList.contains(className)],
        [3 /* Type.TagName */, (element, tagName) => element.nodeName === tagName],
    ]);
    function _getChildren(element, type, value) {
        if (!(element instanceof Element)) {
            throw new TypeError("Expected a valid element as first argument.");
        }
        const children = [];
        for (let i = 0; i < element.childElementCount; i++) {
            if (_test.get(type)(element.children[i], value)) {
                children.push(element.children[i]);
            }
        }
        return children;
    }
    function _getParent(element, type, value, untilElement) {
        if (!(element instanceof Element)) {
            throw new TypeError("Expected a valid element as first argument.");
        }
        let target = element.parentNode;
        while (target instanceof Element) {
            if (target === untilElement) {
                return null;
            }
            if (_test.get(type)(target, value)) {
                return target;
            }
            target = target.parentNode;
        }
        return null;
    }
    function _getSibling(element, siblingType, type, value) {
        if (!(element instanceof Element)) {
            throw new TypeError("Expected a valid element as first argument.");
        }
        if (element instanceof Element) {
            if (element[siblingType] !== null && _test.get(type)(element[siblingType], value)) {
                return element[siblingType];
            }
        }
        return null;
    }
    /**
     * Examines child elements and returns the first child matching the given selector.
     */
    function childBySel(element, selector) {
        return _getChildren(element, 1 /* Type.Selector */, selector)[0] || null;
    }
    /**
     * Examines child elements and returns the first child that has the given CSS class set.
     */
    function childByClass(element, className) {
        return _getChildren(element, 2 /* Type.ClassName */, className)[0] || null;
    }
    function childByTag(element, tagName) {
        return _getChildren(element, 3 /* Type.TagName */, tagName)[0] || null;
    }
    /**
     * Examines child elements and returns all children matching the given selector.
     */
    function childrenBySel(element, selector) {
        return _getChildren(element, 1 /* Type.Selector */, selector);
    }
    /**
     * Examines child elements and returns all children that have the given CSS class set.
     */
    function childrenByClass(element, className) {
        return _getChildren(element, 2 /* Type.ClassName */, className);
    }
    function childrenByTag(element, tagName) {
        return _getChildren(element, 3 /* Type.TagName */, tagName);
    }
    /**
     * Examines parent nodes and returns the first parent that matches the given selector.
     */
    function parentBySel(element, selector, untilElement) {
        return _getParent(element, 1 /* Type.Selector */, selector, untilElement);
    }
    /**
     * Examines parent nodes and returns the first parent that has the given CSS class set.
     */
    function parentByClass(element, className, untilElement) {
        return _getParent(element, 2 /* Type.ClassName */, className, untilElement);
    }
    /**
     * Examines parent nodes and returns the first parent which equals the given tag.
     */
    function parentByTag(element, tagName, untilElement) {
        return _getParent(element, 3 /* Type.TagName */, tagName, untilElement);
    }
    /**
     * Returns the next element sibling.
     *
     * @deprecated 5.4 Use `element.nextElementSibling` instead.
     */
    function next(element) {
        return _getSibling(element, "nextElementSibling", 0 /* Type.None */, "");
    }
    /**
     * Returns the next element sibling that matches the given selector.
     */
    function nextBySel(element, selector) {
        return _getSibling(element, "nextElementSibling", 1 /* Type.Selector */, selector);
    }
    /**
     * Returns the next element sibling with given CSS class.
     */
    function nextByClass(element, className) {
        return _getSibling(element, "nextElementSibling", 2 /* Type.ClassName */, className);
    }
    /**
     * Returns the next element sibling with given CSS class.
     */
    function nextByTag(element, tagName) {
        return _getSibling(element, "nextElementSibling", 3 /* Type.TagName */, tagName);
    }
    /**
     * Returns the previous element sibling.
     *
     * @deprecated 5.4 Use `element.previousElementSibling` instead.
     */
    function prev(element) {
        return _getSibling(element, "previousElementSibling", 0 /* Type.None */, "");
    }
    /**
     * Returns the previous element sibling that matches the given selector.
     */
    function prevBySel(element, selector) {
        return _getSibling(element, "previousElementSibling", 1 /* Type.Selector */, selector);
    }
    /**
     * Returns the previous element sibling with given CSS class.
     */
    function prevByClass(element, className) {
        return _getSibling(element, "previousElementSibling", 2 /* Type.ClassName */, className);
    }
    /**
     * Returns the previous element sibling with given CSS class.
     */
    function prevByTag(element, tagName) {
        return _getSibling(element, "previousElementSibling", 3 /* Type.TagName */, tagName);
    }
});
