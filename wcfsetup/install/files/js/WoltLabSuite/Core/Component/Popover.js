define(["require", "exports", "tslib", "../Ajax/Backend", "../Dom/Util", "../Helper/PageOverlay", "../Helper/Selector"], function (require, exports, tslib_1, Backend_1, Util_1, PageOverlay_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupFor = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    class Popover {
        #cache = new Map();
        #currentElement = undefined;
        #container = undefined;
        #endpoint;
        #enabled = true;
        #identifier;
        constructor(selector, endpoint, identifier) {
            this.#identifier = identifier;
            this.#endpoint = new URL(endpoint);
            (0, Selector_1.wheneverFirstSeen)(selector, (element) => {
                element.addEventListener("mouseenter", () => {
                    void this.#hoverStart(element);
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
        async #hoverStart(element) {
            const objectId = this.#getObjectId(element);
            let content = this.#cache.get(objectId);
            if (content === undefined) {
                content = await this.#fetch(objectId);
                this.#cache.set(objectId, content);
            }
            Util_1.default.setInnerHtml(this.#getContainer(), content);
        }
        #hoverEnd(element) { }
        async #fetch(objectId) {
            this.#endpoint.searchParams.set("id", objectId.toString());
            const response = await (0, Backend_1.prepareRequest)(this.#endpoint).get().fetchAsResponse();
            if (response?.ok) {
                return await response.text();
            }
            return "";
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
                this.#container.dataset.identifier = this.#identifier;
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
