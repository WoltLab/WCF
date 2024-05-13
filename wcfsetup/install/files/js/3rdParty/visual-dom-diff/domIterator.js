define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DomIterator = void 0;
    class DomIterator {
        rootNode;
        config;
        nextNode;
        descend = true;
        constructor(rootNode, config) {
            this.rootNode = rootNode;
            this.config = config;
            this.nextNode = this.rootNode;
            if (this.skipSelf(this.nextNode)) {
                this.next();
            }
        }
        toArray() {
            const array = [];
            let { done, value } = this.next();
            while (!done) {
                array.push(value);
                ({ done, value } = this.next());
            }
            return array;
        }
        forEach(fn) {
            let { done, value } = this.next();
            while (!done) {
                fn(value);
                ({ done, value } = this.next());
            }
        }
        reduce(fn, initial) {
            let result = initial;
            let { done, value } = this.next();
            while (!done) {
                result = fn(result, value);
                ({ done, value } = this.next());
            }
            return result;
        }
        some(fn) {
            let { done, value } = this.next();
            while (!done) {
                if (fn(value)) {
                    return true;
                }
                ;
                ({ done, value } = this.next());
            }
            return false;
        }
        next() {
            if (!this.nextNode) {
                return { done: true, value: this.rootNode };
            }
            const value = this.nextNode;
            const done = false;
            if (this.descend &&
                this.nextNode.firstChild &&
                !this.skipChildren(this.nextNode)) {
                this.nextNode = this.nextNode.firstChild;
            }
            else if (this.nextNode === this.rootNode) {
                this.nextNode = null;
            }
            else if (this.nextNode.nextSibling) {
                this.nextNode = this.nextNode.nextSibling;
                this.descend = true;
            }
            else {
                this.nextNode = this.nextNode.parentNode;
                this.descend = false;
                this.next(); // Skip this node, as we've visited it already.
            }
            if (this.nextNode && this.skipSelf(this.nextNode)) {
                this.next(); // Skip this node, as directed by the config.
            }
            return { done, value };
        }
        skipSelf(node) {
            return this.config && this.config.skipSelf
                ? this.config.skipSelf(node)
                : false;
        }
        skipChildren(node) {
            return this.config && this.config.skipChildren
                ? this.config.skipChildren(node)
                : false;
        }
    }
    exports.DomIterator = DomIterator;
});
