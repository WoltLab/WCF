/**
 * Provides enhanced tooltips.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Tooltip
 */
define(["require", "exports", "tslib", "../Dom/Change/Listener", "../Environment", "./Alignment"], function (require, exports, tslib_1, Listener_1, Environment, UiAlignment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = exports.setup = void 0;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Environment = tslib_1.__importStar(Environment);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    let _pointer;
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
            pointer: true,
            pointerClassNames: ["inverse"],
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
        _pointer = document.createElement("span");
        _pointer.classList.add("elementPointer");
        _pointer.appendChild(document.createElement("span"));
        _tooltip.appendChild(_pointer);
        document.body.appendChild(_tooltip);
        init();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Tooltip", init);
        window.addEventListener("scroll", mouseLeave);
    }
    exports.setup = setup;
    /**
     * Initializes tooltip elements.
     */
    function init() {
        document.querySelectorAll(".jsTooltip").forEach((element) => {
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
    }
    exports.init = init;
});
