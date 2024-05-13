define(["require", "exports", "diff-match-patch"], function (require, exports, diff_match_patch_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isTableValid = exports.markUpNode = exports.diffText = exports.cleanUpNodeMarkers = exports.charForNodeName = exports.hashCode = exports.never = exports.getAncestors = exports.areNodesEqual = exports.areArraysEqual = exports.strictEqual = exports.isComment = exports.isDocumentFragment = exports.isDocument = exports.isText = exports.isElement = void 0;
    function isElement(node) {
        return node.nodeType === node.ELEMENT_NODE;
    }
    exports.isElement = isElement;
    function isText(node) {
        return node.nodeType === node.TEXT_NODE;
    }
    exports.isText = isText;
    function isDocument(node) {
        return node.nodeType === node.DOCUMENT_NODE;
    }
    exports.isDocument = isDocument;
    function isDocumentFragment(node) {
        return node.nodeType === node.DOCUMENT_FRAGMENT_NODE;
    }
    exports.isDocumentFragment = isDocumentFragment;
    function isComment(node) {
        return node.nodeType === node.COMMENT_NODE;
    }
    exports.isComment = isComment;
    function strictEqual(item1, item2) {
        return item1 === item2;
    }
    exports.strictEqual = strictEqual;
    function areArraysEqual(array1, array2, comparator = strictEqual) {
        if (array1.length !== array2.length) {
            return false;
        }
        for (let i = 0, l = array1.length; i < l; ++i) {
            if (!comparator(array1[i], array2[i])) {
                return false;
            }
        }
        return true;
    }
    exports.areArraysEqual = areArraysEqual;
    function getAttributeNames(element) {
        if (element.getAttributeNames) {
            return element.getAttributeNames();
        }
        else {
            const attributes = element.attributes;
            const length = attributes.length;
            const attributeNames = new Array(length);
            for (let i = 0; i < length; i++) {
                attributeNames[i] = attributes[i].name;
            }
            return attributeNames;
        }
    }
    /**
     * Compares DOM nodes for equality.
     * @param node1 The first node to compare.
     * @param node2 The second node to compare.
     * @param deep If true, the child nodes are compared recursively too.
     * @returns `true`, if the 2 nodes are equal, otherwise `false`.
     */
    function areNodesEqual(node1, node2, deep = false) {
        if (node1 === node2) {
            return true;
        }
        if (node1.nodeType !== node2.nodeType ||
            node1.nodeName !== node2.nodeName) {
            return false;
        }
        if (isText(node1) || isComment(node1)) {
            if (node1.data !== node2.data) {
                return false;
            }
        }
        else if (isElement(node1)) {
            const attributeNames1 = getAttributeNames(node1).sort();
            const attributeNames2 = getAttributeNames(node2).sort();
            if (!areArraysEqual(attributeNames1, attributeNames2)) {
                return false;
            }
            for (let i = 0, l = attributeNames1.length; i < l; ++i) {
                const name = attributeNames1[i];
                const value1 = node1.getAttribute(name);
                const value2 = node2.getAttribute(name);
                if (value1 !== value2) {
                    return false;
                }
            }
        }
        if (deep) {
            const childNodes1 = node1.childNodes;
            const childNodes2 = node2.childNodes;
            if (childNodes1.length !== childNodes2.length) {
                return false;
            }
            for (let i = 0, l = childNodes1.length; i < l; ++i) {
                if (!areNodesEqual(childNodes1[i], childNodes2[i], deep)) {
                    return false;
                }
            }
        }
        return true;
    }
    exports.areNodesEqual = areNodesEqual;
    /**
     * Gets a list of `node`'s ancestor nodes up until and including `rootNode`.
     * @param node Node whose ancestors to get.
     * @param rootNode The root node.
     */
    function getAncestors(node, rootNode = null) {
        if (!node || node === rootNode) {
            return [];
        }
        const ancestors = [];
        let currentNode = node.parentNode;
        while (currentNode) {
            ancestors.push(currentNode);
            if (currentNode === rootNode) {
                break;
            }
            currentNode = currentNode.parentNode;
        }
        return ancestors;
    }
    exports.getAncestors = getAncestors;
    function never(message = 'visual-dom-diff: Should never happen') {
        throw new Error(message);
    }
    exports.never = never;
    // Source: https://stackoverflow.com/a/7616484/706807 (simplified)
    function hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            // tslint:disable-next-line:no-bitwise
            hash = ((hash << 5) - hash + str.charCodeAt(i)) | 0;
        }
        return hash;
    }
    exports.hashCode = hashCode;
    /**
     * Returns a single character which should replace the given node name
     * when serializing a non-text node.
     */
    function charForNodeName(nodeName) {
        return String.fromCharCode(0xe000 + (hashCode(nodeName) % (0xf900 - 0xe000)));
    }
    exports.charForNodeName = charForNodeName;
    /**
     * Moves trailing HTML tag markers in the DIFF_INSERT and DIFF_DELETE diff items to the front,
     * if possible, in order to improve quality of the DOM diff.
     */
    function cleanUpNodeMarkers(diff) {
        for (let i = 0; i < diff.length - 2;) {
            const diff0 = diff[i];
            const diff1 = diff[i + 1];
            const diff2 = diff[i + 2];
            if (diff0[0] !== diff_match_patch_1.DIFF_EQUAL ||
                diff1[0] === diff_match_patch_1.DIFF_EQUAL ||
                diff2[0] !== diff_match_patch_1.DIFF_EQUAL) {
                i++;
                continue;
            }
            const string0 = diff0[1];
            const string1 = diff1[1];
            const string2 = diff2[1];
            const lastChar0 = string0[string0.length - 1];
            const lastChar1 = string1[string1.length - 1];
            if (lastChar0 !== lastChar1 ||
                lastChar0 < '\uE000' ||
                lastChar0 >= '\uF900') {
                i++;
                continue;
            }
            diff0[1] = string0.substring(0, string0.length - 1);
            diff1[1] = lastChar0 + string1.substring(0, string1.length - 1);
            diff2[1] = lastChar0 + string2;
            if (diff0[1].length === 0) {
                diff.splice(i, 1);
            }
        }
    }
    exports.cleanUpNodeMarkers = cleanUpNodeMarkers;
    const dmp = new diff_match_patch_1.diff_match_patch();
    /**
     * Diffs the 2 strings and cleans up the result before returning it.
     */
    function diffText(oldText, newText) {
        const diff = dmp.diff_main(oldText, newText);
        const result = [];
        const temp = [];
        cleanUpNodeMarkers(diff);
        // Execute `dmp.diff_cleanupSemantic` excluding equal node markers.
        for (let i = 0, l = diff.length; i < l; ++i) {
            const item = diff[i];
            if (item[0] === diff_match_patch_1.DIFF_EQUAL) {
                const text = item[1];
                const totalLength = text.length;
                const prefixLength = /^[^\uE000-\uF8FF]*/.exec(text)[0].length;
                if (prefixLength < totalLength) {
                    const suffixLength = /[^\uE000-\uF8FF]*$/.exec(text)[0].length;
                    if (prefixLength > 0) {
                        temp.push([diff_match_patch_1.DIFF_EQUAL, text.substring(0, prefixLength)]);
                    }
                    dmp.diff_cleanupSemantic(temp);
                    pushAll(result, temp);
                    temp.length = 0;
                    result.push([
                        diff_match_patch_1.DIFF_EQUAL,
                        text.substring(prefixLength, totalLength - suffixLength),
                    ]);
                    if (suffixLength > 0) {
                        temp.push([
                            diff_match_patch_1.DIFF_EQUAL,
                            text.substring(totalLength - suffixLength),
                        ]);
                    }
                }
                else {
                    temp.push(item);
                }
            }
            else {
                temp.push(item);
            }
        }
        dmp.diff_cleanupSemantic(temp);
        pushAll(result, temp);
        temp.length = 0;
        dmp.diff_cleanupMerge(result);
        cleanUpNodeMarkers(result);
        return result;
    }
    exports.diffText = diffText;
    function pushAll(array, items) {
        let destination = array.length;
        let source = 0;
        const length = items.length;
        while (source < length) {
            array[destination++] = items[source++];
        }
    }
    function markUpNode(node, elementName, className) {
        const document = node.ownerDocument;
        const parentNode = node.parentNode;
        const previousSibling = node.previousSibling;
        if (isElement(node)) {
            node.classList.add(className);
        }
        else if (previousSibling &&
            previousSibling.nodeName === elementName &&
            previousSibling.classList.contains(className)) {
            previousSibling.appendChild(node);
        }
        else {
            const wrapper = document.createElement(elementName);
            wrapper.classList.add(className);
            parentNode.insertBefore(wrapper, node);
            wrapper.appendChild(node);
        }
    }
    exports.markUpNode = markUpNode;
    function isTableValid(table, verifyColumns) {
        let columnCount;
        return validateTable(table);
        function validateTable({ childNodes }) {
            const l = childNodes.length;
            let i = 0;
            if (i < l && childNodes[i].nodeName === 'CAPTION') {
                i++;
            }
            if (i < l && childNodes[i].nodeName === 'THEAD') {
                if (!validateRowGroup(childNodes[i])) {
                    return false;
                }
                i++;
            }
            if (i < l && childNodes[i].nodeName === 'TBODY') {
                if (!validateRowGroup(childNodes[i])) {
                    return false;
                }
                i++;
            }
            else {
                return false;
            }
            if (i < l && childNodes[i].nodeName === 'TFOOT') {
                if (!validateRowGroup(childNodes[i])) {
                    return false;
                }
                i++;
            }
            return i === l;
        }
        function validateRowGroup({ childNodes, nodeName }) {
            if (nodeName === 'TBODY' && childNodes.length === 0) {
                return false;
            }
            for (let i = 0, l = childNodes.length; i < l; ++i) {
                if (!validateRow(childNodes[i])) {
                    return false;
                }
            }
            return true;
        }
        function validateRow({ childNodes, nodeName }) {
            if (nodeName !== 'TR' || childNodes.length === 0) {
                return false;
            }
            if (verifyColumns) {
                if (columnCount === undefined) {
                    columnCount = childNodes.length;
                }
                else if (columnCount !== childNodes.length) {
                    return false;
                }
            }
            for (let i = 0, l = childNodes.length; i < l; ++i) {
                if (!validateCell(childNodes[i])) {
                    return false;
                }
            }
            return true;
        }
        function validateCell(node) {
            const { nodeName } = node;
            if (nodeName !== 'TD' && nodeName !== 'TH') {
                return false;
            }
            const cell = node;
            const colspan = cell.getAttribute('colspan');
            const rowspan = cell.getAttribute('rowspan');
            return ((colspan === null || colspan === '1') &&
                (rowspan === null || rowspan === '1'));
        }
    }
    exports.isTableValid = isTableValid;
});
