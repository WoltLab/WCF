/**
 * Provides helper methods to add and remove format elements. These methods should in
 * theory work with non-editor elements but has not been tested and any usage outside
 * the editor is not recommended.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Format
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.removeFormat = exports.format = void 0;
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    function isValidSelection(editorElement) {
        let element = window.getSelection().anchorNode;
        while (element) {
            if (element === editorElement) {
                return true;
            }
            element = element.parentNode;
        }
        return false;
    }
    /**
     * Slices relevant parent nodes and removes matching ancestors.
     *
     * @param       {Element}       strikeElement           strike element representing the text selection
     * @param       {Element}       lastMatchingParent      last matching ancestor element
     * @param       {string}        property                CSS property that should be removed
     */
    function handleParentNodes(strikeElement, lastMatchingParent, property) {
        const parent = lastMatchingParent.parentElement;
        // selection does not begin at parent node start, slice all relevant parent
        // nodes to ensure that selection is then at the beginning while preserving
        // all proper ancestor elements
        //
        // before: (the pipe represents the node boundary)
        // |otherContent <-- selection -->
        // after:
        // |otherContent| |<-- selection -->
        if (!Util_1.default.isAtNodeStart(strikeElement, lastMatchingParent)) {
            const range = document.createRange();
            range.setStartBefore(lastMatchingParent);
            range.setEndBefore(strikeElement);
            const fragment = range.extractContents();
            parent.insertBefore(fragment, lastMatchingParent);
        }
        // selection does not end at parent node end, slice all relevant parent nodes
        // to ensure that selection is then at the end while preserving all proper
        // ancestor elements
        //
        // before: (the pipe represents the node boundary)
        // <-- selection --> otherContent|
        // after:
        // <-- selection -->| |otherContent|
        if (!Util_1.default.isAtNodeEnd(strikeElement, lastMatchingParent)) {
            const range = document.createRange();
            range.setStartAfter(strikeElement);
            range.setEndAfter(lastMatchingParent);
            const fragment = range.extractContents();
            parent.insertBefore(fragment, lastMatchingParent.nextSibling);
        }
        // the strike element is now some kind of isolated, meaning we can now safely
        // remove all offending parent nodes without influencing formatting of any content
        // before or after the element
        lastMatchingParent.querySelectorAll("span").forEach((span) => {
            if (span.style.getPropertyValue(property)) {
                Util_1.default.unwrapChildNodes(span);
            }
        });
        // finally remove the parent itself
        Util_1.default.unwrapChildNodes(lastMatchingParent);
    }
    /**
     * Finds the last matching ancestor until it reaches the editor element.
     */
    function getLastMatchingParent(strikeElement, editorElement, property) {
        let parent = strikeElement.parentElement;
        let match = null;
        while (parent !== editorElement) {
            if (parent.nodeName === "SPAN" && parent.style.getPropertyValue(property) !== "") {
                match = parent;
            }
            parent = parent.parentElement;
        }
        return match;
    }
    /**
     * Returns true if provided element is the first or last element
     * of its parent, ignoring empty text nodes appearing between the
     * element and the boundary.
     */
    function isBoundaryElement(element, parent, type) {
        let node = element;
        while ((node = node[type])) {
            if (node.nodeType !== Node.TEXT_NODE || node.textContent.replace(/\u200B/, "") !== "") {
                return false;
            }
        }
        return true;
    }
    /**
     * Returns a custom selection marker element, can be either `strike`, `sub` or `sup`. Using other kind
     * of formattings is not possible due to the inconsistent behavior across browsers.
     */
    function getSelectionMarker(editorElement, selection) {
        const tags = ["DEL", "SUB", "SUP"];
        const tag = tags.find((tagName) => {
            const anchorNode = selection.anchorNode;
            let node = anchorNode.nodeType === Node.ELEMENT_NODE ? anchorNode : anchorNode.parentElement;
            const hasNode = node.querySelector(tagName.toLowerCase()) !== null;
            if (!hasNode) {
                while (node && node !== editorElement) {
                    if (node.nodeName === tagName) {
                        return true;
                    }
                    node = node.parentElement;
                }
            }
            return false;
        });
        if (tag === "DEL" || tag === undefined) {
            return ["strike", "strikethrough"];
        }
        return [tag.toLowerCase(), tag.toLowerCase() + "script"];
    }
    /**
     * Slightly modified version of Redactor's `utils.isEmpty()`.
     */
    function isEmpty(html) {
        html = html.replace(/[\u200B-\u200D\uFEFF]/g, "");
        html = html.replace(/&nbsp;/gi, "");
        html = html.replace(/<\/?br\s?\/?>/g, "");
        html = html.replace(/\s/g, "");
        html = html.replace(/^<p>[^\W\w\D\d]*?<\/p>$/i, "");
        html = html.replace(/<iframe(.*?[^>])>$/i, "iframe");
        html = html.replace(/<source(.*?[^>])>$/i, "source");
        // remove empty tags
        html = html.replace(/<[^/>][^>]*><\/[^>]+>/gi, "");
        html = html.replace(/<[^/>][^>]*><\/[^>]+>/gi, "");
        return html.trim() === "";
    }
    /**
     * Applies format elements to the selected text.
     */
    function format(editorElement, property, value) {
        const selection = window.getSelection();
        if (!selection.rangeCount) {
            // no active selection
            return;
        }
        if (!isValidSelection(editorElement)) {
            console.error("Invalid selection, range exists outside of the editor:", selection.anchorNode);
            return;
        }
        let range = selection.getRangeAt(0);
        let markerStart = null;
        let markerEnd = null;
        let tmpElement = null;
        if (range.collapsed) {
            tmpElement = document.createElement("strike");
            tmpElement.textContent = "\u200B";
            range.insertNode(tmpElement);
            range = document.createRange();
            range.selectNodeContents(tmpElement);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        else {
            // removing existing format causes the selection to vanish,
            // these markers are used to restore it afterwards
            markerStart = document.createElement("mark");
            markerEnd = document.createElement("mark");
            let tmpRange = range.cloneRange();
            tmpRange.collapse(true);
            tmpRange.insertNode(markerStart);
            tmpRange = range.cloneRange();
            tmpRange.collapse(false);
            tmpRange.insertNode(markerEnd);
            range = document.createRange();
            range.setStartAfter(markerStart);
            range.setEndBefore(markerEnd);
            selection.removeAllRanges();
            selection.addRange(range);
            // remove existing format before applying new one
            removeFormat(editorElement, property);
            range = document.createRange();
            range.setStartAfter(markerStart);
            range.setEndBefore(markerEnd);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        let selectionMarker = ["strike", "strikethrough"];
        if (tmpElement === null) {
            selectionMarker = getSelectionMarker(editorElement, selection);
            document.execCommand(selectionMarker[1]);
        }
        const selectElements = [];
        editorElement.querySelectorAll(selectionMarker[0]).forEach((strike) => {
            const formatElement = document.createElement("span");
            // we're bypassing `style.setPropertyValue()` on purpose here,
            // as it prevents browsers from mangling the value
            formatElement.setAttribute("style", `${property}: ${value}`);
            Util_1.default.replaceElement(strike, formatElement);
            selectElements.push(formatElement);
        });
        const count = selectElements.length;
        if (count) {
            const firstSelectedElement = selectElements[0];
            const lastSelectedElement = selectElements[count - 1];
            // check if parent is of the same format
            // and contains only the selected nodes
            if (tmpElement === null && firstSelectedElement.parentElement === lastSelectedElement.parentElement) {
                const parent = firstSelectedElement.parentElement;
                if (parent.nodeName === "SPAN" && parent.style.getPropertyValue(property) !== "") {
                    if (isBoundaryElement(firstSelectedElement, parent, "previousSibling") &&
                        isBoundaryElement(lastSelectedElement, parent, "nextSibling")) {
                        Util_1.default.unwrapChildNodes(parent);
                    }
                }
            }
            range = document.createRange();
            range.setStart(firstSelectedElement, 0);
            range.setEnd(lastSelectedElement, lastSelectedElement.childNodes.length);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        if (markerStart !== null) {
            markerStart.remove();
            markerEnd.remove();
        }
    }
    exports.format = format;
    /**
     * Removes a format element from the current selection.
     *
     * The removal uses a few techniques to remove the target element(s) without harming
     * nesting nor any other formatting present. The steps taken are described below:
     *
     * 1. The browser will wrap all parts of the selection into <strike> tags
     *
     *      This isn't the most efficient way to isolate each selected node, but is the
     *      most reliable way to accomplish this because the browser will insert them
     *      exactly where the range spans without harming the node nesting.
     *
     *      Basically it is a trade-off between efficiency and reliability, the performance
     *      is still excellent but could be better at the expense of an increased complexity,
     *      which simply doesn't exactly pay off.
     *
     * 2. Iterate over each inserted <strike> and isolate all relevant ancestors
     *
     *      Format tags can appear both as a child of the <strike> as well as once or multiple
     *      times as an ancestor.
     *
     *      It uses ranges to select the contents before the <strike> element up to the start
     *      of the last matching ancestor and cuts out the nodes. The browser will ensure that
     *      the resulting fragment will include all relevant ancestors that were present before.
     *
     *      The example below will use the fictional <bar> elements as the tag to remove, the
     *      pipe ("|") is used to denote the outer node boundaries.
     *
     *      Before:
     *      |<bar>This is <foo>a <strike>simple <bar>example</bar></strike></foo></bar>|
     *      After:
     *      |<bar>This is <foo>a </foo></bar>|<bar><foo>simple <bar>example</bar></strike></foo></bar>|
     *
     *      As a result we can now remove <bar> both inside the <strike> element as well as
     *      the outer <bar> without harming the effect of <bar> for the preceding siblings.
     *
     *      This process is repeated for siblings appearing after the <strike> element too, it
     *      works as described above but flipped. This is an expensive operation and will only
     *      take place if there are any matching ancestors that need to be considered.
     *
     *      Inspired by http://stackoverflow.com/a/12899461
     *
     * 3. Remove all matching ancestors, child elements and last the <strike> element itself
     *
     *      Depending on the amount of nested matching nodes, this process will move a lot of
     *      nodes around. Removing the <bar> element will require all its child nodes to be moved
     *      in front of <bar>, they will actually become a sibling of <bar>. Afterwards the
     *      (now empty) <bar> element can be safely removed without losing any nodes.
     *
     *
     * One last hint: This method will not check if the selection at some point contains at
     * least one target element, it assumes that the user will not take any action that invokes
     * this method for no reason (unless they want to waste CPU cycles, in that case they're
     * welcome).
     *
     * This is especially important for developers as this method shouldn't be called for
     * no good reason. Even though it is super fast, it still comes with expensive DOM operations
     * and especially low-end devices (such as cheap smartphones) might not exactly like executing
     * this method on large documents.
     *
     * If you fell the need to invoke this method anyway, go ahead. I'm a comment, not a cop.
     */
    function removeFormat(editorElement, property) {
        const selection = window.getSelection();
        if (!selection.rangeCount) {
            return;
        }
        else if (!isValidSelection(editorElement)) {
            console.error("Invalid selection, range exists outside of the editor:", selection.anchorNode);
            return;
        }
        // Removing a span from an empty selection in an empty line containing a `<br>` causes a selection
        // shift where the caret is moved into the span again. Unlike inline changes to the formatting, any
        // removal of the format in an empty line should remove it from its entirely, instead of just around
        // the caret position.
        let range = selection.getRangeAt(0);
        let helperTextNode = null;
        const rangeIsCollapsed = range.collapsed;
        if (rangeIsCollapsed) {
            let container = range.startContainer;
            const tree = [container];
            for (;;) {
                const parent = container.parentElement;
                if (parent === editorElement || parent.nodeName === "TD") {
                    break;
                }
                container = parent;
                tree.push(container);
            }
            if (isEmpty(container.innerHTML)) {
                const marker = document.createElement("woltlab-format-marker");
                range.insertNode(marker);
                // Find the offending span and remove it entirely.
                tree.forEach((element) => {
                    if (element.nodeName === "SPAN") {
                        if (element.style.getPropertyValue(property)) {
                            Util_1.default.unwrapChildNodes(element);
                        }
                    }
                });
                // Firefox messes up the selection if the ancestor element was removed and there is
                // an adjacent `<br>` present. Instead of keeping the caret in front of the <br>, it
                // is implicitly moved behind it.
                range = document.createRange();
                range.selectNode(marker);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                marker.remove();
                return;
            }
            // Fill up the range with a zero length whitespace to give the browser
            // something to strike through. If the range is completely empty, the
            // "strike" is remembered by the browser, but not actually inserted into
            // the DOM, causing the next keystroke to magically insert it.
            helperTextNode = document.createTextNode("\u200B");
            range.insertNode(helperTextNode);
        }
        let strikeElements = editorElement.querySelectorAll("strike");
        // remove any <strike> element first, all though there shouldn't be any at all
        strikeElements.forEach((el) => Util_1.default.unwrapChildNodes(el));
        const selectionMarker = getSelectionMarker(editorElement, selection);
        document.execCommand(selectionMarker[1]);
        strikeElements = editorElement.querySelectorAll(selectionMarker[0]);
        // Safari 13 sometimes refuses to execute the `strikeThrough` command.
        if (rangeIsCollapsed && helperTextNode !== null && strikeElements.length === 0) {
            // Executing the command again will toggle off the previous command that had no
            // effect anyway, effectively cancelling out the previous call. Only works if the
            // first call had no effect, otherwise it will enable it.
            document.execCommand(selectionMarker[1]);
            const tmp = document.createElement(selectionMarker[0]);
            helperTextNode.parentElement.insertBefore(tmp, helperTextNode);
            tmp.appendChild(helperTextNode);
        }
        strikeElements.forEach((strikeElement) => {
            const lastMatchingParent = getLastMatchingParent(strikeElement, editorElement, property);
            if (lastMatchingParent !== null) {
                handleParentNodes(strikeElement, lastMatchingParent, property);
            }
            // remove offending elements from child nodes
            strikeElement.querySelectorAll("span").forEach((span) => {
                if (span.style.getPropertyValue(property)) {
                    Util_1.default.unwrapChildNodes(span);
                }
            });
            // remove strike element itself
            Util_1.default.unwrapChildNodes(strikeElement);
        });
        // search for tags that are still floating around, but are completely empty
        editorElement.querySelectorAll("span").forEach((element) => {
            if (element.parentNode && !element.textContent.length && element.style.getPropertyValue(property) !== "") {
                if (element.childElementCount === 1 && element.children[0].nodeName === "MARK") {
                    element.parentNode.insertBefore(element.children[0], element);
                }
                if (element.childElementCount === 0) {
                    element.remove();
                }
            }
        });
    }
    exports.removeFormat = removeFormat;
});
