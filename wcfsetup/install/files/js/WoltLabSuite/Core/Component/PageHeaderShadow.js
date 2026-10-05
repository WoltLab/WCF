/**
 * Marks the bar of the `system_pageHeader` with `data-stuck` while it sticks to the top,
 * the styles show a shadow then.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup() {
        const header = document.getElementById("pageHeader");
        // The auth flow keeps the classic header, which does not stick.
        if (header === null || header.classList.contains("pageHeader--authFlow")) {
            return;
        }
        // CSS cannot tell a stuck element apart, a sentinel right above the bar leaves the
        // viewport exactly when the bar starts to stick.
        const sentinel = document.createElement("div");
        sentinel.classList.add("pageHeaderSentinel");
        sentinel.setAttribute("aria-hidden", "true");
        header.before(sentinel);
        new IntersectionObserver(([entry]) => {
            header.toggleAttribute("data-stuck", !entry.isIntersecting && entry.boundingClientRect.top < 0);
        }).observe(sentinel);
    }
});
