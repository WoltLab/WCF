/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Language", "../../Component/Dialog", "../../Helper/Selector", "WoltLabSuite/Core/Helper/PromiseMutex"], function (require, exports, Language_1, Dialog_1, Selector_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-reaction-summary", (element) => {
            element.addEventListener("showDetails", (0, PromiseMutex_1.promiseMutex)(() => {
                return (0, Dialog_1.dialogFactory)()
                    .usingListView()
                    .fromPreset((0, Language_1.getPhrase)("wcf.reactions.summary.title"), "wcf\\system\\listView\\user\\ReactionSummaryDetailsListView", new Map([
                    ["objectID", element.objectId.toString()],
                    ["objectType", element.objectType],
                ]));
            }));
        });
    }
});
