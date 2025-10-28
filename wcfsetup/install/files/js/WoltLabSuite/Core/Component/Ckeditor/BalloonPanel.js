/**
 * Dynamically adjusts the position of mention UIs that are placed outside of
 * the viewport due to a case of unconditional positioning when the panel is
 * already visible.
 *
 * See https://github.com/ckeditor/ckeditor5/issues/19271
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PageOverlay"], function (require, exports, PageOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    const trackedBalloons = new WeakSet();
    function watch(balloon) {
        console.log("Tracking", balloon);
        trackedBalloons.add(balloon);
        const observer = new MutationObserver(() => {
            verifyHorizontalPosition(balloon);
        });
        observer.observe(balloon, {
            attributes: true,
            attributeFilter: ["style"],
        });
    }
    function verifyHorizontalPosition(balloon) {
        const position = balloon.getBoundingClientRect();
        if (position.left < 0) {
            balloon.style.setProperty("left", "0");
        }
        else if (position.right > window.innerWidth) {
            const leftMostPosition = Math.min(window.innerWidth - position.width, 0);
            balloon.style.setProperty("left", `${leftMostPosition}px`);
        }
    }
    let hasWatcher = false;
    function setup() {
        if (hasWatcher) {
            return;
        }
        hasWatcher = true;
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.addedNodes.length === 0) {
                    continue;
                }
                for (const node of mutation.addedNodes) {
                    if (node instanceof HTMLElement && node.classList.contains("ck-balloon-panel") && !trackedBalloons.has(node)) {
                        watch(node);
                    }
                }
            }
        });
        observer.observe((0, PageOverlay_1.getPageOverlayContainer)(), {
            childList: true,
            subtree: true,
        });
    }
});
