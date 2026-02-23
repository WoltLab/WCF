/**
 * Handles the reaction list in the user profile.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Users/Reactions/RenderUserReactions"], function (require, exports, tslib_1, Util_1, PromiseMutex_1, Language_1, RenderUserReactions_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    async function loadMore(container) {
        const result = await (0, RenderUserReactions_1.renderUserReactions)(parseInt(container.dataset.userId || "0"), container.dataset.targetType || "received", parseInt(container.dataset.lastLikeTime || "0"), parseInt(container.dataset.reactionTypeId || "0"));
        const response = result.unwrap();
        if ("template" in response) {
            container.dataset.lastLikeTime = response.lastLikeTime.toString();
            const showMoreButton = container.querySelector(".likeList__showMoreButton");
            const fragment = Util_1.default.createFragmentFromHtml(response.template);
            container.insertBefore(fragment, showMoreButton);
            showMoreButton.querySelector("button").hidden = false;
            showMoreButton.querySelector("small").hidden = true;
        }
        else {
            const showMoreButton = container.querySelector(".likeList__showMoreButton");
            showMoreButton.querySelector("button").hidden = true;
            showMoreButton.querySelector("small").hidden = false;
        }
    }
    async function reload(container) {
        container.querySelectorAll(":scope > li:not(:first-child):not(:last-child)").forEach((el) => el.remove());
        container.dataset.lastLikeTime = "0";
        const showMoreButton = container.querySelector(".likeList__showMoreButton");
        if (showMoreButton !== null) {
            showMoreButton.querySelector("button").hidden = false;
            showMoreButton.querySelector("small").hidden = true;
        }
        await loadMore(container);
    }
    function initShowMoreButton(container) {
        if (container.querySelector(".likeList__showMoreButton")) {
            return;
        }
        const li = document.createElement("li");
        li.classList.add("likeList__showMoreButton");
        container.append(li);
        const button = document.createElement("button");
        button.type = "button";
        button.classList.add("button", "small");
        button.textContent = (0, Language_1.getPhrase)("wcf.like.reaction.more");
        li.append(button);
        const small = document.createElement("small");
        small.textContent = (0, Language_1.getPhrase)("wcf.like.reaction.noMoreEntries");
        small.hidden = true;
        li.append(small);
        const hasItems = container.querySelectorAll(":scope > li").length > 2;
        if (!hasItems) {
            button.hidden = true;
            small.hidden = false;
        }
        button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => loadMore(container)));
    }
    function initTargetTypeButtons(container) {
        container.querySelectorAll("[data-target-type]").forEach((button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => {
                const targetType = button.dataset.targetType;
                if (targetType === container.dataset.targetType) {
                    return Promise.resolve();
                }
                container.querySelector("[data-target-type].active").classList.remove("active");
                button.classList.add("active");
                container.dataset.targetType = targetType;
                return reload(container);
            }));
        });
    }
    function initReactionTypeButtons(container) {
        container.querySelectorAll("[data-reaction-type-id]").forEach((button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => {
                const reactionTypeID = button.dataset.reactionTypeId;
                const activeButton = container.querySelector("[data-reaction-type-id].active");
                if (activeButton) {
                    activeButton.classList.remove("active");
                }
                if (container.dataset.reactionTypeId !== reactionTypeID) {
                    button.classList.add("active");
                    container.dataset.reactionTypeId = reactionTypeID;
                }
                else {
                    container.dataset.reactionTypeId = "0";
                }
                return reload(container);
            }));
        });
    }
    function setup(container) {
        initShowMoreButton(container);
        initTargetTypeButtons(container);
        initReactionTypeButtons(container);
    }
});
