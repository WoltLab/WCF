/**
 * Automatically reloads the page if `.jsReloadPageWhenEmpty` elements contain no child elements.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Dom/Change/Listener"], function (require, exports, tslib_1, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            const target = mutation.target;
            if (target.childElementCount === 0) {
                window.location.reload();
            }
            else {
                // Some elements may contain items, like a head row, that should not be considered when checking
                // whether the list is empty.
                const isEmpty = Array.from(target.children).every((el) => el.dataset.reloadPageWhenEmpty === "ignore");
                if (isEmpty) {
                    window.location.reload();
                }
            }
        });
    });
    function observeElements() {
        document.querySelectorAll(".jsReloadPageWhenEmpty").forEach((el) => {
            el.classList.remove("jsReloadPageWhenEmpty");
            observer.observe(el, {
                childList: true,
            });
        });
    }
    function setup() {
        observeElements();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Empty", () => observeElements());
    }
});
