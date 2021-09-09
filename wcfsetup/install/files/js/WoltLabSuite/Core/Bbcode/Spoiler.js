/**
 * Generic handler for spoiler boxes.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Bbcode/Spoiler
 */
define(["require", "exports", "tslib", "../Core", "../Language", "../Dom/Util"], function (require, exports, tslib_1, Core, Language, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.observe = void 0;
    Core = (0, tslib_1.__importStar)(Core);
    Language = (0, tslib_1.__importStar)(Language);
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    function onClick(event, content, toggleButton) {
        event.preventDefault();
        toggleButton.classList.toggle("active");
        const isActive = toggleButton.classList.contains("active");
        if (isActive) {
            Util_1.default.show(content);
        }
        else {
            Util_1.default.hide(content);
        }
        toggleButton.setAttribute("aria-expanded", isActive ? "true" : "false");
        content.setAttribute("aria-hidden", isActive ? "false" : "true");
        if (!Core.stringToBool(toggleButton.dataset.hasCustomLabel || "")) {
            toggleButton.textContent = Language.get(toggleButton.classList.contains("active") ? "wcf.bbcode.spoiler.hide" : "wcf.bbcode.spoiler.show");
        }
    }
    function observe() {
        const className = "jsSpoilerBox";
        document.querySelectorAll(`.${className}`).forEach((container) => {
            container.classList.remove(className);
            const toggleButton = container.querySelector(".jsSpoilerToggle");
            const content = container.querySelector(".spoilerBoxContent");
            toggleButton.addEventListener("click", (ev) => onClick(ev, content, toggleButton));
        });
    }
    exports.observe = observe;
});
