/**
 * Provides interface elements to use reactions.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Reaction/Handler
 * @since       5.2
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Change/Listener", "../../Dom/Util", "../../Event/Handler", "../../StringUtil", "../Dialog"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Util_1, EventHandler, StringUtil, Dialog_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    StringUtil = tslib_1.__importStar(StringUtil);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class CountButtons {
        /**
         * Initializes the like handler.
         */
        constructor(objectType, opts) {
            this._containers = new Map();
            this._currentObjectId = 0;
            this._objects = new Map();
            if (!opts.containerSelector) {
                throw new Error("[WoltLabSuite/Core/Ui/Reaction/CountButtons] Expected a non-empty string for option 'containerSelector'.");
            }
            this._objectType = objectType;
            this._options = Core.extend({
                // selectors
                summaryListSelector: ".reactionSummaryList",
                containerSelector: "",
                isSingleItem: false,
                // optional parameters
                parameters: {
                    data: {},
                },
            }, opts);
            this.initContainers();
            Listener_1.default.add(`WoltLabSuite/Core/Ui/Reaction/CountButtons-${objectType}`, () => this.initContainers());
        }
        /**
         * Initialises the containers.
         */
        initContainers() {
            let triggerChange = false;
            document.querySelectorAll(this._options.containerSelector).forEach((element) => {
                const elementId = Util_1.default.identify(element);
                if (this._containers.has(elementId)) {
                    return;
                }
                const objectId = ~~element.dataset.objectId;
                const elementData = {
                    reactButton: null,
                    summary: null,
                    objectId: objectId,
                    element: element,
                };
                this._containers.set(elementId, elementData);
                this._initReactionCountButtons(element, elementData);
                const objects = this._objects.get(objectId) || [];
                objects.push(elementData);
                this._objects.set(objectId, objects);
                triggerChange = true;
            });
            if (triggerChange) {
                Listener_1.default.trigger();
            }
        }
        /**
         * Update the count buttons with the given data.
         */
        updateCountButtons(objectId, data) {
            let triggerChange = false;
            this._objects.get(objectId).forEach((elementData) => {
                let summaryList;
                if (this._options.isSingleItem) {
                    summaryList = document.querySelector(this._options.summaryListSelector);
                }
                else {
                    summaryList = elementData.element.querySelector(this._options.summaryListSelector);
                }
                // summary list for the object not found; abort
                if (summaryList === null) {
                    return;
                }
                const existingReactions = new Map(Object.entries(data));
                const sortedElements = new Map();
                summaryList.querySelectorAll(".reactCountButton").forEach((reaction) => {
                    const reactionTypeId = reaction.dataset.reactionTypeId;
                    if (existingReactions.has(reactionTypeId)) {
                        sortedElements.set(reactionTypeId, reaction);
                    }
                    else {
                        // The reaction no longer has any reactions.
                        reaction.remove();
                    }
                });
                const availableReactions = new Map(Object.entries(window.REACTION_TYPES));
                existingReactions.forEach((count, reactionTypeId) => {
                    if (sortedElements.has(reactionTypeId)) {
                        const reaction = sortedElements.get(reactionTypeId);
                        const reactionCount = reaction.querySelector(".reactionCount");
                        reactionCount.innerHTML = StringUtil.shortUnit(count);
                    }
                    else if (availableReactions.has(reactionTypeId)) {
                        const createdElement = document.createElement("span");
                        createdElement.className = "reactCountButton";
                        createdElement.innerHTML = availableReactions.get(reactionTypeId).renderedIcon;
                        createdElement.dataset.reactionTypeId = reactionTypeId;
                        const countSpan = document.createElement("span");
                        countSpan.className = "reactionCount";
                        countSpan.innerHTML = StringUtil.shortUnit(count);
                        createdElement.appendChild(countSpan);
                        summaryList.appendChild(createdElement);
                        triggerChange = true;
                    }
                });
                if (summaryList.childElementCount > 0) {
                    Util_1.default.show(summaryList);
                }
                else {
                    Util_1.default.hide(summaryList);
                }
            });
            if (triggerChange) {
                Listener_1.default.trigger();
            }
        }
        /**
         * Initialized the reaction count buttons.
         */
        _initReactionCountButtons(element, elementData) {
            let summaryList;
            if (this._options.isSingleItem) {
                summaryList = document.querySelector(this._options.summaryListSelector);
            }
            else {
                summaryList = element.querySelector(this._options.summaryListSelector);
            }
            if (summaryList !== null) {
                summaryList.addEventListener("click", (ev) => this._showReactionOverlay(elementData.objectId, ev));
            }
        }
        /**
         * Shows the reaction overly for a specific object.
         */
        _showReactionOverlay(objectId, event) {
            event.preventDefault();
            this._currentObjectId = objectId;
            this._showOverlay();
        }
        /**
         * Shows a specific page of the current opened reaction overlay.
         */
        _showOverlay() {
            this._options.parameters.data.containerID = `${this._objectType}-${this._currentObjectId}`;
            this._options.parameters.data.objectID = this._currentObjectId;
            this._options.parameters.data.objectType = this._objectType;
            Ajax.api(this, {
                parameters: this._options.parameters,
            });
        }
        _ajaxSuccess(data) {
            EventHandler.fire("com.woltlab.wcf.ReactionCountButtons", "openDialog", data);
            Dialog_1.default.open(this, data.returnValues.template);
            Dialog_1.default.setTitle("userReactionOverlay-" + this._objectType, data.returnValues.title);
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getReactionDetails",
                    className: "\\wcf\\data\\reaction\\ReactionAction",
                },
            };
        }
        _dialogSetup() {
            return {
                id: `userReactionOverlay-${this._objectType}`,
                options: {
                    title: "",
                },
                source: null,
            };
        }
    }
    Core.enableLegacyInheritance(CountButtons);
    return CountButtons;
});
