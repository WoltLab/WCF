/**
 * Handles the poll UI.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Manager/Manager
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../../Dom/Util", "../../../StringUtil", "./View/Participants", "./View/Results", "./View/Vote", "./Vote"], function (require, exports, tslib_1, Util_1, StringUtil_1, Participants_1, Results_1, Vote_1, Vote_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Manager = exports.PollViews = void 0;
    Util_1 = (0, tslib_1.__importDefault)(Util_1);
    Participants_1 = (0, tslib_1.__importDefault)(Participants_1);
    Results_1 = (0, tslib_1.__importDefault)(Results_1);
    Vote_1 = (0, tslib_1.__importDefault)(Vote_1);
    Vote_2 = (0, tslib_1.__importDefault)(Vote_2);
    var PollViews;
    (function (PollViews) {
        PollViews["vote"] = "vote";
        PollViews["results"] = "results";
    })(PollViews = exports.PollViews || (exports.PollViews = {}));
    class Manager {
        constructor(pollID, canViewResults, canVote, isPublic, maxVotes, question) {
            this.pollID = pollID;
            this.canViewResults = canViewResults;
            this.canVote = canVote;
            this.isPublic = isPublic;
            this.maxVotes = maxVotes;
            this.question = question;
            const poll = document.getElementById(`poll${pollID}`);
            if (poll === null) {
                throw new Error(`Could not find poll with id "${pollID}".`);
            }
            this.poll = poll;
            if (this.canViewResults) {
                this.resultsView = new Results_1.default(this);
            }
            if (this.canVote) {
                this.voteView = new Vote_1.default(this);
                this.voteHandler = new Vote_2.default(this);
            }
            if (this.canViewParticipants()) {
                this.participants = new Participants_1.default(this);
            }
        }
        getPollContainer() {
            return this.poll;
        }
        changeView(view, html) {
            var _a, _b, _c, _d;
            (_a = this.voteView) === null || _a === void 0 ? void 0 : _a.checkVisibility(view);
            (_b = this.resultsView) === null || _b === void 0 ? void 0 : _b.checkVisibility(view);
            (_c = this.voteHandler) === null || _c === void 0 ? void 0 : _c.checkVisibility(view);
            this.setInnerContainer(html);
            if (view === PollViews.vote) {
                (_d = this.voteHandler) === null || _d === void 0 ? void 0 : _d.initSelects();
            }
            if (!this.participants && this.canViewParticipants()) {
                this.participants = new Participants_1.default(this);
                this.participants.showButton();
            }
        }
        canViewParticipants() {
            return this.canViewResults && this.isPublic;
        }
        getInnerContainer() {
            const innerContainer = this.poll.querySelector(".pollInnerContainer") || null;
            if (!innerContainer) {
                throw new Error(`Could not find inner container for poll "${this.pollID}"`);
            }
            return innerContainer;
        }
        setInnerContainer(html) {
            Util_1.default.setInnerHtml(this.getInnerContainer(), html);
        }
        changeTotalVotes(votes, tooltip) {
            const badge = this.getPollContainer().querySelector(".pollTotalVotesBadge");
            if (!badge) {
                throw new Error(`Could not find total votes badge.`);
            }
            badge.textContent = (0, StringUtil_1.formatNumeric)(votes);
            badge.dataset.tooltip = tooltip;
        }
    }
    exports.Manager = Manager;
    exports.default = Manager;
});
