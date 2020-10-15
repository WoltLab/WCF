/**
 * Provides the basic core functionality.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Core (alias)
 * @module  WoltLabSuite/Core/Core
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getStoragePrefix = exports.triggerEvent = exports.serialize = exports.getUuid = exports.getType = exports.isPlainObject = exports.inherit = exports.extend = exports.convertLegacyUrl = exports.clone = void 0;
    const _clone = function (variable) {
        if (typeof variable === 'object' && (Array.isArray(variable) || isPlainObject(variable))) {
            return _cloneObject(variable);
        }
        return variable;
    };
    const _cloneObject = function (obj) {
        if (!obj) {
            return null;
        }
        if (Array.isArray(obj)) {
            return obj.slice();
        }
        const newObj = {};
        Object.keys(obj).forEach(key => newObj[key] = _clone(obj[key]));
        return newObj;
    };
    //noinspection JSUnresolvedVariable
    const _prefix = 'wsc1337' + /*window.WCF_PATH.hashCode()*/ +'-';
    /**
     * Deep clones an object.
     */
    function clone(obj) {
        return _clone(obj);
    }
    exports.clone = clone;
    /**
     * Converts WCF 2.0-style URLs into the default URL layout.
     */
    function convertLegacyUrl(url) {
        return url.replace(/^index\.php\/(.*?)\/\?/, (match, controller) => {
            const parts = controller.split(/([A-Z][a-z0-9]+)/);
            controller = '';
            for (let i = 0, length = parts.length; i < length; i++) {
                const part = parts[i].trim();
                if (part.length) {
                    if (controller.length)
                        controller += '-';
                    controller += part.toLowerCase();
                }
            }
            return `index.php?${controller}/&`;
        });
    }
    exports.convertLegacyUrl = convertLegacyUrl;
    /**
     * Merges objects with the first argument.
     *
     * @param  {object}  out    destination object
     * @param  {...object}  args  variable number of objects to be merged into the destination object
     * @return  {object}  destination object with all provided objects merged into
     */
    function extend(out, ...args) {
        out = out || {};
        const newObj = clone(out);
        for (let i = 1, length = args.length; i < length; i++) {
            const obj = args[i];
            if (!obj)
                continue;
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    if (!Array.isArray(obj[key]) && typeof obj[key] === 'object') {
                        if (isPlainObject(obj[key])) {
                            // object literals have the prototype of Object which in return has no parent prototype
                            newObj[key] = extend(out[key], obj[key]);
                        }
                        else {
                            newObj[key] = obj[key];
                        }
                    }
                    else {
                        newObj[key] = obj[key];
                    }
                }
            }
        }
        return newObj;
    }
    exports.extend = extend;
    /**
     * Inherits the prototype methods from one constructor to another
     * constructor.
     *
     * Usage:
     *
     * function MyDerivedClass() {}
     * Core.inherit(MyDerivedClass, TheAwesomeBaseClass, {
     *      // regular prototype for `MyDerivedClass`
     *
     *      overwrittenMethodFromBaseClass: function(foo, bar) {
     *              // do stuff
     *
     *              // invoke parent
     *              MyDerivedClass._super.prototype.overwrittenMethodFromBaseClass.call(this, foo, bar);
     *      }
     * });
     *
     * @see  https://github.com/nodejs/node/blob/7d14dd9b5e78faabb95d454a79faa513d0bbc2a5/lib/util.js#L697-L735
     */
    function inherit(constructor, superConstructor, propertiesObject) {
        if (constructor === undefined || constructor === null) {
            throw new TypeError('The constructor must not be undefined or null.');
        }
        if (superConstructor === undefined || superConstructor === null) {
            throw new TypeError('The super constructor must not be undefined or null.');
        }
        if (superConstructor.prototype === undefined) {
            throw new TypeError('The super constructor must have a prototype.');
        }
        constructor._super = superConstructor;
        constructor.prototype = extend(Object.create(superConstructor.prototype, {
            constructor: {
                configurable: true,
                enumerable: false,
                value: constructor,
                writable: true,
            },
        }), propertiesObject || {});
    }
    exports.inherit = inherit;
    /**
     * Returns true if `obj` is an object literal.
     */
    function isPlainObject(obj) {
        if (typeof obj !== 'object' || obj === null || obj.nodeType) {
            return false;
        }
        return (Object.getPrototypeOf(obj) === Object.prototype);
    }
    exports.isPlainObject = isPlainObject;
    /**
     * Returns the object's class name.
     */
    function getType(obj) {
        return Object.prototype.toString.call(obj).replace(/^\[object (.+)]$/, '$1');
    }
    exports.getType = getType;
    /**
     * Returns a RFC4122 version 4 compilant UUID.
     *
     * @see    http://stackoverflow.com/a/2117523
     */
    function getUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    exports.getUuid = getUuid;
    /**
     * Recursively serializes an object into an encoded URI parameter string.
     */
    function serialize(obj, prefix) {
        let parameters = [];
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                const parameterKey = (prefix) ? prefix + '[' + key + ']' : key;
                const value = obj[key];
                if (typeof value === 'object') {
                    parameters.push(serialize(value, parameterKey));
                }
                else {
                    parameters.push(encodeURIComponent(parameterKey) + '=' + encodeURIComponent(value));
                }
            }
        }
        return parameters.join('&');
    }
    exports.serialize = serialize;
    /**
     * Triggers a custom or built-in event.
     */
    function triggerEvent(element, eventName) {
        let event;
        try {
            event = new Event(eventName, {
                bubbles: true,
                cancelable: true,
            });
        }
        catch (e) {
            event = document.createEvent('Event');
            event.initEvent(eventName, true, true);
        }
        element.dispatchEvent(event);
    }
    exports.triggerEvent = triggerEvent;
    /**
     * Returns the unique prefix for the localStorage.
     */
    function getStoragePrefix() {
        return _prefix;
    }
    exports.getStoragePrefix = getStoragePrefix;
});
