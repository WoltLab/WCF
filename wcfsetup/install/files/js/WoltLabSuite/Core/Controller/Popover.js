/**
 * Versatile popover manager.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Popover
 */
define(["require", "exports", "tslib", "../Ajax", "../Dom/Change/Listener", "../Dom/Util", "../Environment", "../Ui/Alignment"], function (require, exports, tslib_1, Ajax, Listener_1, Util_1, Environment, UiAlignment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ajaxApi = exports.setContent = exports.init = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Environment = tslib_1.__importStar(Environment);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    class ControllerPopover {
        /**
         * Builds popover DOM elements and binds event listeners.
         */
        constructor() {
            this.activeId = "";
            this.cache = new Map();
            this.elements = new Map();
            this.handlers = new Map();
            this.hoverId = "";
            this.suspended = false;
            this.timerEnter = undefined;
            this.timerLeave = undefined;
            this.popover = document.createElement("div");
            this.popover.className = "popover forceHide";
            this.popoverContent = document.createElement("div");
            this.popoverContent.className = "popoverContent";
            this.popover.appendChild(this.popoverContent);
            const pointer = document.createElement("span");
            pointer.className = "elementPointer";
            pointer.appendChild(document.createElement("span"));
            this.popover.appendChild(pointer);
            document.body.appendChild(this.popover);
            // event listener
            this.popover.addEventListener("mouseenter", () => this.popoverMouseEnter());
            this.popover.addEventListener("mouseleave", () => this.mouseLeave());
            this.popover.addEventListener("animationend", () => this.clearContent());
            window.addEventListener("beforeunload", () => {
                this.suspended = true;
                if (this.timerEnter) {
                    window.clearTimeout(this.timerEnter);
                    this.timerEnter = undefined;
                }
                this.hidePopover();
            });
            Listener_1.default.add("WoltLabSuite/Core/Controller/Popover", (identifier) => this.initHandler(identifier));
        }
        /**
         * Initializes a popover handler.
         *
         * Usage:
         *
         * ControllerPopover.init({
         * 	attributeName: 'data-object-id',
         * 	className: 'fooLink',
         * 	identifier: 'com.example.bar.foo',
         * 	loadCallback: (objectId, popover) => {
         * 		// request data for object id (e.g. via WoltLabSuite/Core/Ajax)
         *
         * 		// then call this to set the content
         * 		popover.setContent('com.example.bar.foo', objectId, htmlTemplateString);
         * 	}
         * });
         */
        init(options) {
            if (Environment.platform() !== "desktop") {
                return;
            }
            options.attributeName = options.attributeName || "data-object-id";
            options.legacy = options.legacy === true;
            if (this.handlers.has(options.identifier)) {
                return;
            }
            // Legacy implementations provided a selector for `className`.
            const selector = options.legacy ? options.className : `.${options.className}`;
            this.handlers.set(options.identifier, {
                attributeName: options.attributeName,
                dboAction: options.dboAction,
                legacy: options.legacy,
                loadCallback: options.loadCallback,
                selector: selector,
            });
            this.initHandler(options.identifier);
        }
        /**
         * Initializes a popover handler.
         */
        initHandler(identifier) {
            if (typeof identifier === "string" && identifier.length) {
                this.initElements(this.handlers.get(identifier), identifier);
            }
            else {
                this.handlers.forEach((value, key) => {
                    this.initElements(value, key);
                });
            }
        }
        /**
         * Binds event listeners for popover-enabled elements.
         */
        initElements(options, identifier) {
            document.querySelectorAll(options.selector).forEach((element) => {
                const id = Util_1.default.identify(element);
                if (this.cache.has(id)) {
                    return;
                }
                // Skip elements that are located inside a popover.
                if (element.closest(".popover") !== null) {
                    this.cache.set(id, {
                        content: null,
                        state: 0 /* None */,
                    });
                    return;
                }
                const objectId = options.legacy ? id : ~~element.getAttribute(options.attributeName);
                if (objectId === 0) {
                    return;
                }
                element.addEventListener("mouseenter", (ev) => this.mouseEnter(ev));
                element.addEventListener("mouseleave", () => this.mouseLeave());
                if (element instanceof HTMLAnchorElement && element.href) {
                    element.addEventListener("click", () => this.hidePopover());
                }
                const cacheId = `${identifier}-${objectId}`;
                element.dataset.cacheId = cacheId;
                this.elements.set(id, {
                    element,
                    identifier,
                    objectId: objectId.toString(),
                });
                if (!this.cache.has(cacheId)) {
                    this.cache.set(cacheId, {
                        content: null,
                        state: 0 /* None */,
                    });
                }
            });
        }
        /**
         * Sets the content for given identifier and object id.
         */
        setContent(identifier, objectId, content) {
            const cacheId = `${identifier}-${objectId}`;
            const data = this.cache.get(cacheId);
            if (data === undefined) {
                throw new Error(`Unable to find element for object id '${objectId}' (identifier: '${identifier}').`);
            }
            let fragment = Util_1.default.createFragmentFromHtml(content);
            if (!fragment.childElementCount) {
                fragment = Util_1.default.createFragmentFromHtml("<p>" + content + "</p>");
            }
            data.content = fragment;
            data.state = 2 /* Ready */;
            if (this.activeId) {
                const activeElement = this.elements.get(this.activeId).element;
                if (activeElement.dataset.cacheId === cacheId) {
                    this.show();
                }
            }
        }
        /**
         * Handles the mouse start hovering the popover-enabled element.
         */
        mouseEnter(event) {
            if (this.suspended) {
                return;
            }
            if (this.timerEnter) {
                window.clearTimeout(this.timerEnter);
                this.timerEnter = undefined;
            }
            const id = Util_1.default.identify(event.currentTarget);
            if (this.activeId === id && this.timerLeave) {
                window.clearTimeout(this.timerLeave);
                this.timerLeave = undefined;
            }
            this.hoverId = id;
            this.timerEnter = window.setTimeout(() => {
                this.timerEnter = undefined;
                if (this.hoverId === id) {
                    this.show();
                }
            }, 800 /* Show */);
        }
        /**
         * Handles the mouse leaving the popover-enabled element or the popover itself.
         */
        mouseLeave() {
            this.hoverId = "";
            if (this.timerLeave) {
                return;
            }
            this.timerLeave = window.setTimeout(() => this.hidePopover(), 500 /* Hide */);
        }
        /**
         * Handles the mouse start hovering the popover element.
         */
        popoverMouseEnter() {
            if (this.timerLeave) {
                window.clearTimeout(this.timerLeave);
                this.timerLeave = undefined;
            }
        }
        /**
         * Shows the popover and loads content on-the-fly.
         */
        show() {
            if (this.timerLeave) {
                window.clearTimeout(this.timerLeave);
                this.timerLeave = undefined;
            }
            let forceHide = false;
            if (this.popover.classList.contains("active")) {
                if (this.activeId !== this.hoverId) {
                    this.hidePopover();
                    forceHide = true;
                }
            }
            else if (this.popoverContent.childElementCount) {
                forceHide = true;
            }
            if (forceHide) {
                this.popover.classList.add("forceHide");
                // force layout
                //noinspection BadExpressionStatementJS
                this.popover.offsetTop;
                this.clearContent();
                this.popover.classList.remove("forceHide");
            }
            this.activeId = this.hoverId;
            const elementData = this.elements.get(this.activeId);
            // check if source element is already gone
            if (elementData === undefined) {
                return;
            }
            const cacheId = elementData.element.dataset.cacheId;
            const data = this.cache.get(cacheId);
            switch (data.state) {
                case 2 /* Ready */: {
                    this.popoverContent.appendChild(data.content);
                    this.rebuild();
                    break;
                }
                case 0 /* None */: {
                    data.state = 1 /* Loading */;
                    const handler = this.handlers.get(elementData.identifier);
                    if (handler.loadCallback) {
                        handler.loadCallback(elementData.objectId, this, elementData.element);
                    }
                    else if (handler.dboAction) {
                        const callback = (data) => {
                            this.setContent(elementData.identifier, elementData.objectId, data.returnValues.template);
                            return true;
                        };
                        this.ajaxApi({
                            actionName: "getPopover",
                            className: handler.dboAction,
                            interfaceName: "wcf\\data\\IPopoverAction",
                            objectIDs: [elementData.objectId],
                        }, callback, callback);
                    }
                    break;
                }
                case 1 /* Loading */: {
                    // Do not interrupt inflight requests.
                    break;
                }
            }
        }
        /**
         * Hides the popover element.
         */
        hidePopover() {
            if (this.timerLeave) {
                window.clearTimeout(this.timerLeave);
                this.timerLeave = undefined;
            }
            this.popover.classList.remove("active");
        }
        /**
         * Clears popover content by moving it back into the cache.
         */
        clearContent() {
            if (this.activeId && this.popoverContent.childElementCount && !this.popover.classList.contains("active")) {
                const cacheId = this.elements.get(this.activeId).element.dataset.cacheId;
                const activeElData = this.cache.get(cacheId);
                while (this.popoverContent.childNodes.length) {
                    activeElData.content.appendChild(this.popoverContent.childNodes[0]);
                }
            }
        }
        /**
         * Rebuilds the popover.
         */
        rebuild() {
            if (this.popover.classList.contains("active")) {
                return;
            }
            this.popover.classList.remove("forceHide");
            this.popover.classList.add("active");
            UiAlignment.set(this.popover, this.elements.get(this.activeId).element, {
                pointer: true,
                vertical: "top",
            });
        }
        _ajaxSuccess() {
            // This class was designed in a strange way without utilizing this method.
        }
        _ajaxSetup() {
            return {
                silent: true,
            };
        }
        /**
         * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
         */
        ajaxApi(data, success, failure) {
            if (typeof success !== "function") {
                throw new TypeError("Expected a valid callback for parameter 'success'.");
            }
            Ajax.api(this, data, success, failure);
        }
    }
    let controllerPopover;
    function getControllerPopover() {
        if (!controllerPopover) {
            controllerPopover = new ControllerPopover();
        }
        return controllerPopover;
    }
    /**
     * Initializes a popover handler.
     *
     * Usage:
     *
     * ControllerPopover.init({
     * 	attributeName: 'data-object-id',
     * 	className: 'fooLink',
     * 	identifier: 'com.example.bar.foo',
     * 	loadCallback: function(objectId, popover) {
     * 		// request data for object id (e.g. via WoltLabSuite/Core/Ajax)
     *
     * 		// then call this to set the content
     * 		popover.setContent('com.example.bar.foo', objectId, htmlTemplateString);
     * 	}
     * });
     */
    function init(options) {
        getControllerPopover().init(options);
    }
    exports.init = init;
    /**
     * Sets the content for given identifier and object id.
     */
    function setContent(identifier, objectId, content) {
        getControllerPopover().setContent(identifier, objectId, content);
    }
    exports.setContent = setContent;
    /**
     * Sends an AJAX requests to the server, simple wrapper to reuse the request object.
     */
    function ajaxApi(data, success, failure) {
        getControllerPopover().ajaxApi(data, success, failure);
    }
    exports.ajaxApi = ajaxApi;
});
