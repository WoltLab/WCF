define(["require", "exports", "tslib", "../Ajax/Backend", "../Dom/Util", "../Helper/PageOverlay", "../Helper/Selector", "../Timer/Repeating", "../Ui/Alignment"], function (require, exports, tslib_1, Backend_1, Util_1, PageOverlay_1, Selector_1, Repeating_1, UiAlignment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupFor = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Repeating_1 = tslib_1.__importDefault(Repeating_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    class Popover {
        #cache = new Map();
        #currentElement = undefined;
        #container = undefined;
        #endpoint;
        #enabled = true;
        #identifier;
        #pendingElement = undefined;
        #pendingObjectId = undefined;
        #timerStart = undefined;
        #timerHide = undefined;
        constructor(selector, endpoint, identifier) {
            this.#identifier = identifier;
            this.#endpoint = new URL(endpoint);
            (0, Selector_1.wheneverFirstSeen)(selector, (element) => {
                element.addEventListener("mouseenter", () => {
                    this.#hoverStart(element);
                });
                element.addEventListener("mouseleave", () => {
                    this.#hoverEnd(element);
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
        #hoverStart(element) {
            const objectId = this.#getObjectId(element);
            this.#pendingObjectId = objectId;
            if (this.#timerStart === undefined) {
                this.#timerStart = new Repeating_1.default((timer) => {
                    timer.stop();
                    const objectId = this.#pendingObjectId;
                    void this.#getContent(objectId).then((content) => {
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
        #hoverEnd(element) {
            this.#timerStart?.stop();
            this.#pendingObjectId = undefined;
            if (this.#timerHide === undefined) {
                this.#timerHide = new Repeating_1.default(() => {
                    // do something
                }, 500 /* Delay.Hide */);
            }
            else {
                this.#timerHide.restart();
            }
        }
        async #getContent(objectId) {
            let content = this.#cache.get(objectId);
            if (content !== undefined) {
                return content;
            }
            this.#endpoint.searchParams.set("id", objectId.toString());
            const response = await (0, Backend_1.prepareRequest)(this.#endpoint).get().fetchAsResponse();
            if (!response?.ok) {
                return "";
            }
            content = await response.text();
            this.#cache.set(objectId, content);
            return content;
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
        new Popover(selector, endpoint, identifier);
    }
    exports.setupFor = setupFor;
});
