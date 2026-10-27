/**
 * Customizable popover overlays that show additional information after a short
 * delay.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
define(["require", "exports", "tslib", "../Devtools", "../Dom/Util", "../Helper/PageOverlay", "../Helper/Selector", "../Timer/Repeating", "../Ui/Alignment", "../Ui/Dropdown/Simple", "./Popover/SharedCache"], function (require, exports, tslib_1, Devtools_1, Util_1, PageOverlay_1, Selector_1, Repeating_1, UiAlignment, Simple_1, SharedCache_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupFor = setupFor;
    exports.destroyDropdowns = destroyDropdowns;
    exports.resetCache = resetCache;
    Devtools_1 = tslib_1.__importDefault(Devtools_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    Repeating_1 = tslib_1.__importDefault(Repeating_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    SharedCache_1 = tslib_1.__importDefault(SharedCache_1);
    class Popover {
        #activated = false;
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
                this.#activated = false;
                this.#hidePopover();
            });
            // Links opened in a new tab or through the context menu keep the page in
            // place, leaving the popover to cover the adjacent elements.
            for (const eventName of ["click", "auxclick", "contextmenu"]) {
                element.addEventListener(eventName, () => {
                    this.#activated = true;
                    this.#hidePopoverImmediately();
                });
            }
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
                    if (objectId === undefined) {
                        console.error("Missing attribute `data-object-id` for element", this.#element);
                        return;
                    }
                    void this.#cache.get(objectId).then((content) => {
                        // The element may have been activated while the request was pending.
                        if (content === "" || this.#activated) {
                            return;
                        }
                        const container = this.#getContainer();
                        destroyDropdowns(container);
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
            if (!this.#enabled || Devtools_1.default._internal_.persistentPopover()) {
                return;
            }
            this.#timerShouldShow?.stop();
            if (this.#timerHide === undefined) {
                this.#timerHide = new Repeating_1.default((timer) => {
                    if (this.#container?.querySelector(".dropdown.dropdownOpen") !== null) {
                        return;
                    }
                    timer.stop();
                    this.#container?.setAttribute("aria-hidden", "true");
                }, 500 /* Delay.Hide */);
            }
            else {
                this.#timerHide.restart();
            }
        }
        #hidePopoverImmediately() {
            this.#timerShouldShow?.stop();
            this.#timerHide?.stop();
            this.#container?.setAttribute("aria-hidden", "true");
        }
        #setEnabled(enabled) {
            this.#enabled = enabled;
            this.#container?.setAttribute("aria-hidden", "true");
        }
        #getObjectId() {
            const objectId = parseInt(this.#element.dataset.objectId || "");
            if (Number.isNaN(objectId) || objectId === 0) {
                return undefined;
            }
            return objectId;
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
    /**
     * The dropdown menus have been moved into the global container and must be
     * removed together with the content of the popover.
     */
    function destroyDropdowns(element) {
        element.querySelectorAll(".dropdownToggle[data-target]").forEach((toggle) => {
            Simple_1.default.destroy(toggle.dataset.target);
        });
    }
    function resetCache(identifier, objectId) {
        cacheByIdentifier.get(identifier)?.reset(objectId);
    }
});
