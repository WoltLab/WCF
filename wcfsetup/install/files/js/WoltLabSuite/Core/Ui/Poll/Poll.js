/**
 * Handles the poll UI.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Poll/Poll
 * @since   5.5
 */
define(["require", "exports", "tslib", "../../Dom/Change/Listener", "../../Dom/Util", "../../StringUtil", "./View/Participants", "./View/Results", "./View/Vote", "./Vote"], function (require, exports, tslib_1, Listener_1, Util_1, StringUtil_1, Participants_1, Results_1, Vote_1, Vote_2) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupAll = exports.Poll = exports.PollViews = void 0;
    Listener_1 = (0, tslib_1.__importDefault)(Listener_1);
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
    class Poll {
        constructor(pollID) {
            this.voteView = undefined;
            this.resultsView = undefined;
            this.participants = undefined;
            this.voteHandler = undefined;
            this.views = new Map();
            const poll = document.getElementById(`poll${pollID}`);
            if (poll === null) {
                throw new Error(`Could not find poll with id "${pollID}".`);
            }
            this.element = poll;
            this.pollId = pollID;
            this.getInnerContainer()
                .querySelectorAll("div")
                .forEach((element) => {
                if (element.dataset.key) {
                    this.views.set(element.dataset.key, element);
                }
            });
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
        getElement() {
            return this.element;
        }
        hasView(key) {
            return this.views.has(key);
        }
        getView(key) {
            if (!this.hasView(key)) {
                throw new Error(`The view "${key}" is unknown for poll "${this.pollId}".`);
            }
            return this.views.get(key);
        }
        displayView(key) {
            var _a, _b, _c;
            if (!this.hasView(key)) {
                throw new Error(`The view "${key}" is unknown for poll "${this.pollId}".`);
            }
            this.views.forEach((view) => {
                view.hidden = true;
            });
            this.views.get(key).hidden = false;
            (_a = this.voteView) === null || _a === void 0 ? void 0 : _a.checkVisibility(key);
            (_b = this.resultsView) === null || _b === void 0 ? void 0 : _b.checkVisibility(key);
            (_c = this.voteHandler) === null || _c === void 0 ? void 0 : _c.checkVisibility(key);
            if (!this.participants && this.canViewParticipants()) {
                this.participants = new Participants_1.default(this);
                this.participants.showButton();
            }
        }
        addView(key, html) {
            const container = document.createElement("div");
            container.dataset.key = key;
            container.hidden = true;
            Util_1.default.setInnerHtml(container, html);
            this.getInnerContainer().append(container);
            if (this.views.has(key)) {
                this.views.get(key).remove();
            }
            this.views.set(key, container);
            if (key === PollViews.vote) {
                this.voteHandler.initSelects();
            }
        }
        canViewParticipants() {
            return this.canViewResults && this.isPublic;
        }
        getInnerContainer() {
            const innerContainer = this.element.querySelector(".pollInnerContainer");
            if (!innerContainer) {
                throw new Error(`Could not find inner container for poll "${this.pollId}"`);
            }
            return innerContainer;
        }
        changeTotalVotes(votes, tooltip) {
            const badge = this.getElement().querySelector(".pollTotalVotesBadge");
            if (!badge) {
                throw new Error(`Could not find total votes badge.`);
            }
            badge.textContent = (0, StringUtil_1.formatNumeric)(votes);
            badge.dataset.tooltip = tooltip;
        }
        get isPublic() {
            return this.element.dataset.isPublic === "true";
        }
        get maxVotes() {
            return parseInt(this.element.dataset.maxVotes, 10);
        }
        get question() {
            return this.element.dataset.question;
        }
        get canVote() {
            return this.element.dataset.canVote === "true";
        }
        set canVote(canVote) {
            this.element.dataset.canVote = canVote ? "true" : "false";
        }
        get canViewResults() {
            return this.element.dataset.canViewResult === "true";
        }
        set canViewResults(canViewResults) {
            this.element.dataset.canViewResult = canViewResults ? "true" : "false";
        }
    }
    exports.Poll = Poll;
    const polls = new Map();
    function setup() {
        document.querySelectorAll(".pollContainer").forEach((pollElement) => {
            if (!pollElement.dataset.pollId) {
                throw new Error("Invalid poll element given. Missing pollID.");
            }
            const pollID = parseInt(pollElement.dataset.pollId, 10);
            if (!polls.has(pollID)) {
                polls.set(pollID, new Poll(pollID));
            }
        });
    }
    function setupAll() {
        Listener_1.default.add("WoltLabSuite/Core/Ui/Poll/Manager/Poll", () => {
            setup();
        });
        setup();
    }
    exports.setupAll = setupAll;
    exports.default = setupAll;
});
