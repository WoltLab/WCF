/**
 * Generic handler for collapsible bbcode boxes.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.observe = observe;
    function initContainer(container, toggleButtons, overflowContainer) {
        toggleButtons.forEach((toggleButton) => {
            toggleButton.classList.add("jsToggleButtonEnabled");
            toggleButton.addEventListener("click", (ev) => toggleContainer(container, toggleButtons, ev));
            toggleButton.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    toggleContainer(container, toggleButtons);
                }
            });
        });
        // expand boxes that are initially scrolled
        if (overflowContainer.scrollTop !== 0) {
            overflowContainer.scrollTop = 0;
            toggleContainer(container, toggleButtons);
        }
        overflowContainer.addEventListener("scroll", () => {
            overflowContainer.scrollTop = 0;
            if (container.classList.contains("collapsed")) {
                toggleContainer(container, toggleButtons);
            }
        });
    }
    function toggleContainer(container, toggleButtons, event) {
        if (container.classList.toggle("collapsed")) {
            toggleButtons.forEach((toggleButton) => {
                const title = toggleButton.dataset.titleExpand;
                const icon = toggleButton.querySelector("fa-icon");
                if (icon) {
                    icon.setIcon("up-right-and-down-left-from-center");
                    toggleButton.title = title;
                }
                else {
                    toggleButton.textContent = title;
                }
            });
            if (event instanceof Event) {
                // negative top value means the upper boundary is not within the viewport
                const top = container.getBoundingClientRect().top;
                if (top < 0) {
                    let y = window.pageYOffset + (top - 100);
                    if (y < 0) {
                        y = 0;
                    }
                    window.scrollTo(window.pageXOffset, y);
                }
            }
        }
        else {
            toggleButtons.forEach((toggleButton) => {
                const title = toggleButton.dataset.titleCollapse;
                const icon = toggleButton.querySelector("fa-icon");
                if (icon) {
                    icon.setIcon("down-left-and-up-right-to-center");
                    toggleButton.title = title;
                }
                else {
                    toggleButton.textContent = title;
                }
            });
        }
    }
    function observe() {
        document.querySelectorAll(".jsCollapsibleBbcode").forEach((container) => {
            // find the matching toggle button
            const toggleButtons = Array.from(container.querySelectorAll(".toggleButton:not(.jsToggleButtonEnabled)")).filter((button) => {
                return button.closest(".jsCollapsibleBbcode") === container;
            });
            const overflowContainer = container.querySelector(".collapsibleBbcodeOverflow") || container;
            if (toggleButtons.length > 0) {
                initContainer(container, toggleButtons, overflowContainer);
            }
            container.classList.remove("jsCollapsibleBbcode");
        });
    }
});
