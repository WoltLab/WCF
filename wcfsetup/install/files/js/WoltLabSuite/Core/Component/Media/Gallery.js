/**
 * Adds the controls to navigate between the images of media galleries.
 *
 * The gallery itself is a horizontally scrollable track that snaps to the
 * images and remains usable without these controls.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function getStep(track, slides) {
        if (slides.length < 2) {
            return track.clientWidth;
        }
        return Math.abs(slides[1].offsetLeft - slides[0].offsetLeft);
    }
    function initGallery(gallery) {
        const track = gallery.querySelector(".mediaGallery__track");
        const controls = gallery.querySelector(".mediaGallery__controls");
        const previousButton = controls.querySelector(".mediaGallery__button--previous");
        const nextButton = controls.querySelector(".mediaGallery__button--next");
        const counter = controls.querySelector(".mediaGallery__counter");
        const slides = Array.from(track.querySelectorAll(".mediaGallery__slide"));
        // The scroll position is negative for right-to-left languages.
        const isRtl = () => window.getComputedStyle(track).direction === "rtl";
        const update = () => {
            const isScrollable = track.scrollWidth > track.clientWidth + 1;
            controls.hidden = !isScrollable;
            if (!isScrollable) {
                return;
            }
            const scrollPosition = Math.abs(track.scrollLeft);
            const index = Math.min(Math.round(scrollPosition / getStep(track, slides)), slides.length - 1);
            counter.textContent = `${index + 1} / ${slides.length}`;
            previousButton.disabled = scrollPosition <= 1;
            nextButton.disabled = scrollPosition + track.clientWidth >= track.scrollWidth - 1;
        };
        const scrollBySlides = (count) => {
            track.scrollBy({ left: count * getStep(track, slides) });
        };
        previousButton.addEventListener("click", () => scrollBySlides(isRtl() ? 1 : -1));
        nextButton.addEventListener("click", () => scrollBySlides(isRtl() ? -1 : 1));
        track.addEventListener("keydown", (event) => {
            if (event.target !== track) {
                return;
            }
            if (event.key === "ArrowLeft") {
                event.preventDefault();
                scrollBySlides(-1);
            }
            else if (event.key === "ArrowRight") {
                event.preventDefault();
                scrollBySlides(1);
            }
        });
        let pendingUpdate = false;
        track.addEventListener("scroll", () => {
            if (pendingUpdate) {
                return;
            }
            pendingUpdate = true;
            window.requestAnimationFrame(() => {
                pendingUpdate = false;
                update();
            });
        }, { passive: true });
        new ResizeObserver(() => update()).observe(track);
        update();
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)(".mediaGallery", (gallery) => initGallery(gallery));
    }
});
