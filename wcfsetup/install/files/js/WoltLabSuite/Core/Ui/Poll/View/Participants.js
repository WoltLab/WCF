/**
 * Abstract implementation for participants views.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
define(["require", "exports", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, PromiseMutex_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Participants = void 0;
    class Participants {
        pollManager;
        button;
        constructor(manager) {
            this.pollManager = manager;
            const button = this.pollManager.getElement().querySelector(".showPollParticipantsButton");
            if (!button) {
                throw new Error(`Could not find button with selector "showPollParticipantsButton" for poll "${this.pollManager.pollId}"`);
            }
            this.button = button;
            this.button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => {
                return (0, Dialog_1.dialogFactory)()
                    .usingListView()
                    .fromPreset(this.pollManager.question, "wcf\\system\\listView\\user\\PollParticipantListView", new Map([["pollID", this.pollManager.pollId.toString()]]));
            }));
        }
        showButton() {
            this.button.hidden = false;
        }
        hideButton() {
            this.button.hidden = true;
        }
    }
    exports.Participants = Participants;
    exports.default = Participants;
});
