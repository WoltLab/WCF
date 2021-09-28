/**
 * Executes provided callback if scroll threshold is reached. Usable to determine
 * if user reached the bottom of an element to load new elements on the fly.
 *
 * If you do not provide a value for `reference` and `target` it will assume you're
 * monitoring page scrolls, otherwise a valid HTMLElement must be provided.
 *
 * If reference is `null` the entire window will be the reference.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/ScrollSpy
 * @since 5.5
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ScrollSpy = void 0;
    class ScrollSpy {
        constructor(threshold, callback, target, reference) {
            if (threshold <= 0) {
                throw new Error("Given threshold must be greater than 0.");
            }
            this.threshold = threshold;
            this.callback = callback;
            this.reference = reference;
            this.target = target;
            this.start();
            this.scroll();
        }
        scroll() {
            const targetHeight = this.getTargetHeight();
            const topOffset = this.getReferenceOffset();
            const referenceHeight = this.getReferenceHeight();
            if (targetHeight - (referenceHeight + topOffset) < this.threshold) {
                this.callback();
            }
        }
        getTargetHeight() {
            if (this.target) {
                return this.target.clientHeight;
            }
            return document.querySelector("body").scrollHeight;
        }
        getReferenceOffset() {
            if (this.reference) {
                return this.reference.scrollTop;
            }
            return window.scrollY;
        }
        getReferenceHeight() {
            if (this.reference) {
                return this.reference.clientHeight;
            }
            return window.innerHeight;
        }
        getReference() {
            if (this.reference) {
                return this.reference;
            }
            return window;
        }
        start() {
            this.getReference().addEventListener("scroll", () => this.scroll());
        }
        stop() {
            this.getReference().removeEventListener("scroll", () => this.scroll());
        }
    }
    exports.ScrollSpy = ScrollSpy;
});
