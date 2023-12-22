/**
 * Provides enhanced tooltips.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Environment", "../Helper/PageOverlay", "../Helper/Selector", "./Alignment"], function (require, exports, tslib_1, Environment, PageOverlay_1, Selector_1, UiAlignment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Environment = tslib_1.__importStar(Environment);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    let _text;
    let _tooltip;
    /**
     * Displays the tooltip on mouse enter.
     */
    function mouseEnter(event) {
        const element = event.currentTarget;
        let title = element.title.trim();
        if (title !== "") {
            element.dataset.tooltip = title;
            element.setAttribute("aria-label", title);
            element.removeAttribute("title");
        }
        title = element.dataset.tooltip || "";
        // reset tooltip position
        _tooltip.style.removeProperty("top");
        _tooltip.style.removeProperty("left");
        // ignore empty tooltip
        if (!title.length) {
            _tooltip.classList.remove("active");
            return;
        }
        else {
            _tooltip.classList.add("active");
        }
        _text.textContent = title;
        UiAlignment.set(_tooltip, element, {
            horizontal: "center",
            verticalOffset: 4,
            vertical: "top",
        });
    }
    /**
     * Hides the tooltip once the mouse leaves the element.
     */
    function mouseLeave() {
        _tooltip.classList.remove("active");
    }
    /**
     * Initializes the tooltip element and binds event listener.
     */
    function setup() {
        if (Environment.platform() !== "desktop") {
            return;
        }
        _tooltip = document.createElement("div");
        _tooltip.id = "balloonTooltip";
        _tooltip.classList.add("balloonTooltip");
        _tooltip.addEventListener("transitionend", () => {
            if (!_tooltip.classList.contains("active")) {
                // reset back to the upper left corner, prevent it from staying outside
                // the viewport if the body overflow was previously hidden
                ["bottom", "left", "right", "top"].forEach((property) => {
                    _tooltip.style.removeProperty(property);
                });
            }
        });
        _text = document.createElement("span");
        _text.id = "balloonTooltipText";
        _tooltip.appendChild(_text);
        (0, PageOverlay_1.getPageOverlayContainer)().append(_tooltip);
        (0, Selector_1.wheneverSeen)(".jsTooltip", (element) => {
            element.classList.remove("jsTooltip");
            const title = element.title.trim();
            if (title.length) {
                element.dataset.tooltip = title;
                element.removeAttribute("title");
                element.setAttribute("aria-label", title);
                element.addEventListener("mouseenter", mouseEnter);
                element.addEventListener("mouseleave", mouseLeave);
                element.addEventListener("click", mouseLeave);
            }
        });
        window.addEventListener("scroll", mouseLeave);
    }
    exports.setup = setup;
});
