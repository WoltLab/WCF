define(["require", "exports", "tslib", "../Dom/Util", "../Helper/PageOverlay", "../Helper/Selector", "../Timer/Repeating", "../Ui/Alignment", "./Popover/SharedCache"], function (require, exports, tslib_1, Util_1, PageOverlay_1, Selector_1, Repeating_1, UiAlignment, SharedCache_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupFor = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Repeating_1 = tslib_1.__importDefault(Repeating_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    SharedCache_1 = tslib_1.__importDefault(SharedCache_1);
    class Popover {
        #cache;
        #container = undefined;
        #enabled = true;
        #identifier;
        #pendingObjectId = undefined;
        #timerStart = undefined;
        #timerHide = undefined;
        constructor(cache, selector, identifier) {
            this.#cache = cache;
            this.#identifier = identifier;
            (0, Selector_1.wheneverFirstSeen)(selector, (element) => {
                element.addEventListener("mouseenter", () => {
                    this.#showPopover(element);
                });
                element.addEventListener("mouseleave", () => {
                    this.#hidePopover();
                });
            });
            const mq = window.matchMedia("(hover:hover)");
            this.#setEnabled(mq.matches);
            mq.addEventListener("change", (event) => {
                this.#setEnabled(event.matches);
            });
            window.addEventListener("beforeunload", () => {
                this.#setEnabled(false);
            });
        }
        #showPopover(element) {
            const objectId = this.#getObjectId(element);
            this.#pendingObjectId = objectId;
            if (this.#timerStart === undefined) {
                this.#timerStart = new Repeating_1.default((timer) => {
                    timer.stop();
                    const objectId = this.#pendingObjectId;
                    void this.#cache.get(objectId).then((content) => {
                        if (objectId !== this.#pendingObjectId) {
                            return;
                        }
                        const container = this.#getContainer();
                        Util_1.default.setInnerHtml(container, content);
                        UiAlignment.set(container, element, { vertical: "top" });
                        container.setAttribute("aria-hidden", "false");
                    });
                }, 800 /* Delay.Show */);
            }
            else {
                this.#timerStart.restart();
            }
        }
        #hidePopover() {
            if (this.#timerHide === undefined) {
                this.#timerHide = new Repeating_1.default((timer) => {
                    timer.stop();
                    this.#timerStart?.stop();
                    this.#container?.setAttribute("aria-hidden", "true");
                }, 500 /* Delay.Hide */);
            }
            else {
                this.#timerHide.restart();
            }
        }
        #setEnabled(enabled) {
            this.#enabled = enabled;
        }
        #getObjectId(element) {
            return parseInt(element.dataset.objectId);
        }
        #getContainer() {
            if (this.#container === undefined) {
                this.#container = document.createElement("div");
                this.#container.classList.add("popoverContainer");
                this.#container.dataset.identifier = this.#identifier;
                this.#container.setAttribute("aria-hidden", "true");
            }
            this.#container.remove();
            (0, PageOverlay_1.getPageOverlayContainer)().append(this.#container);
            return this.#container;
        }
    }
    function setupFor(configuration) {
        const { identifier, endpoint, selector } = configuration;
        const cache = new SharedCache_1.default(endpoint);
        new Popover(cache, selector, identifier);
    }
    exports.setupFor = setupFor;
});
