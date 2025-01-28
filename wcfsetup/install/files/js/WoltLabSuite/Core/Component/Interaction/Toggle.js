/**
 * Handles a toggle interaction.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Api/PostObject"], function (require, exports, Selector_1, PostObject_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleToggle(checked, enableEndpoint, disableEndpoint) {
        await (0, PostObject_1.postObject)(checked ? enableEndpoint : disableEndpoint);
    }
    function setup(identifier, container) {
        (0, Selector_1.wheneverFirstSeen)(`#${container.id} [data-interaction="${identifier}"]`, (toggleButton) => {
            toggleButton.addEventListener("change", (event) => {
                void handleToggle(event.detail.checked, toggleButton.dataset.enableEndpoint, toggleButton.dataset.disableEndpoint);
            });
        });
    }
});
