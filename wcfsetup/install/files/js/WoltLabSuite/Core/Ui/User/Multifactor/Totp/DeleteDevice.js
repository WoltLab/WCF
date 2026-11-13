/**
 * Asks for confirmation before deleting a TOTP device.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since  6.3
 * @woltlabExcludeBundle  all
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Confirmation"], function (require, exports, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup(container) {
        // The listener is registered in the capture phase of the container to run
        // before any listeners of the button itself, e.g. the `IsNotClicked` dependency.
        container.addEventListener("click", (event) => {
            const button = event.target.closest(".jsTotpDeviceDelete");
            if (button === null) {
                return;
            }
            if (button.dataset.confirmed === "1") {
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            void (0, Confirmation_1.confirmationFactory)()
                .delete(button.dataset.deviceName)
                .then((result) => {
                if (!result) {
                    return;
                }
                button.dataset.confirmed = "1";
                button.click();
            });
        }, { capture: true });
    }
});
