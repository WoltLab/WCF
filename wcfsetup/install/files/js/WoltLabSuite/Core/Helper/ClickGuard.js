/**
 * Prevents concurrent runs of the event handler for the click event by blocking
 * the event while a previous call is still running.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.clickGuard = void 0;
    function clickGuard(element, eventHandler) {
        let pending = false;
        element.addEventListener("click", (event) => {
            if (pending) {
                event.preventDefault();
                return;
            }
            pending = true;
            void eventHandler(event)
                .then(() => {
                pending = false;
            }, () => {
                pending = false;
            })
                .catch((reason) => {
                pending = false;
                throw reason;
            });
        });
    }
    exports.clickGuard = clickGuard;
});
