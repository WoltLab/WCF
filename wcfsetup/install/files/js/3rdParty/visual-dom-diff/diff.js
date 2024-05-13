define(["require", "exports", "diff-match-patch", "./config", "./domIterator", "./util"], function (require, exports, diff_match_patch_1, config_1, domIterator_1, util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.visualDomDiff = void 0;
    /**
     * A simple helper which allows us to treat TH as TD in certain situations.
     */
    const nodeNameOverride = (nodeName) => {
        return nodeName === 'TH' ? 'TD' : nodeName;
    };
    /**
     * Stringifies a DOM node recursively. Text nodes are represented by their `data`,
     * while all other nodes are represented by a single Unicode code point
     * from the Private Use Area of the Basic Multilingual Plane.
     */
    const serialize = (root, config) => new domIterator_1.DomIterator(root, config).reduce((text, node) => text +
        ((0, util_1.isText)(node)
            ? node.data
            : (0, util_1.charForNodeName)(nodeNameOverride(node.nodeName))), '');
    const getLength = (node) => ((0, util_1.isText)(node) ? node.length : 1);
    const isTr = (node) => node.nodeName === 'TR';
    const isNotTr = (node) => !isTr(node);
    const trIteratorOptions = {
        skipChildren: isTr,
        skipSelf: isNotTr,
    };
    function visualDomDiff(oldRootNode, newRootNode, options = {}) {
        // Define config and simple helpers.
        const document = newRootNode.ownerDocument || newRootNode;
        const config = (0, config_1.optionsToConfig)(options);
        const { addedClass, diffText, modifiedClass, removedClass, skipSelf, skipChildren, } = config;
        const notSkipSelf = (node) => !skipSelf(node);
        const getDepth = (node, rootNode) => (0, util_1.getAncestors)(node, rootNode).filter(notSkipSelf).length;
        const isFormattingNode = (node) => (0, util_1.isElement)(node) && skipSelf(node);
        const getFormattingAncestors = (node, rootNode) => (0, util_1.getAncestors)(node, rootNode)
            .filter(isFormattingNode)
            .reverse();
        const getColumnValue = (node) => addedNodes.has(node) ? 1 : removedNodes.has(node) ? -1 : 0;
        // Input iterators.
        const diffArray = diffText(serialize(oldRootNode, config), serialize(newRootNode, config));
        let diffIndex = 0;
        const oldIterator = new domIterator_1.DomIterator(oldRootNode, config);
        const newIterator = new domIterator_1.DomIterator(newRootNode, config);
        // Input variables produced by the input iterators.
        let oldDone;
        let newDone;
        let diffItem;
        let oldNode;
        let newNode;
        let diffOffset = 0;
        let oldOffset = 0;
        let newOffset = 0;
        diffItem = diffArray[diffIndex++];
        ({ done: oldDone, value: oldNode } = oldIterator.next());
        ({ done: newDone, value: newNode } = newIterator.next());
        // Output variables.
        const rootOutputNode = document.createDocumentFragment();
        let oldOutputNode = rootOutputNode;
        let oldOutputDepth = 0;
        let newOutputNode = rootOutputNode;
        let newOutputDepth = 0;
        let removedNode = null;
        let addedNode = null;
        const removedNodes = new Set();
        const addedNodes = new Set();
        const modifiedNodes = new Set();
        const formattingMap = new Map();
        const equalTables = new Array();
        const equalRows = new Map();
        function prepareOldOutput() {
            const depth = getDepth(oldNode, oldRootNode);
            while (oldOutputDepth > depth) {
                /* istanbul ignore if */
                if (!oldOutputNode.parentNode) {
                    return (0, util_1.never)();
                }
                if (oldOutputNode === removedNode) {
                    removedNode = null;
                }
                oldOutputNode = oldOutputNode.parentNode;
                oldOutputDepth--;
            }
            /* istanbul ignore if */
            if (oldOutputDepth !== depth) {
                return (0, util_1.never)();
            }
        }
        function prepareNewOutput() {
            const depth = getDepth(newNode, newRootNode);
            while (newOutputDepth > depth) {
                /* istanbul ignore if */
                if (!newOutputNode.parentNode) {
                    return (0, util_1.never)();
                }
                if (newOutputNode === addedNode) {
                    addedNode = null;
                }
                newOutputNode = newOutputNode.parentNode;
                newOutputDepth--;
            }
            /* istanbul ignore if */
            if (newOutputDepth !== depth) {
                return (0, util_1.never)();
            }
        }
        function appendCommonChild(node) {
            /* istanbul ignore if */
            if (oldOutputNode !== newOutputNode || addedNode || removedNode) {
                return (0, util_1.never)();
            }
            if ((0, util_1.isText)(node)) {
                const oldFormatting = getFormattingAncestors(oldNode, oldRootNode);
                const newFormatting = getFormattingAncestors(newNode, newRootNode);
                formattingMap.set(node, newFormatting);
                const length = oldFormatting.length;
                if (length !== newFormatting.length) {
                    modifiedNodes.add(node);
                }
                else {
                    for (let i = 0; i < length; ++i) {
                        if (!(0, util_1.areNodesEqual)(oldFormatting[i], newFormatting[i])) {
                            modifiedNodes.add(node);
                            break;
                        }
                    }
                }
            }
            else {
                if (!(0, util_1.areNodesEqual)(oldNode, newNode)) {
                    modifiedNodes.add(node);
                }
                const nodeName = oldNode.nodeName;
                if (nodeName === 'TABLE') {
                    equalTables.push({
                        newTable: newNode,
                        oldTable: oldNode,
                        outputTable: node,
                    });
                }
                else if (nodeName === 'TR') {
                    equalRows.set(node, {
                        newRow: newNode,
                        oldRow: oldNode,
                    });
                }
            }
            newOutputNode.appendChild(node);
            oldOutputNode = node;
            newOutputNode = node;
            oldOutputDepth++;
            newOutputDepth++;
        }
        function appendOldChild(node) {
            if (!removedNode) {
                removedNode = node;
                removedNodes.add(node);
            }
            if ((0, util_1.isText)(node)) {
                const oldFormatting = getFormattingAncestors(oldNode, oldRootNode);
                formattingMap.set(node, oldFormatting);
            }
            oldOutputNode.appendChild(node);
            oldOutputNode = node;
            oldOutputDepth++;
        }
        function appendNewChild(node) {
            if (!addedNode) {
                addedNode = node;
                addedNodes.add(node);
            }
            if ((0, util_1.isText)(node)) {
                const newFormatting = getFormattingAncestors(newNode, newRootNode);
                formattingMap.set(node, newFormatting);
            }
            newOutputNode.appendChild(node);
            newOutputNode = node;
            newOutputDepth++;
        }
        function nextDiff(step) {
            const length = diffItem[1].length;
            diffOffset += step;
            if (diffOffset === length) {
                diffItem = diffArray[diffIndex++];
                diffOffset = 0;
            }
            else {
                /* istanbul ignore if */
                if (diffOffset > length) {
                    return (0, util_1.never)();
                }
            }
        }
        function nextOld(step) {
            const length = getLength(oldNode);
            oldOffset += step;
            if (oldOffset === length) {
                ;
                ({ done: oldDone, value: oldNode } = oldIterator.next());
                oldOffset = 0;
            }
            else {
                /* istanbul ignore if */
                if (oldOffset > length) {
                    return (0, util_1.never)();
                }
            }
        }
        function nextNew(step) {
            const length = getLength(newNode);
            newOffset += step;
            if (newOffset === length) {
                ;
                ({ done: newDone, value: newNode } = newIterator.next());
                newOffset = 0;
            }
            else {
                /* istanbul ignore if */
                if (newOffset > length) {
                    return (0, util_1.never)();
                }
            }
        }
        // Copy all content from oldRootNode and newRootNode to rootOutputNode,
        // while deduplicating identical content.
        // Difference markers and formatting are excluded at this stage.
        while (diffItem) {
            if (diffItem[0] === diff_match_patch_1.DIFF_DELETE) {
                /* istanbul ignore if */
                if (oldDone) {
                    return (0, util_1.never)();
                }
                prepareOldOutput();
                const length = Math.min(diffItem[1].length - diffOffset, getLength(oldNode) - oldOffset);
                const text = diffItem[1].substring(diffOffset, diffOffset + length);
                appendOldChild((0, util_1.isText)(oldNode)
                    ? document.createTextNode(text)
                    : oldNode.cloneNode(false));
                nextDiff(length);
                nextOld(length);
            }
            else if (diffItem[0] === diff_match_patch_1.DIFF_INSERT) {
                /* istanbul ignore if */
                if (newDone) {
                    return (0, util_1.never)();
                }
                prepareNewOutput();
                const length = Math.min(diffItem[1].length - diffOffset, getLength(newNode) - newOffset);
                const text = diffItem[1].substring(diffOffset, diffOffset + length);
                appendNewChild((0, util_1.isText)(newNode)
                    ? document.createTextNode(text)
                    : newNode.cloneNode(false));
                nextDiff(length);
                nextNew(length);
            }
            else {
                /* istanbul ignore if */
                if (oldDone || newDone) {
                    return (0, util_1.never)();
                }
                prepareOldOutput();
                prepareNewOutput();
                const length = Math.min(diffItem[1].length - diffOffset, getLength(oldNode) - oldOffset, getLength(newNode) - newOffset);
                const text = diffItem[1].substring(diffOffset, diffOffset + length);
                if (oldOutputNode === newOutputNode &&
                    (((0, util_1.isText)(oldNode) && (0, util_1.isText)(newNode)) ||
                        (nodeNameOverride(oldNode.nodeName) ===
                            nodeNameOverride(newNode.nodeName) &&
                            !skipChildren(oldNode) &&
                            !skipChildren(newNode)) ||
                        (0, util_1.areNodesEqual)(oldNode, newNode))) {
                    appendCommonChild((0, util_1.isText)(newNode)
                        ? document.createTextNode(text)
                        : newNode.cloneNode(false));
                }
                else {
                    appendOldChild((0, util_1.isText)(oldNode)
                        ? document.createTextNode(text)
                        : oldNode.cloneNode(false));
                    appendNewChild((0, util_1.isText)(newNode)
                        ? document.createTextNode(text)
                        : newNode.cloneNode(false));
                }
                nextDiff(length);
                nextOld(length);
                nextNew(length);
            }
        }
        // Move deletes before inserts.
        removedNodes.forEach(node => {
            const parentNode = node.parentNode;
            let previousSibling = node.previousSibling;
            while (previousSibling && addedNodes.has(previousSibling)) {
                parentNode.insertBefore(node, previousSibling);
                previousSibling = node.previousSibling;
            }
        });
        // Ensure a user friendly result for tables.
        equalTables.forEach(equalTable => {
            const { newTable, oldTable, outputTable } = equalTable;
            // Handle tables which can't be diffed nicely.
            if (!(0, util_1.isTableValid)(oldTable, true) ||
                !(0, util_1.isTableValid)(newTable, true) ||
                !(0, util_1.isTableValid)(outputTable, false)) {
                // Remove all values which were previously recorded for outputTable.
                new domIterator_1.DomIterator(outputTable).forEach(node => {
                    addedNodes.delete(node);
                    removedNodes.delete(node);
                    modifiedNodes.delete(node);
                    formattingMap.delete(node);
                });
                // Display both the old and new table.
                const parentNode = outputTable.parentNode;
                const oldTableClone = oldTable.cloneNode(true);
                const newTableClone = newTable.cloneNode(true);
                parentNode.insertBefore(oldTableClone, outputTable);
                parentNode.insertBefore(newTableClone, outputTable);
                parentNode.removeChild(outputTable);
                removedNodes.add(oldTableClone);
                addedNodes.add(newTableClone);
                return;
            }
            // Figure out which columns have been added or removed
            // based on the first row appearing in both tables.
            //
            // -  1: column added
            // -  0: column equal
            // - -1: column removed
            const columns = [];
            new domIterator_1.DomIterator(outputTable, trIteratorOptions).some(row => {
                const diffedRows = equalRows.get(row);
                if (!diffedRows) {
                    return false;
                }
                const { oldRow, newRow } = diffedRows;
                const oldColumnCount = oldRow.childNodes.length;
                const newColumnCount = newRow.childNodes.length;
                const maxColumnCount = Math.max(oldColumnCount, newColumnCount);
                const minColumnCount = Math.min(oldColumnCount, newColumnCount);
                if (row.childNodes.length === maxColumnCount) {
                    // The generic diff algorithm worked properly in this case,
                    // so we can rely on its results.
                    const cells = row.childNodes;
                    for (let i = 0, l = cells.length; i < l; ++i) {
                        columns.push(getColumnValue(cells[i]));
                    }
                }
                else {
                    // Fallback to a simple but correct algorithm.
                    let i = 0;
                    let columnValue = 0;
                    while (i < minColumnCount) {
                        columns[i++] = columnValue;
                    }
                    columnValue = oldColumnCount < newColumnCount ? 1 : -1;
                    while (i < maxColumnCount) {
                        columns[i++] = columnValue;
                    }
                }
                return true;
            });
            const columnCount = columns.length;
            /* istanbul ignore if */
            if (columnCount === 0) {
                return (0, util_1.never)();
            }
            // Fix up the rows which do not align with `columns`.
            new domIterator_1.DomIterator(outputTable, trIteratorOptions).forEach(row => {
                const cells = row.childNodes;
                if (addedNodes.has(row) || addedNodes.has(row.parentNode)) {
                    if (cells.length < columnCount) {
                        for (let i = 0; i < columnCount; ++i) {
                            if (columns[i] === -1) {
                                const td = document.createElement('TD');
                                row.insertBefore(td, cells[i]);
                                removedNodes.add(td);
                            }
                        }
                    }
                }
                else if (removedNodes.has(row) ||
                    removedNodes.has(row.parentNode)) {
                    if (cells.length < columnCount) {
                        for (let i = 0; i < columnCount; ++i) {
                            if (columns[i] === 1) {
                                const td = document.createElement('TD');
                                row.insertBefore(td, cells[i]);
                            }
                        }
                    }
                }
                else {
                    // Check, if the columns in this row are aligned with those in the reference row.
                    let isAligned = true;
                    for (let i = 0, l = cells.length; i < l; ++i) {
                        if (getColumnValue(cells[i]) !== columns[i]) {
                            isAligned = false;
                            break;
                        }
                    }
                    if (!isAligned) {
                        // Remove all values which were previously recorded for row's content.
                        const iterator = new domIterator_1.DomIterator(row);
                        iterator.next(); // Skip the row itself.
                        iterator.forEach(node => {
                            addedNodes.delete(node);
                            removedNodes.delete(node);
                            modifiedNodes.delete(node);
                            formattingMap.delete(node);
                        });
                        // Remove the row's content.
                        while (row.firstChild) {
                            row.removeChild(row.firstChild);
                        }
                        // Diff the individual cells.
                        const { newRow, oldRow } = equalRows.get(row);
                        const newCells = newRow.childNodes;
                        const oldCells = oldRow.childNodes;
                        let oldIndex = 0;
                        let newIndex = 0;
                        for (let i = 0; i < columnCount; ++i) {
                            if (columns[i] === 1) {
                                const newCellClone = newCells[newIndex++].cloneNode(true);
                                row.appendChild(newCellClone);
                                addedNodes.add(newCellClone);
                            }
                            else if (columns[i] === -1) {
                                const oldCellClone = oldCells[oldIndex++].cloneNode(true);
                                row.appendChild(oldCellClone);
                                removedNodes.add(oldCellClone);
                            }
                            else {
                                row.appendChild(visualDomDiff(oldCells[oldIndex++], newCells[newIndex++], options));
                            }
                        }
                    }
                }
            });
            return;
        });
        // Mark up the content which has been removed.
        removedNodes.forEach(node => {
            (0, util_1.markUpNode)(node, 'DEL', removedClass);
        });
        // Mark up the content which has been added.
        addedNodes.forEach(node => {
            (0, util_1.markUpNode)(node, 'INS', addedClass);
        });
        // Mark up the content which has been modified.
        if (!config.skipModified) {
            modifiedNodes.forEach(modifiedNode => {
                (0, util_1.markUpNode)(modifiedNode, 'INS', modifiedClass);
            });
        }
        // Add formatting.
        formattingMap.forEach((formattingNodes, textNode) => {
            formattingNodes.forEach(formattingNode => {
                const parentNode = textNode.parentNode;
                const previousSibling = textNode.previousSibling;
                if (previousSibling &&
                    (0, util_1.areNodesEqual)(previousSibling, formattingNode)) {
                    previousSibling.appendChild(textNode);
                }
                else {
                    const clonedFormattingNode = formattingNode.cloneNode(false);
                    parentNode.insertBefore(clonedFormattingNode, textNode);
                    clonedFormattingNode.appendChild(textNode);
                }
            });
        });
        return rootOutputNode;
    }
    exports.visualDomDiff = visualDomDiff;
});
