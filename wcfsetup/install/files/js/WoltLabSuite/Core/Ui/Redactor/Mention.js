define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../StringUtil", "../CloseOverlay"], function (require, exports, tslib_1, Ajax, Core, StringUtil, CloseOverlay_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    StringUtil = tslib_1.__importStar(StringUtil);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    let _dropdownContainer = null;
    class UiRedactorMention {
        constructor(redactor) {
            this._active = false;
            this._dropdownActive = false;
            this._dropdownMenu = null;
            this._itemIndex = 0;
            this._lineHeight = null;
            this._mentionStart = "";
            this._timer = null;
            this._redactor = redactor;
            redactor.WoltLabEvent.register("keydown", (data) => this._keyDown(data));
            redactor.WoltLabEvent.register("keyup", (data) => this._keyUp(data));
            CloseOverlay_1.default.add(`UiRedactorMention-${redactor.core.element()[0].id}`, () => this._hideDropdown());
        }
        _keyDown(data) {
            if (!this._dropdownActive) {
                return;
            }
            const event = data.event;
            switch (event.key) {
                case "Enter":
                    this._setUsername(null, this._dropdownMenu.children[this._itemIndex].children[0]);
                    break;
                case "ArrowUp":
                    this._selectItem(-1);
                    break;
                case "ArrowDown":
                    this._selectItem(1);
                    break;
                default:
                    this._hideDropdown();
                    return;
            }
            event.preventDefault();
            data.cancel = true;
        }
        _keyUp(data) {
            const event = data.event;
            // ignore return key
            if (event.key === "Enter") {
                this._active = false;
                return;
            }
            if (this._dropdownActive) {
                data.cancel = true;
                // ignore arrow up/down
                if (event.key === "ArrowDown" || event.key === "ArrowUp") {
                    return;
                }
            }
            const text = this._getTextLineInFrontOfCaret();
            if (text.length > 0 && text.length < 25) {
                const match = /@([^,]{3,})$/.exec(text);
                if (match) {
                    // if mentioning is at text begin or there's a whitespace character
                    // before the '@', everything is fine
                    if (!match.index || /\s/.test(text[match.index - 1])) {
                        this._mentionStart = match[1];
                        if (this._timer !== null) {
                            window.clearTimeout(this._timer);
                            this._timer = null;
                        }
                        this._timer = window.setTimeout(() => {
                            Ajax.api(this, {
                                parameters: {
                                    data: {
                                        searchString: this._mentionStart,
                                    },
                                },
                            });
                            this._timer = null;
                        }, 500);
                    }
                }
                else {
                    this._hideDropdown();
                }
            }
            else {
                this._hideDropdown();
            }
        }
        _getTextLineInFrontOfCaret() {
            const data = this._selectMention(false);
            if (data !== null) {
                return data.range
                    .cloneContents()
                    .textContent.replace(/\u200B/g, "")
                    .replace(/\u00A0/g, " ")
                    .trim();
            }
            return "";
        }
        _getDropdownMenuPosition() {
            const data = this._selectMention();
            if (data === null) {
                return null;
            }
            this._redactor.selection.save();
            data.selection.removeAllRanges();
            data.selection.addRange(data.range);
            // get the offsets of the bounding box of current text selection
            const rect = data.selection.getRangeAt(0).getBoundingClientRect();
            const offsets = {
                top: Math.round(rect.bottom) + (window.scrollY || window.pageYOffset),
                left: Math.round(rect.left) + document.body.scrollLeft,
            };
            if (this._lineHeight === null) {
                this._lineHeight = Math.round(rect.bottom - rect.top);
            }
            // restore caret position
            this._redactor.selection.restore();
            return offsets;
        }
        _setUsername(event, item) {
            if (event) {
                event.preventDefault();
                item = event.currentTarget;
            }
            const data = this._selectMention();
            if (data === null) {
                this._hideDropdown();
                return;
            }
            // allow redactor to undo this
            this._redactor.buffer.set();
            data.selection.removeAllRanges();
            data.selection.addRange(data.range);
            let range = window.getSelection().getRangeAt(0);
            range.deleteContents();
            range.collapse(true);
            // Mentions only allow for one whitespace per match, putting the username in apostrophes
            // will allow an arbitrary number of spaces.
            let username = item.dataset.username.trim();
            if (username.split(/\s/g).length > 2) {
                username = "'" + username.replace(/'/g, "''") + "'";
            }
            const text = document.createTextNode("@" + username + "\u00A0");
            range.insertNode(text);
            range = document.createRange();
            range.selectNode(text);
            range.collapse(false);
            data.selection.removeAllRanges();
            data.selection.addRange(range);
            this._hideDropdown();
        }
        _selectMention(skipCheck) {
            const selection = window.getSelection();
            if (!selection.rangeCount || !selection.isCollapsed) {
                return null;
            }
            let container = selection.anchorNode;
            if (container.nodeType === Node.TEXT_NODE) {
                // work-around for Firefox after suggestions have been presented
                container = container.parentElement;
            }
            // check if there is an '@' within the current range
            if (container.textContent.indexOf("@") === -1) {
                return null;
            }
            // check if we're inside code or quote blocks
            const editor = this._redactor.core.editor()[0];
            while (container && container !== editor) {
                if (["PRE", "WOLTLAB-QUOTE"].indexOf(container.nodeName) !== -1) {
                    return null;
                }
                container = container.parentElement;
            }
            let range = selection.getRangeAt(0);
            let endContainer = range.startContainer;
            let endOffset = range.startOffset;
            // find the appropriate end location
            while (endContainer.nodeType === Node.ELEMENT_NODE) {
                if (endOffset === 0 && endContainer.childNodes.length === 0) {
                    // invalid start location
                    return null;
                }
                // startOffset for elements will always be after a node index
                // or at the very start, which means if there is only text node
                // and the caret is after it, startOffset will equal `1`
                endContainer = endContainer.childNodes[endOffset ? endOffset - 1 : 0];
                if (endOffset > 0) {
                    if (endContainer.nodeType === Node.TEXT_NODE) {
                        endOffset = endContainer.textContent.length;
                    }
                    else {
                        endOffset = endContainer.childNodes.length;
                    }
                }
            }
            let startContainer = endContainer;
            let startOffset = -1;
            while (startContainer !== null) {
                if (startContainer.nodeType !== Node.TEXT_NODE) {
                    return null;
                }
                if (startContainer.textContent.indexOf("@") !== -1) {
                    startOffset = startContainer.textContent.lastIndexOf("@");
                    break;
                }
                startContainer = startContainer.previousSibling;
            }
            if (startOffset === -1) {
                // there was a non-text node that was in our way
                return null;
            }
            try {
                // mark the entire text, starting from the '@' to the current cursor position
                range = document.createRange();
                range.setStart(startContainer, startOffset);
                range.setEnd(endContainer, endOffset);
            }
            catch (e) {
                window.console.debug(e);
                return null;
            }
            if (skipCheck === false) {
                // check if the `@` occurs at the very start of the container
                // or at least has a whitespace in front of it
                let text = "";
                if (startOffset) {
                    text = startContainer.textContent.substr(0, startOffset);
                }
                while ((startContainer = startContainer.previousSibling)) {
                    if (startContainer.nodeType === Node.TEXT_NODE) {
                        text = startContainer.textContent + text;
                    }
                    else {
                        break;
                    }
                }
                if (/\S$/.test(text.replace(/\u200B/g, ""))) {
                    return null;
                }
            }
            else {
                // check if new range includes the mention text
                if (range
                    .cloneContents()
                    .textContent.replace(/\u200B/g, "")
                    .replace(/\u00A0/g, "")
                    .trim()
                    .replace(/^@/, "") !== this._mentionStart) {
                    // string mismatch
                    return null;
                }
            }
            return {
                range: range,
                selection: selection,
            };
        }
        _updateDropdownPosition() {
            const offset = this._getDropdownMenuPosition();
            if (offset === null) {
                this._hideDropdown();
                return;
            }
            offset.top += 7; // add a little vertical gap
            const dropdownMenu = this._dropdownMenu;
            dropdownMenu.style.setProperty("left", `${offset.left}px`, "");
            dropdownMenu.style.setProperty("top", `${offset.top}px`, "");
            this._selectItem(0);
            if (offset.top + dropdownMenu.offsetHeight + 10 > window.innerHeight + (window.scrollY || window.pageYOffset)) {
                const top = offset.top - dropdownMenu.offsetHeight - 2 * this._lineHeight + 7;
                dropdownMenu.style.setProperty("top", `${top}px`, "");
            }
        }
        _selectItem(step) {
            const dropdownMenu = this._dropdownMenu;
            // find currently active item
            const item = dropdownMenu.querySelector(".active");
            if (item !== null) {
                item.classList.remove("active");
            }
            this._itemIndex += step;
            if (this._itemIndex < 0) {
                this._itemIndex = dropdownMenu.childElementCount - 1;
            }
            else if (this._itemIndex >= dropdownMenu.childElementCount) {
                this._itemIndex = 0;
            }
            dropdownMenu.children[this._itemIndex].classList.add("active");
        }
        _hideDropdown() {
            if (this._dropdownMenu !== null) {
                this._dropdownMenu.classList.remove("dropdownOpen");
            }
            this._dropdownActive = false;
            this._itemIndex = 0;
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getSearchResultList",
                    className: "wcf\\data\\user\\UserAction",
                    interfaceName: "wcf\\data\\ISearchAction",
                    parameters: {
                        data: {
                            includeUserGroups: true,
                            scope: "mention",
                        },
                    },
                },
                silent: true,
            };
        }
        _ajaxSuccess(data) {
            if (!Array.isArray(data.returnValues) || !data.returnValues.length) {
                this._hideDropdown();
                return;
            }
            if (this._dropdownMenu === null) {
                this._dropdownMenu = document.createElement("ol");
                this._dropdownMenu.className = "dropdownMenu";
                if (_dropdownContainer === null) {
                    _dropdownContainer = document.createElement("div");
                    _dropdownContainer.className = "dropdownMenuContainer";
                    document.body.appendChild(_dropdownContainer);
                }
                _dropdownContainer.appendChild(this._dropdownMenu);
            }
            this._dropdownMenu.innerHTML = "";
            data.returnValues.forEach((item) => {
                const listItem = document.createElement("li");
                const link = document.createElement("a");
                link.addEventListener("mousedown", (ev) => this._setUsername(ev));
                link.className = "box16";
                link.innerHTML = `<span>${item.icon}</span> <span>${StringUtil.escapeHTML(item.label)}</span>`;
                link.dataset.userId = item.objectID.toString();
                link.dataset.username = item.label;
                listItem.appendChild(link);
                this._dropdownMenu.appendChild(listItem);
            });
            this._dropdownMenu.classList.add("dropdownOpen");
            this._dropdownActive = true;
            this._updateDropdownPosition();
        }
    }
    Core.enableLegacyInheritance(UiRedactorMention);
    return UiRedactorMention;
});
