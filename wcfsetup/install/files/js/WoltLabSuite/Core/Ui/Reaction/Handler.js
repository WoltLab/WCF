/**
 * Provides interface elements to use reactions.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Change/Listener", "../../Dom/Util", "../Alignment", "../CloseOverlay", "../Screen", "focus-trap"], function (require, exports, tslib_1, Ajax, Core, Listener_1, Util_1, UiAlignment, CloseOverlay_1, UiScreen, focus_trap_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    const availableReactions = Object.values(window.REACTION_TYPES);
    class UiReactionHandler {
        activeButton = undefined;
        _cache = new Map();
        focusTrap = undefined;
        _containers = new Map();
        _options;
        _objects = new Map();
        _objectType;
        _popoverCurrentObjectId = 0;
        _popover;
        _popoverContent;
        /**
         * Initializes the reaction handler.
         */
        constructor(objectType, opts) {
            if (!opts.containerSelector) {
                throw new Error("[WoltLabSuite/Core/Ui/Reaction/Handler] Expected a non-empty string for option 'containerSelector'.");
            }
            this._objectType = objectType;
            this._popover = null;
            this._popoverContent = null;
            this._options = Core.extend({
                // selectors
                buttonSelector: ".reactButton",
                containerSelector: "",
                isButtonGroupNavigation: false,
                isSingleItem: false,
                // other stuff
                parameters: {
                    data: {},
                },
            }, opts);
            this.initReactButtons();
            Listener_1.default.add(`WoltLabSuite/Core/Ui/Reaction/Handler-${objectType}`, () => this.initReactButtons());
            CloseOverlay_1.default.add("WoltLabSuite/Core/Ui/Reaction/Handler", () => this._closePopover());
        }
        /**
         * Initializes all applicable react buttons with the given selector.
         */
        initReactButtons() {
            let triggerChange = false;
            document.querySelectorAll(this._options.containerSelector).forEach((element) => {
                const elementId = Util_1.default.identify(element);
                if (this._containers.has(elementId)) {
                    return;
                }
                const objectId = ~~element.dataset.objectId;
                const elementData = {
                    reactButton: null,
                    objectId: objectId,
                    element: element,
                };
                this._containers.set(elementId, elementData);
                this._initReactButton(element, elementData);
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
         * Initializes a specific react button.
         */
        _initReactButton(element, elementData) {
            if (this._options.isSingleItem) {
                elementData.reactButton = document.querySelector(this._options.buttonSelector);
            }
            else {
                elementData.reactButton = element.querySelector(this._options.buttonSelector);
            }
            if (elementData.reactButton === null) {
                // The element may have no react button.
                return;
            }
            if (availableReactions.length === 1) {
                const reaction = availableReactions[0];
                elementData.reactButton.title = reaction.title;
                const textSpan = elementData.reactButton.querySelector(".invisible");
                textSpan.textContent = reaction.title;
            }
            elementData.reactButton.setAttribute("role", "button");
            if (availableReactions.length > 1) {
                elementData.reactButton.setAttribute("aria-haspopup", "true");
                elementData.reactButton.setAttribute("aria-expanded", "false");
            }
            elementData.reactButton.addEventListener("click", (ev) => {
                this._toggleReactPopover(elementData.objectId, elementData.reactButton, ev);
            });
            elementData.reactButton.addEventListener("keydown", (event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    this._toggleReactPopover(elementData.objectId, elementData.reactButton, null);
                }
            });
        }
        _updateReactButton(objectID, reactionTypeID) {
            this._objects.get(objectID).forEach((elementData) => {
                if (elementData.reactButton !== null) {
                    if (reactionTypeID) {
                        elementData.reactButton.classList.add("active");
                        elementData.reactButton.dataset.reactionTypeId = reactionTypeID.toString();
                    }
                    else {
                        elementData.reactButton.dataset.reactionTypeId = "0";
                        elementData.reactButton.classList.remove("active");
                    }
                }
            });
        }
        _markReactionAsActive() {
            let reactionTypeID = null;
            for (const element of this._objects.get(this._popoverCurrentObjectId)) {
                if (element.reactButton !== null) {
                    reactionTypeID = ~~element.reactButton.dataset.reactionTypeId;
                }
            }
            if (reactionTypeID === null) {
                throw new Error("Unable to find react button for current popover.");
            }
            //  Clear the old active state.
            const popover = this._getPopover();
            popover.querySelectorAll(".reactionTypeButton.active").forEach((element) => {
                element.classList.remove("active");
                element.removeAttribute("aria-selected");
            });
            const scrollableContainer = popover.querySelector(".reactionPopoverContent");
            if (reactionTypeID) {
                const reactionTypeButton = popover.querySelector(`.reactionTypeButton[data-reaction-type-id="${reactionTypeID}"]`);
                reactionTypeButton.classList.add("active");
                reactionTypeButton.setAttribute("aria-selected", "true");
                if (~~reactionTypeButton.dataset.isAssignable === 0) {
                    Util_1.default.show(reactionTypeButton);
                }
                this._scrollReactionIntoView(scrollableContainer, reactionTypeButton);
            }
            else {
                // The "first" reaction is positioned as close as possible to the toggle button,
                // which means that we need to scroll the list to the bottom if the popover is
                // displayed above the toggle button.
                if (UiScreen.is("screen-xs")) {
                    if (popover.classList.contains("inverseOrder")) {
                        scrollableContainer.scrollTop = 0;
                    }
                    else {
                        scrollableContainer.scrollTop = scrollableContainer.scrollHeight - scrollableContainer.clientHeight;
                    }
                }
            }
        }
        _scrollReactionIntoView(scrollableContainer, reactionTypeButton) {
            // Do not scroll if the button is located in the upper 75%.
            if (reactionTypeButton.offsetTop < scrollableContainer.clientHeight * 0.75) {
                scrollableContainer.scrollTop = 0;
            }
            else {
                // `Element.scrollTop` permits arbitrary values and will always clamp them to
                // the maximum possible offset value. We can abuse this behavior by calculating
                // the values to place the selected reaction in the center of the popover,
                // regardless of the offset being out of range.
                scrollableContainer.scrollTop =
                    reactionTypeButton.offsetTop + reactionTypeButton.clientHeight / 2 - scrollableContainer.clientHeight / 2;
            }
        }
        /**
         * Toggle the visibility of the react popover.
         */
        _toggleReactPopover(objectId, element, event) {
            if (event !== null) {
                event.preventDefault();
                event.stopPropagation();
            }
            if (availableReactions.length === 1) {
                const reaction = availableReactions[0];
                this._popoverCurrentObjectId = objectId;
                this._react(reaction.reactionTypeID);
            }
            else {
                if (this._popoverCurrentObjectId === 0 || this._popoverCurrentObjectId !== objectId) {
                    this._openReactPopover(objectId, element);
                }
                else {
                    this._closePopover();
                }
            }
        }
        /**
         * Opens the react popover for a specific react button.
         */
        _openReactPopover(objectId, element) {
            if (this._popoverCurrentObjectId !== 0) {
                this._closePopover();
            }
            this._popoverCurrentObjectId = objectId;
            UiAlignment.set(this._getPopover(), element, {
                pointer: true,
                horizontal: this._options.isButtonGroupNavigation ? "left" : "center",
                vertical: UiScreen.is("screen-xs") ? "bottom" : "top",
            });
            if (this._options.isButtonGroupNavigation) {
                element.closest("nav").style.setProperty("opacity", "1", "");
            }
            const popover = this._getPopover();
            // The popover could be rendered below the input field on mobile, in which case
            // the "first" button is displayed at the bottom and thus farthest away. Reversing
            // the display order will restore the logic by placing the "first" button as close
            // to the react button as possible.
            const inverseOrder = popover.style.getPropertyValue("bottom") === "auto";
            if (inverseOrder) {
                popover.classList.add("inverseOrder");
            }
            else {
                popover.classList.remove("inverseOrder");
            }
            this._markReactionAsActive();
            this._rebuildOverflowIndicator();
            popover.classList.remove("forceHide");
            popover.classList.add("active");
            this.activeButton = element;
            if (availableReactions.length > 1) {
                this.activeButton.setAttribute("aria-expanded", "true");
            }
            this.getFocusTrap().activate();
        }
        /**
         * Returns the react popover element.
         */
        _getPopover() {
            if (this._popover == null) {
                this._popover = document.createElement("div");
                this._popover.className = "reactionPopover forceHide";
                this._popoverContent = document.createElement("div");
                this._popoverContent.className = "reactionPopoverContent";
                const popoverContentHTML = document.createElement("ul");
                popoverContentHTML.className = "reactionTypeButtonList";
                this._getSortedReactionTypes().forEach((reactionType) => {
                    const reactionTypeItem = document.createElement("li");
                    reactionTypeItem.tabIndex = 0;
                    reactionTypeItem.setAttribute("role", "button");
                    reactionTypeItem.className = "reactionTypeButton jsTooltip";
                    reactionTypeItem.dataset.reactionTypeId = reactionType.reactionTypeID.toString();
                    reactionTypeItem.dataset.title = reactionType.title;
                    reactionTypeItem.dataset.isAssignable = reactionType.isAssignable.toString();
                    reactionTypeItem.title = reactionType.title;
                    const reactionTypeItemSpan = document.createElement("span");
                    reactionTypeItemSpan.className = "reactionTypeButtonTitle";
                    reactionTypeItemSpan.innerHTML = reactionType.title;
                    reactionTypeItem.innerHTML = reactionType.renderedIcon;
                    reactionTypeItem.appendChild(reactionTypeItemSpan);
                    reactionTypeItem.addEventListener("click", () => this._react(reactionType.reactionTypeID));
                    reactionTypeItem.addEventListener("keydown", (ev) => this.keydown(ev));
                    if (!reactionType.isAssignable) {
                        Util_1.default.hide(reactionTypeItem);
                    }
                    popoverContentHTML.appendChild(reactionTypeItem);
                });
                this._popoverContent.appendChild(popoverContentHTML);
                this._popoverContent.addEventListener("scroll", () => this._rebuildOverflowIndicator(), { passive: true });
                this._popover.appendChild(this._popoverContent);
                const pointer = document.createElement("span");
                pointer.className = "elementPointer";
                pointer.appendChild(document.createElement("span"));
                this._popover.appendChild(pointer);
                document.body.appendChild(this._popover);
                Listener_1.default.trigger();
            }
            return this._popover;
        }
        keydown(event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                const activeButton = this.activeButton;
                const reactionTypeItem = event.currentTarget;
                const reactionTypeId = ~~reactionTypeItem.dataset.reactionTypeId;
                this._react(reactionTypeId);
                activeButton.focus();
            }
        }
        _rebuildOverflowIndicator() {
            const popoverContent = this._popoverContent;
            const hasTopOverflow = popoverContent.scrollTop > 0;
            if (hasTopOverflow) {
                popoverContent.classList.add("overflowTop");
            }
            else {
                popoverContent.classList.remove("overflowTop");
            }
            const hasBottomOverflow = popoverContent.scrollTop + popoverContent.clientHeight < popoverContent.scrollHeight;
            if (hasBottomOverflow) {
                popoverContent.classList.add("overflowBottom");
            }
            else {
                popoverContent.classList.remove("overflowBottom");
            }
        }
        /**
         * Sort the reaction types by the showOrder field.
         */
        _getSortedReactionTypes() {
            return availableReactions.sort((a, b) => a.showOrder - b.showOrder);
        }
        /**
         * Closes the react popover.
         */
        _closePopover() {
            if (this._popoverCurrentObjectId !== 0) {
                const popover = this._getPopover();
                popover.classList.remove("active");
                popover
                    .querySelectorAll('.reactionTypeButton[data-is-assignable="0"]')
                    .forEach((el) => Util_1.default.hide(el));
                if (this._options.isButtonGroupNavigation) {
                    this._objects.get(this._popoverCurrentObjectId).forEach((elementData) => {
                        elementData.reactButton.closest("nav").style.cssText = "";
                    });
                }
                if (availableReactions.length > 1) {
                    this.activeButton.setAttribute("aria-expanded", "false");
                }
                this.activeButton = undefined;
                this._popoverCurrentObjectId = 0;
                this.getFocusTrap().deactivate();
            }
        }
        /**
         * React with the given reactionTypeId on an object.
         */
        _react(reactionTypeId) {
            if (~~this._popoverCurrentObjectId === 0) {
                // Double clicking the reaction will cause the first click to go through, but
                // causes the second to fail because the overlay is already closing.
                return;
            }
            this._options.parameters.reactionTypeID = reactionTypeId;
            this._options.parameters.data.objectID = this._popoverCurrentObjectId;
            this._options.parameters.data.objectType = this._objectType;
            Ajax.api(this, {
                parameters: this._options.parameters,
            });
            this._closePopover();
        }
        _ajaxSuccess(data) {
            const objectId = ~~data.returnValues.objectID;
            const reactions = new Map();
            Object.entries(data.returnValues.reactions).forEach(([key, value]) => {
                reactions.set(parseInt(key), value);
            });
            const component = document.querySelector(`woltlab-core-reaction-summary[object-type="${this._objectType}"][object-id="${objectId}"]`);
            component?.setData(reactions, data.returnValues.reactionTypeID);
            this._updateReactButton(objectId, data.returnValues.reactionTypeID);
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "react",
                    className: "\\wcf\\data\\reaction\\ReactionAction",
                },
            };
        }
        getFocusTrap() {
            if (this.focusTrap === undefined) {
                this.focusTrap = (0, focus_trap_1.createFocusTrap)(this._popover, {
                    allowOutsideClick: true,
                    escapeDeactivates: () => {
                        this._closePopover();
                        return false;
                    },
                    preventScroll: true,
                });
            }
            return this.focusTrap;
        }
    }
    return UiReactionHandler;
});
