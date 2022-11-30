/**
 * Handles the reaction summary details dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/Reaction/SummaryDetails
 * @since 6.0
 */
define(["require", "exports", "../../Ajax", "../../Component/Dialog"], function (require, exports, Ajax_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.SummaryDetails = void 0;
    class SummaryDetails {
        #objectType;
        #objectId;
        constructor(objectType, objectId) {
            this.#objectType = objectType;
            this.#objectId = objectId;
            const component = document.querySelector(`woltlab-core-reaction-summary[object-type="${this.#objectType}"][object-id="${this.#objectId}"]`);
            component?.addEventListener("showDetails", () => {
                void this.#loadDetails();
            });
        }
        async #loadDetails() {
            const response = (await (0, Ajax_1.dboAction)("getReactionDetails", "wcf\\data\\reaction\\ReactionAction")
                .payload({
                data: {
                    objectID: this.#objectId,
                    objectType: this.#objectType,
                },
            })
                .dispatch());
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(response.template).withoutControls();
            dialog.show(response.title);
        }
    }
    exports.SummaryDetails = SummaryDetails;
    exports.default = SummaryDetails;
});
