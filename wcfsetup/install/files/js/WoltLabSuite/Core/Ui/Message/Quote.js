define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Event/Handler", "../../Language", "../../Dom/Change/Listener", "../../Dom/Util"], function (require, exports, tslib_1, Ajax, Core, EventHandler, Language, Listener_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    Language = tslib_1.__importStar(Language);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class UiMessageQuote {
        /**
         * Initializes the quote handler for given object type.
         */
        constructor(quoteManager, // TODO
        className, objectType, containerSelector, messageBodySelector, messageContentSelector, supportDirectInsert) {
            this.activeMessageId = "";
            this.containers = new Map();
            this.containerSelector = "";
            this.copyQuote = document.createElement("div");
            this.message = "";
            this.objectId = 0;
            this.objectType = "";
            this.timerSelectionChange = undefined;
            this.isMouseDown = false;
            this.className = className;
            this.objectType = objectType;
            this.containerSelector = containerSelector;
            this.messageBodySelector = messageBodySelector;
            this.initContainers();
            supportDirectInsert = supportDirectInsert && quoteManager.supportPaste();
            this.quoteManager = quoteManager;
            this.initCopyQuote(supportDirectInsert);
            document.addEventListener("mouseup", (event) => this.onMouseUp(event));
            document.addEventListener("selectionchange", () => this.onSelectionchange());
            Listener_1.default.add("UiMessageQuote", () => this.initContainers());
            // Prevent the tooltip from being selectable while the touch pointer is being moved.
            document.addEventListener("touchstart", (event) => {
                if (this.copyQuote.classList.contains("active")) {
                    const target = event.target;
                    if (target !== this.copyQuote && !this.copyQuote.contains(target)) {
                        this.copyQuote.classList.add("touchForceInaccessible");
                        document.addEventListener("touchend", () => {
                            this.copyQuote.classList.remove("touchForceInaccessible");
                        }, { once: true });
                    }
                }
            }, { passive: true });
        }
        /**
         * Initializes message containers.
         */
        initContainers() {
            document.querySelectorAll(this.containerSelector).forEach((container) => {
                var _a;
                const id = Util_1.default.identify(container);
                if (this.containers.has(id)) {
                    return;
                }
                this.containers.set(id, container);
                if (container.classList.contains("jsInvalidQuoteTarget")) {
                    return;
                }
                container.addEventListener("mousedown", (event) => this.onMouseDown(event));
                container.classList.add("jsQuoteMessageContainer");
                (_a = container
                    .querySelector(".jsQuoteMessage")) === null || _a === void 0 ? void 0 : _a.addEventListener("click", (event) => this.saveFullQuote(event));
            });
        }
        onSelectionchange() {
            if (this.isMouseDown) {
                return;
            }
            if (this.activeMessageId === "") {
                // check if the selection is non-empty and is entirely contained
                // inside a single message container that is registered for quoting
                const selection = window.getSelection();
                if (selection.rangeCount !== 1 || selection.isCollapsed) {
                    return;
                }
                const range = selection.getRangeAt(0);
                const startContainer = Util_1.default.closest(range.startContainer, ".jsQuoteMessageContainer");
                const endContainer = Util_1.default.closest(range.endContainer, ".jsQuoteMessageContainer");
                if (startContainer &&
                    startContainer === endContainer &&
                    !startContainer.classList.contains("jsInvalidQuoteTarget")) {
                    // Check if the selection is visible, such as text marked inside containers with an
                    // active overflow handling attached to it. This can be a side effect of the browser
                    // search which modifies the text selection, but cannot be distinguished from manual
                    // selections initiated by the user.
                    let commonAncestor = range.commonAncestorContainer;
                    if (commonAncestor.nodeType !== Node.ELEMENT_NODE) {
                        commonAncestor = commonAncestor.parentElement;
                    }
                    const offsetParent = commonAncestor.offsetParent;
                    if (startContainer.contains(offsetParent)) {
                        if (offsetParent.scrollTop + offsetParent.clientHeight < commonAncestor.offsetTop) {
                            // The selected text is not visible to the user.
                            return;
                        }
                    }
                    this.activeMessageId = startContainer.id;
                }
            }
            if (this.timerSelectionChange) {
                window.clearTimeout(this.timerSelectionChange);
            }
            this.timerSelectionChange = window.setTimeout(() => this.onMouseUp(), 100);
        }
        onMouseDown(event) {
            // hide copy quote
            this.copyQuote.classList.remove("active");
            const message = event.currentTarget;
            this.activeMessageId = message.classList.contains("jsInvalidQuoteTarget") ? "" : message.id;
            if (this.timerSelectionChange) {
                window.clearTimeout(this.timerSelectionChange);
                this.timerSelectionChange = undefined;
            }
            this.isMouseDown = true;
        }
        /**
         * Returns the text of a node and its children.
         */
        getNodeText(node) {
            const treeWalker = document.createTreeWalker(node, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, {
                acceptNode(node) {
                    if (node.nodeName === "BLOCKQUOTE" || node.nodeName === "SCRIPT") {
                        return NodeFilter.FILTER_REJECT;
                    }
                    if (node instanceof HTMLImageElement) {
                        // Skip any image that is not a smiley or contains no alt text.
                        if (!node.classList.contains("smiley") || !node.alt) {
                            return NodeFilter.FILTER_REJECT;
                        }
                    }
                    return NodeFilter.FILTER_ACCEPT;
                },
            });
            let text = "";
            const ignoreLinks = [];
            while (treeWalker.nextNode()) {
                const node = treeWalker.currentNode;
                if (node instanceof Text) {
                    const parent = node.parentElement;
                    if (parent instanceof HTMLAnchorElement && ignoreLinks.includes(parent)) {
                        // ignore text content of links that have already been captured
                        continue;
                    }
                    // Firefox loves to arbitrarily wrap pasted text at weird line lengths, causing
                    // pointless linebreaks to be inserted. Replacing them with a simple space will
                    // preserve the spacing between words that would otherwise be lost.
                    text += node.nodeValue.replace(/\n/g, " ");
                    continue;
                }
                if (node instanceof HTMLAnchorElement) {
                    // \u2026 === &hellip;
                    const value = node.textContent;
                    if (value.indexOf("\u2026") > 0) {
                        const tmp = value.split(/\u2026/);
                        if (tmp.length === 2) {
                            const href = node.href;
                            if (href.indexOf(tmp[0]) === 0 && href.substr(tmp[1].length * -1) === tmp[1]) {
                                // This is a truncated url, use the original href instead to preserve the link.
                                text += href;
                                ignoreLinks.push(node);
                            }
                        }
                    }
                }
                switch (node.nodeName) {
                    case "BR":
                    case "LI":
                    case "TD":
                    case "UL":
                        text += "\n";
                        break;
                    case "P":
                        text += "\n\n";
                        break;
                    // smilies
                    case "IMG": {
                        const img = node;
                        text += ` ${img.alt} `;
                        break;
                    }
                    // Code listing
                    case "DIV":
                        if (node.classList.contains("codeBoxHeadline") || node.classList.contains("codeBoxLine")) {
                            text += "\n";
                        }
                        break;
                }
            }
            return text;
        }
        onMouseUp(event) {
            if (event instanceof Event) {
                if (this.timerSelectionChange) {
                    // Prevent collisions of the `selectionchange` and the `mouseup` event.
                    window.clearTimeout(this.timerSelectionChange);
                    this.timerSelectionChange = undefined;
                }
                this.isMouseDown = false;
            }
            // ignore event
            if (this.activeMessageId === "") {
                this.copyQuote.classList.remove("active");
                return;
            }
            const selection = window.getSelection();
            if (selection.rangeCount !== 1 || selection.isCollapsed) {
                this.copyQuote.classList.remove("active");
                return;
            }
            const container = this.containers.get(this.activeMessageId);
            const objectId = ~~container.dataset.objectId;
            const content = this.messageBodySelector
                ? container.querySelector(this.messageBodySelector)
                : container;
            let anchorNode = selection.anchorNode;
            while (anchorNode) {
                if (anchorNode === content) {
                    break;
                }
                anchorNode = anchorNode.parentNode;
            }
            // selection spans unrelated nodes
            if (anchorNode !== content) {
                this.copyQuote.classList.remove("active");
                return;
            }
            const selectedText = this.getSelectedText();
            const text = selectedText.trim();
            if (text === "") {
                this.copyQuote.classList.remove("active");
                return;
            }
            // check if mousedown/mouseup took place inside a blockquote
            const range = selection.getRangeAt(0);
            const startContainer = Util_1.default.getClosestElement(range.startContainer);
            const endContainer = Util_1.default.getClosestElement(range.endContainer);
            if (startContainer.closest("blockquote") || endContainer.closest("blockquote")) {
                this.copyQuote.classList.remove("active");
                return;
            }
            // compare selection with message text of given container
            const messageText = this.getNodeText(content);
            // selected text is not part of $messageText or contains text from unrelated nodes
            if (!this.normalizeTextForComparison(messageText).includes(this.normalizeTextForComparison(text))) {
                return;
            }
            this.copyQuote.classList.add("active");
            const coordinates = this.getElementBoundaries(selection);
            const dimensions = { height: this.copyQuote.offsetHeight, width: this.copyQuote.offsetWidth };
            let left = (coordinates.right - coordinates.left) / 2 - dimensions.width / 2 + coordinates.left;
            // Prevent the overlay from overflowing the left or right boundary of the container.
            const containerBoundaries = content.getBoundingClientRect();
            if (left < containerBoundaries.left) {
                left = containerBoundaries.left;
            }
            else if (left + dimensions.width > containerBoundaries.right) {
                left = containerBoundaries.right - dimensions.width;
            }
            this.copyQuote.style.setProperty("top", `${coordinates.bottom + 7}px`);
            this.copyQuote.style.setProperty("left", `${left}px`);
            this.copyQuote.classList.remove("active");
            if (!this.timerSelectionChange) {
                // reset containerID
                this.activeMessageId = "";
            }
            else {
                window.clearTimeout(this.timerSelectionChange);
                this.timerSelectionChange = undefined;
            }
            // show element after a delay, to prevent display if text was unmarked again (clicking into marked text)
            window.setTimeout(() => {
                const text = this.getSelectedText().trim();
                if (text !== "") {
                    this.copyQuote.classList.add("active");
                    this.message = text;
                    this.objectId = objectId;
                }
            }, 10);
        }
        normalizeTextForComparison(text) {
            return text
                .replace(/\r?\n|\r/g, "\n")
                .replace(/\s/g, " ")
                .replace(/\s{2,}/g, " ");
        }
        getElementBoundaries(selection) {
            let coordinates = null;
            if (selection.rangeCount > 0) {
                // The coordinates returned by getBoundingClientRect() are relative to the
                // viewport, not the document.
                const rect = selection.getRangeAt(0).getBoundingClientRect();
                const scrollTop = window.pageYOffset;
                coordinates = {
                    bottom: rect.bottom + scrollTop,
                    left: rect.left,
                    right: rect.right,
                    top: rect.top + scrollTop,
                };
            }
            return coordinates;
        }
        initCopyQuote(supportDirectInsert) {
            const copyQuote = document.getElementById("quoteManagerCopy");
            copyQuote === null || copyQuote === void 0 ? void 0 : copyQuote.remove();
            this.copyQuote.id = "quoteManagerCopy";
            this.copyQuote.classList.add("balloonTooltip", "interactive");
            const buttonSaveQuote = document.createElement("span");
            buttonSaveQuote.classList.add("jsQuoteManagerStore");
            buttonSaveQuote.textContent = Language.get("wcf.message.quote.quoteSelected");
            buttonSaveQuote.addEventListener("click", (event) => this.saveQuote(event));
            this.copyQuote.appendChild(buttonSaveQuote);
            if (supportDirectInsert) {
                const buttonSaveAndInsertQuote = document.createElement("span");
                buttonSaveAndInsertQuote.classList.add("jsQuoteManagerQuoteAndInsert");
                buttonSaveAndInsertQuote.textContent = Language.get("wcf.message.quote.quoteAndReply");
                buttonSaveAndInsertQuote.addEventListener("click", (event) => this.saveAndInsertQuote(event));
                this.copyQuote.appendChild(buttonSaveAndInsertQuote);
            }
            document.body.appendChild(this.copyQuote);
        }
        getSelectedText() {
            const selection = window.getSelection();
            if (selection.rangeCount) {
                return this.getNodeText(selection.getRangeAt(0).cloneContents());
            }
            return "";
        }
        saveFullQuote(event) {
            event.preventDefault();
            const listItem = event.currentTarget;
            Ajax.api(this, {
                actionName: "saveFullQuote",
                objectIDs: [listItem.dataset.objectId],
            });
            // mark element as quoted
            const quoteLink = listItem.querySelector("a");
            if (Core.stringToBool(listItem.dataset.isQuoted || "")) {
                listItem.dataset.isQuoted = "false";
                quoteLink.classList.remove("active");
            }
            else {
                listItem.dataset.isQuoted = "true";
                quoteLink.classList.add("active");
            }
            // close navigation on mobile
            const navigationList = listItem.closest(".buttonGroupNavigation");
            if (navigationList.classList.contains("jsMobileButtonGroupNavigation")) {
                const dropDownLabel = navigationList.querySelector(".dropdownLabel");
                dropDownLabel.click();
            }
        }
        saveQuote(event, renderQuote = false) {
            event === null || event === void 0 ? void 0 : event.preventDefault();
            Ajax.api(this, {
                actionName: "saveQuote",
                objectIDs: [this.objectId],
                parameters: {
                    message: this.message,
                    renderQuote,
                },
            });
            const selection = window.getSelection();
            if (selection.rangeCount) {
                selection.removeAllRanges();
                this.copyQuote.classList.remove("active");
            }
        }
        saveAndInsertQuote(event) {
            event.preventDefault();
            this.saveQuote(undefined, true);
        }
        _ajaxSuccess(data) {
            if (data.returnValues.count !== undefined) {
                if (data.returnValues.fullQuoteMessageIDs !== undefined) {
                    data.returnValues.fullQuoteObjectIDs = data.returnValues.fullQuoteMessageIDs;
                }
                const fullQuoteObjectIDs = data.returnValues.fullQuoteObjectIDs || {};
                this.quoteManager.updateCount(data.returnValues.count, fullQuoteObjectIDs);
            }
            switch (data.actionName) {
                case "saveQuote":
                case "saveFullQuote":
                    if (data.returnValues.renderedQuote) {
                        EventHandler.fire("com.woltlab.wcf.message.quote", "insert", {
                            forceInsert: data.actionName === "saveQuote",
                            quote: data.returnValues.renderedQuote,
                        });
                    }
                    break;
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    className: this.className,
                    interfaceName: "wcf\\data\\IMessageQuoteAction",
                },
            };
        }
        /**
         * Updates the full quote data for all matching objects.
         */
        updateFullQuoteObjectIDs(objectIds) {
            this.containers.forEach((message) => {
                const quoteButton = message.querySelector(".jsQuoteMessage");
                quoteButton.dataset.isQuoted = "false";
                const quoteButtonLink = quoteButton.querySelector("a");
                quoteButton.classList.remove("active");
                const objectId = ~~quoteButton.dataset.objectID;
                if (objectIds.includes(objectId)) {
                    quoteButton.dataset.isQuoted = "true";
                    quoteButtonLink.classList.add("active");
                }
            });
        }
    }
    exports.default = UiMessageQuote;
});
