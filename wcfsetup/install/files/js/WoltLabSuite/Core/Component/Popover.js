/**
 * Customizable popover overlays that show additional information after a short
 * delay.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "tslib", "../Dom/Util", "../Helper/PageOverlay", "../Helper/Selector", "../Timer/Repeating", "../Ui/Alignment", "./Popover/SharedCache"], function (require, exports, tslib_1, Util_1, PageOverlay_1, Selector_1, Repeating_1, UiAlignment, SharedCache_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupFor = setupFor;
    exports.resetCache = resetCache;
    Util_1 = tslib_1.__importDefault(Util_1);
    Repeating_1 = tslib_1.__importDefault(Repeating_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    SharedCache_1 = tslib_1.__importDefault(SharedCache_1);
    class Popover {
        #cache;
        #container = undefined;
        #enabled = true;
        #element;
        #identifier;
        #timerShouldShow = undefined;
        #timerHide = undefined;
        constructor(cache, element, identifier) {
            this.#cache = cache;
            this.#element = element;
            this.#identifier = identifier;
            element.addEventListener("mouseenter", () => {
                this.#showPopover();
            });
            element.addEventListener("mouseleave", () => {
                this.#hidePopover();
            });
            const mq = window.matchMedia("(hover:hover)");
            this.#setEnabled(mq.matches);
            mq.addEventListener("change", (event) => {
                this.#setEnabled(event.matches);
            });
            window.addEventListener("beforeunload", () => {
                this.#setEnabled(false);
            });
            this.#showPopover();
        }
        #showPopover() {
            if (!this.#enabled) {
                return;
            }
            this.#timerHide?.stop();
            if (this.#timerShouldShow === undefined) {
                this.#timerShouldShow = new Repeating_1.default((timer) => {
                    timer.stop();
                    const objectId = this.#getObjectId();
                    void this.#cache.get(objectId).then((content) => {
                        if (content === "") {
                            return;
                        }
                        const container = this.#getContainer();
                        Util_1.default.setInnerHtml(container, content);
                        UiAlignment.set(container, this.#element, { vertical: "top" });
                        container.setAttribute("aria-hidden", "false");
                    });
                }, 800 /* Delay.Show */);
            }
            else {
                this.#timerShouldShow.restart();
            }
        }
        #hidePopover() {
            if (!this.#enabled) {
                return;
            }
            this.#timerShouldShow?.stop();
            if (this.#timerHide === undefined) {
                this.#timerHide = new Repeating_1.default((timer) => {
                    timer.stop();
                    this.#container?.setAttribute("aria-hidden", "true");
                }, 500 /* Delay.Hide */);
            }
            else {
                this.#timerHide.restart();
            }
        }
        #setEnabled(enabled) {
            this.#enabled = enabled;
            this.#container?.setAttribute("aria-hidden", "true");
        }
        #getObjectId() {
            return parseInt(this.#element.dataset.objectId);
        }
        #getContainer() {
            if (this.#container === undefined) {
                this.#container = document.createElement("div");
                this.#container.classList.add("popoverContainer");
                this.#container.dataset.identifier = this.#identifier;
                this.#container.setAttribute("aria-hidden", "true");
                this.#container.addEventListener("transitionend", () => {
                    if (this.#container.getAttribute("aria-hidden") === "true") {
                        this.#container.remove();
                    }
                });
                this.#container.addEventListener("mouseenter", () => {
                    this.#timerHide?.stop();
                });
                this.#container.addEventListener("mouseleave", () => {
                    this.#hidePopover();
                });
            }
            if (this.#container.parentNode === null) {
                (0, PageOverlay_1.getPageOverlayContainer)().append(this.#container);
            }
            return this.#container;
        }
    }
    const cacheByIdentifier = new Map();
    function setupFor(configuration) {
        const { identifier, endpoint, selector } = configuration;
        const cache = new SharedCache_1.default(endpoint);
        cacheByIdentifier.set(identifier, cache);
        (0, Selector_1.wheneverFirstSeen)(selector, (element) => {
            // Disregard elements nested inside a popover.
            if (element.closest(".popover, .popoverContainer") !== null) {
                return;
            }
            element.addEventListener("mouseenter", () => {
                new Popover(cache, element, identifier);
            }, { once: true });
        });
    }
    function resetCache(identifier, objectId) {
        cacheByIdentifier.get(identifier).reset(objectId);
    }
});
