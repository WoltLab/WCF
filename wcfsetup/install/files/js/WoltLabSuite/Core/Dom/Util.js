/**
 * Provides helper functions to work with DOM nodes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Dom/Util (alias)
 * @module  WoltLabSuite/Core/Dom/Util
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
define(["require", "exports", "../StringUtil"], function (require, exports, StringUtil) {
    "use strict";
    StringUtil = __importStar(StringUtil);
    function _isBoundaryNode(element, ancestor, position) {
        if (!ancestor.contains(element)) {
            throw new Error('Ancestor element does not contain target element.');
        }
        let node;
        let target = element;
        const whichSibling = position + 'Sibling';
        while (target !== null && target !== ancestor) {
            if (target[position + 'ElementSibling'] !== null) {
                return false;
            }
            else if (target[whichSibling]) {
                node = target[whichSibling];
                while (node) {
                    if (node.textContent.trim() !== '') {
                        return false;
                    }
                    node = node[whichSibling];
                }
            }
            target = target.parentNode;
        }
        return true;
    }
    let _idCounter = 0;
    const DomUtil = {
        /**
         * Returns a DocumentFragment containing the provided HTML string as DOM nodes.
         */
        createFragmentFromHtml: function (html) {
            const tmp = document.createElement('div');
            this.setInnerHtml(tmp, html);
            const fragment = document.createDocumentFragment();
            while (tmp.childNodes.length) {
                fragment.appendChild(tmp.childNodes[0]);
            }
            return fragment;
        },
        /**
         * Returns a unique element id.
         */
        getUniqueId: function () {
            let elementId;
            do {
                elementId = 'wcf' + _idCounter++;
            } while (document.getElementById(elementId) !== null);
            return elementId;
        },
        /**
         * Returns the element's id. If there is no id set, a unique id will be
         * created and assigned.
         */
        identify: function (element) {
            if (!(element instanceof Element)) {
                throw new TypeError('Expected a valid DOM element as argument.');
            }
            let id = element.id;
            if (!id) {
                id = this.getUniqueId();
                element.id = id;
            }
            return id;
        },
        /**
         * Returns the outer height of an element including margins.
         */
        outerHeight: function (element, styles) {
            styles = styles || window.getComputedStyle(element);
            let height = element.offsetHeight;
            height += ~~styles.marginTop + ~~styles.marginBottom;
            return height;
        },
        /**
         * Returns the outer width of an element including margins.
         */
        outerWidth: function (element, styles) {
            styles = styles || window.getComputedStyle(element);
            let width = element.offsetWidth;
            width += ~~styles.marginLeft + ~~styles.marginRight;
            return width;
        },
        /**
         * Returns the outer dimensions of an element including margins.
         */
        outerDimensions: function (element) {
            const styles = window.getComputedStyle(element);
            return {
                height: this.outerHeight(element, styles),
                width: this.outerWidth(element, styles),
            };
        },
        /**
         * Returns the element's offset relative to the document's top left corner.
         *
         * @param  {Element}  element          element
         * @return  {{left: int, top: int}}         offset relative to top left corner
         */
        offset: function (element) {
            const rect = element.getBoundingClientRect();
            return {
                top: Math.round(rect.top + (window.scrollY || window.pageYOffset)),
                left: Math.round(rect.left + (window.scrollX || window.pageXOffset)),
            };
        },
        /**
         * Prepends an element to a parent element.
         *
         * @deprecated 5.3 Use `parent.insertBefore(element, parent.firstChild)` instead.
         */
        prepend: function (element, parent) {
            parent.insertBefore(element, parent.firstChild);
        },
        /**
         * Inserts an element after an existing element.
         *
         * @deprecated 5.3 Use `element.parentNode.insertBefore(newElement, element.nextSibling)` instead.
         */
        insertAfter: function (newElement, element) {
            if (element.parentNode === null) {
                throw new Error('The target element has no parent.');
            }
            element.parentNode.insertBefore(newElement, element.nextSibling);
        },
        /**
         * Applies a list of CSS properties to an element.
         */
        setStyles: function (element, styles) {
            let important = false;
            for (const property in styles) {
                if (styles.hasOwnProperty(property)) {
                    if (/ !important$/.test(styles[property])) {
                        important = true;
                        styles[property] = styles[property].replace(/ !important$/, '');
                    }
                    else {
                        important = false;
                    }
                    // for a set style property with priority = important, some browsers are
                    // not able to overwrite it with a property != important; removing the
                    // property first solves this issue
                    if (element.style.getPropertyPriority(property) === 'important' && !important) {
                        element.style.removeProperty(property);
                    }
                    element.style.setProperty(property, styles[property], (important ? 'important' : ''));
                }
            }
        },
        /**
         * Returns a style property value as integer.
         *
         * The behavior of this method is undefined for properties that are not considered
         * to have a "numeric" value, e.g. "background-image".
         */
        styleAsInt: function (styles, propertyName) {
            const value = styles.getPropertyValue(propertyName);
            if (value === null) {
                return 0;
            }
            return parseInt(value, 10);
        },
        /**
         * Sets the inner HTML of given element and reinjects <script> elements to be properly executed.
         *
         * @see    http://www.w3.org/TR/2008/WD-html5-20080610/dom.html#innerhtml0
         * @param  {Element}  element    target element
         * @param  {string}  innerHtml  HTML string
         */
        setInnerHtml: function (element, innerHtml) {
            element.innerHTML = innerHtml;
            let newScript, script, scripts = element.querySelectorAll('script');
            for (let i = 0, length = scripts.length; i < length; i++) {
                script = scripts[i];
                newScript = document.createElement('script');
                if (script.src) {
                    newScript.src = script.src;
                }
                else {
                    newScript.textContent = script.textContent;
                }
                element.appendChild(newScript);
                script.parentNode.removeChild(script);
            }
        },
        /**
         *
         * @param html
         * @param {Element} referenceElement
         * @param insertMethod
         */
        insertHtml: function (html, referenceElement, insertMethod) {
            const element = document.createElement('div');
            this.setInnerHtml(element, html);
            if (!element.childNodes.length) {
                return;
            }
            let node = element.childNodes[0];
            switch (insertMethod) {
                case 'append':
                    referenceElement.appendChild(node);
                    break;
                case 'after':
                    this.insertAfter(node, referenceElement);
                    break;
                case 'prepend':
                    this.prepend(node, referenceElement);
                    break;
                case 'before':
                    if (referenceElement.parentNode === null) {
                        throw new Error('The reference element has no parent, but the insert position was set to \'before\'.');
                    }
                    referenceElement.parentNode.insertBefore(node, referenceElement);
                    break;
                default:
                    throw new Error('Unknown insert method \'' + insertMethod + '\'.');
            }
            let tmp;
            while (element.childNodes.length) {
                tmp = element.childNodes[0];
                this.insertAfter(tmp, node);
                node = tmp;
            }
        },
        /**
         * Returns true if `element` contains the `child` element.
         *
         * @deprecated 5.4 Use `element.contains(child)` instead.
         */
        contains: function (element, child) {
            return element.contains(child);
        },
        /**
         * Retrieves all data attributes from target element, optionally allowing for
         * a custom prefix that serves two purposes: First it will restrict the results
         * for items starting with it and second it will remove that prefix.
         */
        getDataAttributes: function (element, prefix, camelCaseName, idToUpperCase) {
            prefix = prefix || '';
            if (!/^data-/.test(prefix))
                prefix = 'data-' + prefix;
            camelCaseName = (camelCaseName === true);
            idToUpperCase = (idToUpperCase === true);
            let attribute, attributes = {}, name, tmp;
            for (let i = 0, length = element.attributes.length; i < length; i++) {
                attribute = element.attributes[i];
                if (attribute.name.indexOf(prefix) === 0) {
                    name = attribute.name.replace(new RegExp('^' + prefix), '');
                    if (camelCaseName) {
                        tmp = name.split('-');
                        name = '';
                        for (let j = 0, innerLength = tmp.length; j < innerLength; j++) {
                            if (name.length) {
                                if (idToUpperCase && tmp[j] === 'id') {
                                    tmp[j] = 'ID';
                                }
                                else {
                                    tmp[j] = StringUtil.ucfirst(tmp[j]);
                                }
                            }
                            name += tmp[j];
                        }
                    }
                    attributes[name] = attribute.value;
                }
            }
            return attributes;
        },
        /**
         * Unwraps contained nodes by moving them out of `element` while
         * preserving their previous order. Target element will be removed
         * at the end of the operation.
         */
        unwrapChildNodes: function (element) {
            if (element.parentNode === null) {
                throw new Error('The element has no parent.');
            }
            let parent = element.parentNode;
            while (element.childNodes.length) {
                parent.insertBefore(element.childNodes[0], element);
            }
            element.parentNode.removeChild(element);
        },
        /**
         * Replaces an element by moving all child nodes into the new element
         * while preserving their previous order. The old element will be removed
         * at the end of the operation.
         */
        replaceElement: function (oldElement, newElement) {
            if (oldElement.parentNode === null) {
                throw new Error('The old element has no parent.');
            }
            while (oldElement.childNodes.length) {
                newElement.appendChild(oldElement.childNodes[0]);
            }
            oldElement.parentNode.insertBefore(newElement, oldElement);
            oldElement.parentNode.removeChild(oldElement);
        },
        /**
         * Returns true if given element is the most left node of the ancestor, that is
         * a node without any content nor elements before it or its parent nodes.
         */
        isAtNodeStart: function (element, ancestor) {
            return _isBoundaryNode(element, ancestor, 'previous');
        },
        /**
         * Returns true if given element is the most right node of the ancestor, that is
         * a node without any content nor elements after it or its parent nodes.
         */
        isAtNodeEnd: function (element, ancestor) {
            return _isBoundaryNode(element, ancestor, 'next');
        },
        /**
         * Returns the first ancestor element with position fixed or null.
         *
         * @param       {Element}               element         target element
         * @returns     {(Element|null)}        first ancestor with position fixed or null
         */
        getFixedParent: function (element) {
            while (element && element !== document.body) {
                if (window.getComputedStyle(element).getPropertyValue('position') === 'fixed') {
                    return element;
                }
                element = element.offsetParent;
            }
            return null;
        },
    };
    // expose on window object for backward compatibility
    window.bc_wcfDomUtil = DomUtil;
    return DomUtil;
});
