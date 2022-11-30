/**
 * Handles the reaction summary details dialog.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Reaction/SummaryDetails
 * @since       6.0
 */
define(["require", "exports", "tslib", "../../Ajax", "../Dialog"], function (require, exports, tslib_1, Ajax_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SummaryDetails = void 0;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class SummaryDetails {
        objectType;
        objectId;
        constructor(objectType, objectId) {
            this.objectType = objectType;
            this.objectId = objectId;
            const component = document.querySelector(`woltlab-core-reaction-summary[object-type="${this.objectType}"][object-id="${this.objectId}"]`);
            component?.addEventListener("showDetails", () => {
                void this.loadDetails();
            });
        }
        async loadDetails() {
            const response = (await (0, Ajax_1.dboAction)("getReactionDetails", "wcf\\data\\reaction\\ReactionAction")
                .payload({
                data: {
                    objectID: this.objectId,
                    objectType: this.objectType,
                },
            })
                .dispatch());
            Dialog_1.default.open(this, response.template);
            Dialog_1.default.setTitle(`userReactionOverlay-${this.objectType}`, response.title);
        }
        _dialogSetup() {
            return {
                id: `userReactionOverlay-${this.objectType}`,
                options: {
                    title: "",
                },
                source: null,
            };
        }
    }
    exports.SummaryDetails = SummaryDetails;
});
