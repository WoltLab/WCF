/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "WoltLabSuite/Core/Ui/Message/UserConsent", "WoltLabSuite/Core/Core"], function (require, exports, UserConsent_1, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ConsentPlugin = void 0;
    class ConsentPlugin {
        #api = undefined;
        #sliderToType = new Map();
        #templateNode;
        #onAddSlideEvent = this.#onAddSlide.bind(this);
        #onAttachSlideElEvent = this.#onAttachSlideEl.bind(this);
        constructor() {
            this.#templateNode = document.getElementById("consentImageViewer");
        }
        init(api) {
            if (this.#showAllMedia()) {
                return;
            }
            this.#api = api;
            api.on("Carousel.addSlide", this.#onAddSlideEvent).on("Carousel.attachSlideEl", this.#onAttachSlideElEvent);
        }
        destroy() {
            this.#api
                ?.off("Carousel.addSlide", this.#onAddSlideEvent)
                ?.off("Carousel.attachSlideEl", this.#onAttachSlideElEvent);
        }
        #onAddSlide(_, __, slide) {
            if (!slide.src) {
                return;
            }
            if (this.#isExternalURL(slide.src)) {
                this.#sliderToType.set(slide, slide.type);
                slide.type = "consent";
            }
        }
        #onAttachSlideEl(_, carousel, slide) {
            if (slide.type !== "consent") {
                return;
            }
            slide.el.innerHTML = "";
            slide.el.append(this.getConsentHTML(carousel, slide));
        }
        getConsentHTML(carousel, slide) {
            const clone = this.#templateNode.content.cloneNode(true);
            const messageUserConsent = clone.querySelector(".messageUserConsent");
            messageUserConsent.dataset.payload = "";
            const externalURL = clone.querySelector(".externalURL");
            const url = new URL(slide.src);
            externalURL.href = slide.src;
            externalURL.innerText = url.host;
            const button = clone.querySelector(".jsButtonMessageUserConsentEnable");
            button.addEventListener("click", () => {
                this.destroy();
                this.#templateNode.dataset.showAllMedia = "1";
                carousel
                    .getSlides()
                    .filter((slide) => slide.type === "consent")
                    .forEach((slide) => {
                    slide.type = this.#sliderToType.get(slide);
                    slide.el.innerHTML = "";
                });
                // refresh current slide content
                carousel.emit("detachSlideEl", slide);
                carousel.emit("attachSlideEl", slide);
            });
            return clone;
        }
        #showAllMedia() {
            if (!this.#templateNode) {
                return true;
            }
            if ((0, Core_1.stringToBool)(this.#templateNode.dataset.showAllMedia)) {
                return true;
            }
            return (0, UserConsent_1.allExternalMediaEnabled)();
        }
        #isExternalURL(url) {
            return new URL(url).origin !== window.location.origin;
        }
    }
    exports.ConsentPlugin = ConsentPlugin;
});
