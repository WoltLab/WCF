/**
 * Handles the creation of page notices and refreshes the list of notices
 * after a notice has been added, edited or deleted.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/PageNotices/RenderPageNotices", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, RenderPageNotices_1, Dialog_1, Snackbar_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    async function refresh(container) {
        const pageId = parseInt(container.dataset.pageId, 10);
        const pageObjectId = container.dataset.pageObjectId ? parseInt(container.dataset.pageObjectId, 10) : undefined;
        const { template } = await (0, RenderPageNotices_1.renderPageNotices)(pageId, pageObjectId);
        Util_1.default.setInnerHtml(container, template);
    }
    async function addNotice(container, endpoint) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
        await refresh(container);
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(container) {
        document.querySelectorAll(".jsPageNoticeAdd").forEach((link) => {
            link.addEventListener("click", (event) => {
                event.preventDefault();
                void addNotice(container, link.dataset.endpoint);
            });
        });
        // The standalone interaction buttons would otherwise reload the page or
        // redirect, the listeners use the capture phase to intercept the events
        // before they reach the buttons.
        for (const eventName of ["interaction:invalidate", "interaction:remove"]) {
            container.addEventListener(eventName, (event) => {
                event.stopPropagation();
                void refresh(container);
            }, { capture: true });
        }
    }
});
