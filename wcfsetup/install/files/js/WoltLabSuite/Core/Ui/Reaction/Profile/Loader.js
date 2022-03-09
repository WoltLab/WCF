/**
 * Handles the reaction list in the user profile.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Reaction/Profile/Loader
 * @since       5.2
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Dom/Util", "../../../Language"], function (require, exports, tslib_1, Ajax, Core, Util_1, Language) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    class UiReactionProfileLoader {
        /**
         * Initializes a new ReactionListLoader object.
         */
        constructor(userID) {
            this._reactionTypeID = null;
            this._targetType = "received";
            this._container = document.getElementById("likeList");
            this._userID = userID;
            this._options = {
                parameters: {},
            };
            if (!this._userID) {
                throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'userID' given.");
            }
            const loadButtonList = document.createElement("li");
            loadButtonList.className = "likeListMore showMore";
            this._noMoreEntries = document.createElement("small");
            this._noMoreEntries.innerHTML = Language.get("wcf.like.reaction.noMoreEntries");
            this._noMoreEntries.style.display = "none";
            loadButtonList.appendChild(this._noMoreEntries);
            this._loadButton = document.createElement("button");
            this._loadButton.className = "small";
            this._loadButton.innerHTML = Language.get("wcf.like.reaction.more");
            this._loadButton.addEventListener("click", () => this._loadReactions());
            this._loadButton.style.display = "none";
            loadButtonList.appendChild(this._loadButton);
            this._container.appendChild(loadButtonList);
            if (document.querySelectorAll("#likeList > li").length === 2) {
                this._noMoreEntries.style.display = "";
            }
            else {
                this._loadButton.style.display = "";
            }
            this._setupReactionTypeButtons();
            this._setupTargetTypeButtons();
        }
        /**
         * Set up the reaction type buttons.
         */
        _setupReactionTypeButtons() {
            document.querySelectorAll("#reactionType .button").forEach((element) => {
                element.addEventListener("click", () => this._changeReactionTypeValue(~~element.dataset.reactionTypeId));
            });
        }
        /**
         * Set up the target type buttons.
         */
        _setupTargetTypeButtons() {
            document.querySelectorAll("#likeType .button").forEach((element) => {
                element.addEventListener("click", () => this._changeTargetType(element.dataset.likeType));
            });
        }
        /**
         * Changes the reaction target type (given or received) and reload the entire element.
         */
        _changeTargetType(targetType) {
            if (targetType !== "given" && targetType !== "received") {
                throw new Error("[WoltLabSuite/Core/Ui/Reaction/Profile/Loader] Invalid parameter 'targetType' given.");
            }
            if (targetType !== this._targetType) {
                // remove old active state
                document.querySelector("#likeType .button.active").classList.remove("active");
                // add active status to new button
                document.querySelector(`#likeType .button[data-like-type="${targetType}"]`).classList.add("active");
                this._targetType = targetType;
                this._reload();
            }
        }
        /**
         * Changes the reaction type value and reload the entire element.
         */
        _changeReactionTypeValue(reactionTypeID) {
            // remove old active state
            const activeButton = document.querySelector("#reactionType .button.active");
            if (activeButton) {
                activeButton.classList.remove("active");
            }
            if (this._reactionTypeID !== reactionTypeID) {
                // add active status to new button
                document
                    .querySelector(`#reactionType .button[data-reaction-type-id="${reactionTypeID}"]`)
                    .classList.add("active");
                this._reactionTypeID = reactionTypeID;
            }
            else {
                this._reactionTypeID = null;
            }
            this._reload();
        }
        /**
         * Handles reload.
         */
        _reload() {
            document.querySelectorAll("#likeList > li:not(:first-child):not(:last-child)").forEach((el) => el.remove());
            this._container.dataset.lastLikeTime = "0";
            this._loadReactions();
        }
        /**
         * Load a list of reactions.
         */
        _loadReactions() {
            this._options.parameters.userID = this._userID;
            this._options.parameters.lastLikeTime = ~~this._container.dataset.lastLikeTime;
            this._options.parameters.targetType = this._targetType;
            this._options.parameters.reactionTypeID = ~~this._reactionTypeID;
            Ajax.api(this, {
                parameters: this._options.parameters,
            });
        }
        _ajaxSuccess(data) {
            if (data.returnValues.template) {
                document
                    .querySelector("#likeList > li:nth-last-child(1)")
                    .insertAdjacentHTML("beforebegin", data.returnValues.template);
                this._container.dataset.lastLikeTime = data.returnValues.lastLikeTime.toString();
                Util_1.default.hide(this._noMoreEntries);
                Util_1.default.show(this._loadButton);
            }
            else {
                Util_1.default.show(this._noMoreEntries);
                Util_1.default.hide(this._loadButton);
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "load",
                    className: "\\wcf\\data\\reaction\\ReactionAction",
                },
            };
        }
    }
    Core.enableLegacyInheritance(UiReactionProfileLoader);
    return UiReactionProfileLoader;
});
