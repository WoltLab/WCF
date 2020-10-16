/**
 * Provides helper functions to traverse the DOM.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dom/Traverse (alias)
 * @module  WoltLabSuite/Core/Dom/Traverse
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.prevByTag = exports.prevByClass = exports.prevBySel = exports.prev = exports.nextByTag = exports.nextByClass = exports.nextBySel = exports.next = exports.parentByTag = exports.parentByClass = exports.parentBySel = exports.childrenByTag = exports.childrenByClass = exports.childrenBySel = exports.childByTag = exports.childByClass = exports.childBySel = void 0;
    const _test = new Map([
        [0 /* None */, () => true],
        [1 /* Selector */, (element, selector) => element.matches(selector)],
        [2 /* ClassName */, (element, className) => element.classList.contains(className)],
        [3 /* TagName */, (element, tagName) => element.nodeName === tagName],
    ]);
    function _getChildren(element, type, value) {
        if (!(element instanceof Element)) {
            throw new TypeError('Expected a valid element as first argument.');
        }
        const children = [];
        for (let i = 0; i < element.childElementCount; i++) {
            if (_test[type](element.children[i], value)) {
                children.push(element.children[i]);
            }
        }
        return children;
    }
    function _getParent(element, type, value, untilElement) {
        if (!(element instanceof Element)) {
            throw new TypeError('Expected a valid element as first argument.');
        }
        let target = element.parentNode;
        while (target instanceof Element) {
            if (target === untilElement) {
                return null;
            }
            if (_test[type](target, value)) {
                return target;
            }
            target = target.parentNode;
        }
        return null;
    }
    function _getSibling(element, siblingType, type, value) {
        if (!(element instanceof Element)) {
            throw new TypeError('Expected a valid element as first argument.');
        }
        if (element instanceof Element) {
            if (element[siblingType] !== null && _test[type](element[siblingType], value)) {
                return element[siblingType];
            }
        }
        return null;
    }
    /**
     * Examines child elements and returns the first child matching the given selector.
     */
    function childBySel(element, selector) {
        return _getChildren(element, 1 /* Selector */, selector)[0] || null;
    }
    exports.childBySel = childBySel;
    /**
     * Examines child elements and returns the first child that has the given CSS class set.
     */
    function childByClass(element, className) {
        return _getChildren(element, 2 /* ClassName */, className)[0] || null;
    }
    exports.childByClass = childByClass;
    /**
     * Examines child elements and returns the first child which equals the given tag.
     */
    function childByTag(element, tagName) {
        return _getChildren(element, 3 /* TagName */, tagName)[0] || null;
    }
    exports.childByTag = childByTag;
    /**
     * Examines child elements and returns all children matching the given selector.
     */
    function childrenBySel(element, selector) {
        return _getChildren(element, 1 /* Selector */, selector);
    }
    exports.childrenBySel = childrenBySel;
    /**
     * Examines child elements and returns all children that have the given CSS class set.
     */
    function childrenByClass(element, className) {
        return _getChildren(element, 2 /* ClassName */, className);
    }
    exports.childrenByClass = childrenByClass;
    /**
     * Examines child elements and returns all children which equal the given tag.
     */
    function childrenByTag(element, tagName) {
        return _getChildren(element, 3 /* TagName */, tagName);
    }
    exports.childrenByTag = childrenByTag;
    /**
     * Examines parent nodes and returns the first parent that matches the given selector.
     */
    function parentBySel(element, selector, untilElement) {
        return _getParent(element, 1 /* Selector */, selector, untilElement);
    }
    exports.parentBySel = parentBySel;
    /**
     * Examines parent nodes and returns the first parent that has the given CSS class set.
     */
    function parentByClass(element, className, untilElement) {
        return _getParent(element, 2 /* ClassName */, className, untilElement);
    }
    exports.parentByClass = parentByClass;
    /**
     * Examines parent nodes and returns the first parent which equals the given tag.
     */
    function parentByTag(element, tagName, untilElement) {
        return _getParent(element, 3 /* TagName */, tagName, untilElement);
    }
    exports.parentByTag = parentByTag;
    /**
     * Returns the next element sibling.
     *
     * @deprecated 5.4 Use `element.nextElementSibling` instead.
     */
    function next(element) {
        return _getSibling(element, 'nextElementSibling', 0 /* None */, '');
    }
    exports.next = next;
    /**
     * Returns the next element sibling that matches the given selector.
     */
    function nextBySel(element, selector) {
        return _getSibling(element, 'nextElementSibling', 1 /* Selector */, selector);
    }
    exports.nextBySel = nextBySel;
    /**
     * Returns the next element sibling with given CSS class.
     */
    function nextByClass(element, className) {
        return _getSibling(element, 'nextElementSibling', 2 /* ClassName */, className);
    }
    exports.nextByClass = nextByClass;
    /**
     * Returns the next element sibling with given CSS class.
     */
    function nextByTag(element, tagName) {
        return _getSibling(element, 'nextElementSibling', 3 /* TagName */, tagName);
    }
    exports.nextByTag = nextByTag;
    /**
     * Returns the previous element sibling.
     *
     * @deprecated 5.4 Use `element.previousElementSibling` instead.
     */
    function prev(element) {
        return _getSibling(element, 'previousElementSibling', 0 /* None */, '');
    }
    exports.prev = prev;
    /**
     * Returns the previous element sibling that matches the given selector.
     */
    function prevBySel(element, selector) {
        return _getSibling(element, 'previousElementSibling', 1 /* Selector */, selector);
    }
    exports.prevBySel = prevBySel;
    /**
     * Returns the previous element sibling with given CSS class.
     */
    function prevByClass(element, className) {
        return _getSibling(element, 'previousElementSibling', 2 /* ClassName */, className);
    }
    exports.prevByClass = prevByClass;
    /**
     * Returns the previous element sibling with given CSS class.
     */
    function prevByTag(element, tagName) {
        return _getSibling(element, 'previousElementSibling', 3 /* TagName */, tagName);
    }
    exports.prevByTag = prevByTag;
});
