/**
 * Helper functions for containers that handle interactions.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isOwnInteraction = isOwnInteraction;
    /**
     * Returns true if the element belongs to the interactions of the given
     * container rather than to a nested container that handles its own
     * interactions, such as an embedded poll widget.
     */
    function isOwnInteraction(container, element) {
        const nested = element.closest("[data-interaction-container]");
        return nested === null || nested === container || !container.contains(nested);
    }
});
