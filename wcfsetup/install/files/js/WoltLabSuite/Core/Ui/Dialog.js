/**
 * Modal dialog handler.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Dialog (alias)
 * @module  WoltLabSuite/Core/Ui/Dialog
 */
define(["require", "exports", "tslib", "../Core", "../Dom/Change/Listener", "./Screen", "../Dom/Util", "../Language", "../Environment", "../Event/Handler", "./Dropdown/Simple"], function (require, exports, tslib_1, Core, Listener_1, UiScreen, Util_1, Language, Environment, EventHandler, Simple_1) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Environment = tslib_1.__importStar(Environment);
    EventHandler = tslib_1.__importStar(EventHandler);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    let _activeDialog = null;
    let _callbackFocus;
    let _container;
    const _dialogs = new Map();
    let _dialogFullHeight = false;
    const _dialogObjects = new WeakMap();
    const _dialogToObject = new Map();
    let _focusedBeforeDialog;
    let _keyupListener;
    const _validCallbacks = ["onBeforeClose", "onClose", "onShow"];
    // list of supported `input[type]` values for dialog submit
    const _validInputTypes = ["number", "password", "search", "tel", "text", "url"];
    const _focusableElements = [
        'a[href]:not([tabindex^="-"]):not([inert])',
        'area[href]:not([tabindex^="-"]):not([inert])',
        "input:not([disabled]):not([inert])",
        "select:not([disabled]):not([inert])",
        "textarea:not([disabled]):not([inert])",
        "button:not([disabled]):not([inert])",
        'iframe:not([tabindex^="-"]):not([inert])',
        'audio:not([tabindex^="-"]):not([inert])',
        'video:not([tabindex^="-"]):not([inert])',
        '[contenteditable]:not([tabindex^="-"]):not([inert])',
        '[tabindex]:not([tabindex^="-"]):not([inert])',
    ];
    /**
     * @exports  WoltLabSuite/Core/Ui/Dialog
     */
    const UiDialog = {
        /**
         * Sets up global container and internal variables.
         */
        setup() {
            _container = document.createElement("div");
            _container.classList.add("dialogOverlay");
            _container.setAttribute("aria-hidden", "true");
            _container.addEventListener("mousedown", (ev) => this._closeOnBackdrop(ev));
            _container.addEventListener("wheel", (event) => {
                if (event.target === _container) {
                    event.preventDefault();
                }
            }, { passive: false });
            document.getElementById("content").appendChild(_container);
            _keyupListener = (event) => {
                if (event.key === "Escape") {
                    const target = event.target;
                    if (target.nodeName !== "INPUT" && target.nodeName !== "TEXTAREA") {
                        this.close(_activeDialog);
                        return false;
                    }
                }
                return true;
            };
            UiScreen.on("screen-xs", {
                match() {
                    _dialogFullHeight = true;
                },
                unmatch() {
                    _dialogFullHeight = false;
                },
                setup() {
                    _dialogFullHeight = true;
                },
            });
            this._initStaticDialogs();
            Listener_1.default.add("Ui/Dialog", () => {
                this._initStaticDialogs();
            });
            UiScreen.setDialogContainer(_container);
            window.addEventListener("resize", () => {
                _dialogs.forEach((dialog) => {
                    if (!Core.stringToBool(dialog.dialog.getAttribute("aria-hidden"))) {
                        this.rebuild(dialog.dialog.dataset.id || "");
                    }
                });
            });
        },
        _initStaticDialogs() {
            document.querySelectorAll(".jsStaticDialog").forEach((button) => {
                button.classList.remove("jsStaticDialog");
                const id = button.dataset.dialogId || "";
                if (id) {
                    const container = document.getElementById(id);
                    if (container !== null) {
                        container.classList.remove("jsStaticDialogContent");
                        container.dataset.isStaticDialog = "true";
                        Util_1.default.hide(container);
                        button.addEventListener("click", (event) => {
                            event.preventDefault();
                            this.openStatic(container.id, null, { title: container.dataset.title || "" });
                        });
                    }
                }
            });
        },
        /**
         * Opens the dialog and implicitly creates it on first usage.
         */
        open(callbackObject, html) {
            let dialogData = _dialogObjects.get(callbackObject);
            if (dialogData && Core.isPlainObject(dialogData)) {
                // dialog already exists
                return this.openStatic(dialogData.id, typeof html === "undefined" ? null : html);
            }
            // initialize a new dialog
            if (typeof callbackObject._dialogSetup !== "function") {
                throw new Error("Callback object does not implement the method '_dialogSetup()'.");
            }
            const setupData = callbackObject._dialogSetup();
            if (!Core.isPlainObject(setupData)) {
                throw new Error("Expected an object literal as return value of '_dialogSetup()'.");
            }
            const id = setupData.id;
            dialogData = { id };
            let dialogElement;
            if (setupData.source === undefined) {
                dialogElement = document.getElementById(id);
                if (dialogElement === null) {
                    throw new Error("Element id '" +
                        id +
                        "' is invalid and no source attribute was given. If you want to use the `html` argument instead, please add `source: null` to your dialog configuration.");
                }
                setupData.source = document.createDocumentFragment();
                setupData.source.appendChild(dialogElement);
                dialogElement.removeAttribute("id");
                Util_1.default.show(dialogElement);
            }
            else if (setupData.source === null) {
                // `null` means there is no static markup and `html` should be used instead
                setupData.source = html;
            }
            else if (typeof setupData.source === "function") {
                setupData.source();
            }
            else if (Core.isPlainObject(setupData.source)) {
                if (typeof html === "string" && html.trim() !== "") {
                    setupData.source = html;
                }
                else {
                    new Promise((resolve_1, reject_1) => { require(["../Ajax"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((Ajax) => {
                        const source = setupData.source;
                        Ajax.api(this, source.data, (data) => {
                            if (data.returnValues && typeof data.returnValues.template === "string") {
                                this.open(callbackObject, data.returnValues.template);
                                if (typeof source.after === "function") {
                                    source.after(_dialogs.get(id).content, data);
                                }
                            }
                        });
                    });
                    return {};
                }
            }
            else {
                if (typeof setupData.source === "string") {
                    dialogElement = document.createElement("div");
                    dialogElement.id = id;
                    Util_1.default.setInnerHtml(dialogElement, setupData.source);
                    setupData.source = document.createDocumentFragment();
                    setupData.source.appendChild(dialogElement);
                }
                if (!setupData.source.nodeType || setupData.source.nodeType !== Node.DOCUMENT_FRAGMENT_NODE) {
                    throw new Error("Expected at least a document fragment as 'source' attribute.");
                }
            }
            _dialogObjects.set(callbackObject, dialogData);
            _dialogToObject.set(id, callbackObject);
            return this.openStatic(id, setupData.source, setupData.options);
        },
        /**
         * Opens an dialog, if the dialog is already open the content container
         * will be replaced by the HTML string contained in the parameter html.
         *
         * If id is an existing element id, html will be ignored and the referenced
         * element will be appended to the content element instead.
         */
        openStatic(id, html, options) {
            UiScreen.pageOverlayOpen();
            if (Environment.platform() !== "desktop") {
                if (!this.isOpen(id)) {
                    UiScreen.scrollDisable();
                }
            }
            if (_dialogs.has(id)) {
                this._updateDialog(id, html);
            }
            else {
                options = Core.extend({
                    backdropCloseOnClick: true,
                    closable: true,
                    closeButtonLabel: Language.get("wcf.global.button.close"),
                    closeConfirmMessage: "",
                    disableContentPadding: false,
                    title: "",
                    onBeforeClose: null,
                    onClose: null,
                    onShow: null,
                }, options || {});
                if (!options.closable)
                    options.backdropCloseOnClick = false;
                if (options.closeConfirmMessage) {
                    options.onBeforeClose = (id) => {
                        new Promise((resolve_2, reject_2) => { require(["./Confirmation"], resolve_2, reject_2); }).then(tslib_1.__importStar).then((UiConfirmation) => {
                            UiConfirmation.show({
                                confirm: this.close.bind(this, id),
                                message: options.closeConfirmMessage || "",
                            });
                        });
                    };
                }
                this._createDialog(id, html, options);
            }
            const data = _dialogs.get(id);
            // iOS breaks `position: fixed` when input elements or `contenteditable`
            // are focused, this will freeze the screen and force Safari to scroll
            // to the input field
            if (Environment.platform() === "ios") {
                window.setTimeout(() => {
                    var _a;
                    (_a = data.content.querySelector("input, textarea")) === null || _a === void 0 ? void 0 : _a.focus();
                }, 200);
            }
            return data;
        },
        /**
         * Sets the dialog title.
         */
        setTitle(id, title) {
            id = this._getDialogId(id);
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            const dialogTitle = data.dialog.querySelector(".dialogTitle");
            if (dialogTitle) {
                dialogTitle.textContent = title;
            }
        },
        /**
         * Sets a callback function on runtime.
         */
        setCallback(id, key, value) {
            if (typeof id === "object") {
                const dialogData = _dialogObjects.get(id);
                if (dialogData !== undefined) {
                    id = dialogData.id;
                }
            }
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            if (_validCallbacks.indexOf(key) === -1) {
                throw new Error("Invalid callback identifier, '" + key + "' is not recognized.");
            }
            if (typeof value !== "function" && value !== null) {
                throw new Error("Only functions or the 'null' value are acceptable callback values ('" + typeof value + "' given).");
            }
            data[key] = value;
        },
        /**
         * Creates the DOM for a new dialog and opens it.
         */
        _createDialog(id, html, options) {
            let element = null;
            if (html === null) {
                element = document.getElementById(id);
                if (element === null) {
                    throw new Error("Expected either a HTML string or an existing element id.");
                }
            }
            const dialog = document.createElement("div");
            dialog.classList.add("dialogContainer");
            dialog.setAttribute("aria-hidden", "true");
            dialog.setAttribute("role", "dialog");
            dialog.id = id;
            const header = document.createElement("header");
            dialog.appendChild(header);
            const titleId = Util_1.default.getUniqueId();
            dialog.setAttribute("aria-labelledby", titleId);
            const title = document.createElement("span");
            title.classList.add("dialogTitle");
            title.textContent = options.title;
            title.id = titleId;
            header.appendChild(title);
            if (options.closable) {
                const closeButton = document.createElement("a");
                closeButton.className = "dialogCloseButton jsTooltip";
                closeButton.href = "#";
                closeButton.setAttribute("role", "button");
                closeButton.tabIndex = 0;
                closeButton.title = options.closeButtonLabel;
                closeButton.setAttribute("aria-label", options.closeButtonLabel);
                closeButton.addEventListener("click", (ev) => this._close(ev));
                header.appendChild(closeButton);
                const span = document.createElement("span");
                span.className = "icon icon24 fa-times";
                closeButton.appendChild(span);
            }
            const contentContainer = document.createElement("div");
            contentContainer.classList.add("dialogContent");
            if (options.disableContentPadding)
                contentContainer.classList.add("dialogContentNoPadding");
            dialog.appendChild(contentContainer);
            contentContainer.addEventListener("wheel", (event) => {
                let allowScroll = false;
                let element = event.target;
                let clientHeight, scrollHeight, scrollTop;
                for (;;) {
                    clientHeight = element.clientHeight;
                    scrollHeight = element.scrollHeight;
                    if (clientHeight < scrollHeight) {
                        scrollTop = element.scrollTop;
                        // negative value: scrolling up
                        if (event.deltaY < 0 && scrollTop > 0) {
                            allowScroll = true;
                            break;
                        }
                        else if (event.deltaY > 0 && scrollTop + clientHeight < scrollHeight) {
                            allowScroll = true;
                            break;
                        }
                    }
                    if (!element || element === contentContainer) {
                        break;
                    }
                    element = element.parentNode;
                }
                if (!allowScroll) {
                    event.preventDefault();
                }
            }, { passive: false });
            let content;
            if (element === null) {
                if (typeof html === "string") {
                    content = document.createElement("div");
                    content.id = id;
                    Util_1.default.setInnerHtml(content, html);
                }
                else if (html instanceof DocumentFragment) {
                    const children = [];
                    let node;
                    for (let i = 0, length = html.childNodes.length; i < length; i++) {
                        node = html.childNodes[i];
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            children.push(node);
                        }
                    }
                    if (children[0].nodeName !== "DIV" || children.length > 1) {
                        content = document.createElement("div");
                        content.id = id;
                        content.appendChild(html);
                    }
                    else {
                        content = children[0];
                    }
                }
                else {
                    throw new TypeError("'html' must either be a string or a DocumentFragment");
                }
            }
            else {
                content = element;
            }
            contentContainer.appendChild(content);
            if (content.style.getPropertyValue("display") === "none") {
                Util_1.default.show(content);
            }
            _dialogs.set(id, {
                backdropCloseOnClick: options.backdropCloseOnClick,
                closable: options.closable,
                content: content,
                dialog: dialog,
                header: header,
                onBeforeClose: options.onBeforeClose,
                onClose: options.onClose,
                onShow: options.onShow,
                submitButton: null,
                inputFields: new Set(),
            });
            _container.insertBefore(dialog, _container.firstChild);
            if (typeof options.onSetup === "function") {
                options.onSetup(content);
            }
            this._updateDialog(id, null);
        },
        /**
         * Updates the dialog's content element.
         */
        _updateDialog(id, html) {
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            if (typeof html === "string") {
                Util_1.default.setInnerHtml(data.content, html);
            }
            if (Core.stringToBool(data.dialog.getAttribute("aria-hidden"))) {
                // close existing dropdowns
                Simple_1.default.closeAll();
                window.WCF.Dropdown.Interactive.Handler.closeAll();
                if (_callbackFocus === null) {
                    _callbackFocus = this._maintainFocus.bind(this);
                    document.body.addEventListener("focus", _callbackFocus, { capture: true });
                }
                if (data.closable && Core.stringToBool(_container.getAttribute("aria-hidden"))) {
                    window.addEventListener("keyup", _keyupListener);
                }
                // Move the dialog to the front to prevent it being hidden behind already open dialogs
                // if it was previously visible.
                data.dialog.parentNode.insertBefore(data.dialog, data.dialog.parentNode.firstChild);
                data.dialog.setAttribute("aria-hidden", "false");
                _container.setAttribute("aria-hidden", "false");
                _container.setAttribute("close-on-click", data.backdropCloseOnClick ? "true" : "false");
                _activeDialog = id;
                // Keep a reference to the currently focused element to be able to restore it later.
                _focusedBeforeDialog = document.activeElement;
                // Set the focus to the first focusable child of the dialog element.
                const closeButton = data.header.querySelector(".dialogCloseButton");
                if (closeButton)
                    closeButton.setAttribute("inert", "true");
                this._setFocusToFirstItem(data.dialog, false);
                if (closeButton)
                    closeButton.removeAttribute("inert");
                if (typeof data.onShow === "function") {
                    data.onShow(data.content);
                }
                if (Core.stringToBool(data.content.dataset.isStaticDialog || "")) {
                    EventHandler.fire("com.woltlab.wcf.dialog", "openStatic", {
                        content: data.content,
                        id: id,
                    });
                }
            }
            this.rebuild(id);
            Listener_1.default.trigger();
        },
        _maintainFocus(event) {
            if (_activeDialog) {
                const data = _dialogs.get(_activeDialog);
                const target = event.target;
                if (!data.dialog.contains(target) &&
                    !target.closest(".dropdownMenuContainer") &&
                    !target.closest(".datePicker")) {
                    this._setFocusToFirstItem(data.dialog, true);
                }
            }
        },
        _setFocusToFirstItem(dialog, maintain) {
            let focusElement = this._getFirstFocusableChild(dialog);
            if (focusElement !== null) {
                if (maintain) {
                    if (focusElement.id === "username" || focusElement.name === "username") {
                        if (Environment.browser() === "safari" && Environment.platform() === "ios") {
                            // iOS Safari's username/password autofill breaks if the input field is focused
                            focusElement = null;
                        }
                    }
                }
                if (focusElement) {
                    // Setting the focus to a select element in iOS is pretty strange, because
                    // it focuses it, but also displays the keyboard for a fraction of a second,
                    // causing it to pop out from below and immediately vanish.
                    //
                    // iOS will only show the keyboard if an input element is focused *and* the
                    // focus is an immediate result of a user interaction. This method must be
                    // assumed to be called from within a click event, but we want to set the
                    // focus without triggering the keyboard.
                    //
                    // We can break the condition by wrapping it in a setTimeout() call,
                    // effectively tricking iOS into focusing the element without showing the
                    // keyboard.
                    setTimeout(() => {
                        focusElement.focus();
                    }, 1);
                }
            }
        },
        _getFirstFocusableChild(element) {
            const nodeList = element.querySelectorAll(_focusableElements.join(","));
            for (let i = 0, length = nodeList.length; i < length; i++) {
                if (nodeList[i].offsetWidth && nodeList[i].offsetHeight && nodeList[i].getClientRects().length) {
                    return nodeList[i];
                }
            }
            return null;
        },
        /**
         * Rebuilds dialog identified by given id.
         */
        rebuild(id) {
            id = this._getDialogId(id);
            const data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            // ignore non-active dialogs
            if (Core.stringToBool(data.dialog.getAttribute("aria-hidden"))) {
                return;
            }
            const contentContainer = data.content.parentNode;
            const formSubmit = data.content.querySelector(".formSubmit");
            let unavailableHeight = 0;
            if (formSubmit !== null) {
                contentContainer.classList.add("dialogForm");
                formSubmit.classList.add("dialogFormSubmit");
                unavailableHeight += Util_1.default.outerHeight(formSubmit);
                // Calculated height can be a fractional value and depending on the
                // browser the results can vary. By subtracting a single pixel we're
                // working around fractional values, without visually changing anything.
                unavailableHeight -= 1;
                contentContainer.style.setProperty("margin-bottom", unavailableHeight + "px", "");
            }
            else {
                contentContainer.classList.remove("dialogForm");
                contentContainer.style.removeProperty("margin-bottom");
            }
            unavailableHeight += Util_1.default.outerHeight(data.header);
            const maximumHeight = window.innerHeight * (_dialogFullHeight ? 1 : 0.8) - unavailableHeight;
            contentContainer.style.setProperty("max-height", ~~maximumHeight + "px", "");
            // fix for a calculation bug in Chrome causing the scrollbar to overlap the border
            if (Environment.browser() === "chrome") {
                if (data.content.scrollHeight > maximumHeight) {
                    data.content.style.setProperty("margin-right", "-1px", "");
                }
                else {
                    data.content.style.removeProperty("margin-right");
                }
            }
            // Chrome and Safari use heavy anti-aliasing when the dialog's width
            // cannot be evenly divided, causing the whole text to become blurry
            if (Environment.browser() === "chrome" || Environment.browser() === "safari") {
                // The new Microsoft Edge is detected as "chrome", because effectively we're detecting
                // Chromium rather than Chrome specifically. The workaround for fractional pixels does
                // not work well in Edge, there seems to be a different logic for fractional positions,
                // causing the text to be blurry.
                //
                // We can use `backface-visibility: hidden` to prevent the anti aliasing artifacts in
                // WebKit/Blink, which will also prevent some weird font rendering issues when resizing.
                contentContainer.classList.add("jsWebKitFractionalPixelFix");
            }
            const callbackObject = _dialogToObject.get(id);
            //noinspection JSUnresolvedVariable
            if (callbackObject !== undefined && typeof callbackObject._dialogSubmit === "function") {
                const inputFields = data.content.querySelectorAll('input[data-dialog-submit-on-enter="true"]');
                const submitButton = data.content.querySelector('.formSubmit > input[type="submit"], .formSubmit > button[data-type="submit"]');
                if (submitButton === null) {
                    // check if there is at least one input field with submit handling,
                    // otherwise we'll assume the dialog has not been populated yet
                    if (inputFields.length === 0) {
                        console.warn("Broken dialog, expected a submit button.", data.content);
                    }
                    return;
                }
                if (data.submitButton !== submitButton) {
                    data.submitButton = submitButton;
                    submitButton.addEventListener("click", (event) => {
                        event.preventDefault();
                        this._submit(id);
                    });
                    const _callbackKeydown = (event) => {
                        if (event.key === "Enter") {
                            event.preventDefault();
                            this._submit(id);
                        }
                    };
                    // bind input fields
                    let inputField;
                    for (let i = 0, length = inputFields.length; i < length; i++) {
                        inputField = inputFields[i];
                        if (data.inputFields.has(inputField))
                            continue;
                        if (_validInputTypes.indexOf(inputField.type) === -1) {
                            console.warn("Unsupported input type.", inputField);
                            continue;
                        }
                        data.inputFields.add(inputField);
                        inputField.addEventListener("keydown", _callbackKeydown);
                    }
                }
            }
        },
        /**
         * Submits the dialog.
         */
        _submit(id) {
            const data = _dialogs.get(id);
            let isValid = true;
            data.inputFields.forEach((inputField) => {
                if (inputField.required) {
                    if (inputField.value.trim() === "") {
                        Util_1.default.innerError(inputField, Language.get("wcf.global.form.error.empty"));
                        isValid = false;
                    }
                    else {
                        Util_1.default.innerError(inputField, false);
                    }
                }
            });
            if (isValid) {
                const callbackObject = _dialogToObject.get(id);
                if (typeof callbackObject._dialogSubmit === "function") {
                    callbackObject._dialogSubmit();
                }
            }
        },
        /**
         * Handles clicks on the close button or the backdrop if enabled.
         */
        _close(event) {
            event.preventDefault();
            const data = _dialogs.get(_activeDialog);
            if (typeof data.onBeforeClose === "function") {
                data.onBeforeClose(_activeDialog);
                return false;
            }
            this.close(_activeDialog);
            return true;
        },
        /**
         * Closes the current active dialog by clicks on the backdrop.
         */
        _closeOnBackdrop(event) {
            if (event.target !== _container) {
                return;
            }
            if (Core.stringToBool(_container.getAttribute("close-on-click"))) {
                this._close(event);
            }
            else {
                event.preventDefault();
            }
        },
        /**
         * Closes a dialog identified by given id.
         */
        close(id) {
            id = this._getDialogId(id);
            let data = _dialogs.get(id);
            if (data === undefined) {
                throw new Error("Expected a valid dialog id, '" + id + "' does not match any active dialog.");
            }
            data.dialog.setAttribute("aria-hidden", "true");
            // Move the keyboard focus away from a now hidden element.
            const activeElement = document.activeElement;
            if (activeElement.closest(".dialogContainer") === data.dialog) {
                activeElement.blur();
            }
            if (typeof data.onClose === "function") {
                data.onClose(id);
            }
            // get next active dialog
            _activeDialog = null;
            for (let i = 0; i < _container.childElementCount; i++) {
                const child = _container.children[i];
                if (!Core.stringToBool(child.getAttribute("aria-hidden"))) {
                    _activeDialog = child.dataset.id || "";
                    break;
                }
            }
            UiScreen.pageOverlayClose();
            if (_activeDialog === null) {
                _container.setAttribute("aria-hidden", "true");
                _container.dataset.closeOnClick = "false";
                if (data.closable) {
                    window.removeEventListener("keyup", _keyupListener);
                }
            }
            else {
                data = _dialogs.get(_activeDialog);
                _container.dataset.closeOnClick = data.backdropCloseOnClick ? "true" : "false";
            }
            if (Environment.platform() !== "desktop") {
                UiScreen.scrollEnable();
            }
        },
        /**
         * Returns the dialog data for given element id.
         */
        getDialog(id) {
            return _dialogs.get(this._getDialogId(id));
        },
        /**
         * Returns true for open dialogs.
         */
        isOpen(id) {
            const data = this.getDialog(id);
            return data !== undefined && data.dialog.getAttribute("aria-hidden") === "false";
        },
        /**
         * Destroys a dialog instance.
         *
         * @param  {Object}  callbackObject  the same object that was used to invoke `_dialogSetup()` on first call
         */
        destroy(callbackObject) {
            if (typeof callbackObject !== "object") {
                throw new TypeError("Expected the callback object as parameter.");
            }
            if (_dialogObjects.has(callbackObject)) {
                const id = _dialogObjects.get(callbackObject).id;
                if (this.isOpen(id)) {
                    this.close(id);
                }
                // If the dialog is destroyed in the close callback, this method is
                // called twice resulting in `_dialogs.get(id)` being undefined for
                // the initial call.
                if (_dialogs.has(id)) {
                    _dialogs.get(id).dialog.remove();
                    _dialogs.delete(id);
                }
                _dialogObjects.delete(callbackObject);
            }
        },
        /**
         * Returns a dialog's id.
         *
         * @param  {(string|object)}  id  element id or callback object
         * @return      {string}
         * @protected
         */
        _getDialogId(id) {
            if (typeof id === "object") {
                const dialogData = _dialogObjects.get(id);
                if (dialogData !== undefined) {
                    return dialogData.id;
                }
            }
            return id.toString();
        },
        _ajaxSetup() {
            return {};
        },
    };
    return UiDialog;
});
