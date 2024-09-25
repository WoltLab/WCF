/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "../../Ajax", "../../Component/Dialog", "../../Helper/Selector"], function (require, exports, Ajax_1, Dialog_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function showDetails(objectID, objectType) {
        const response = (await (0, Ajax_1.dboAction)("getReactionDetails", "wcf\\data\\reaction\\ReactionAction")
            .payload({
            data: {
                objectID,
                objectType,
            },
        })
            .dispatch());
        const dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.template).withoutControls();
        dialog.show(response.title);
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("woltlab-core-reaction-summary", (element) => {
            element.addEventListener("showDetails", () => {
                void showDetails(element.objectId, element.objectType);
            });
        });
    }
});
