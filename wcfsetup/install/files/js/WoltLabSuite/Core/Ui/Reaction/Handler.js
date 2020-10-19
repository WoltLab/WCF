/**
 * Provides interface elements to use reactions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Reaction/Handler
 * @since       5.2
 */
define([
    'Ajax',
    'Core',
    'Dictionary',
    'Dom/ChangeListener',
    'Dom/Util',
    'Ui/Alignment',
    'Ui/CloseOverlay',
    'Ui/Screen',
    'WoltLabSuite/Core/Ui/Reaction/CountButtons',
], function (Ajax, Core, Dictionary, DomChangeListener, DomUtil, UiAlignment, UiCloseOverlay, UiScreen, CountButtons) {
    "use strict";
    /**
     * @constructor
     */
    function UiReactionHandler(objectType, options) { this.init(objectType, options); }
    UiReactionHandler.prototype = {
        /**
         * Initializes the reaction handler.
         *
         * @param	{string}	objectType	object type
         * @param	{object}	options		initialization options
         */
        init: function (objectType, options) {
            if (options.containerSelector === '') {
                throw new Error("[WoltLabSuite/Core/Ui/Reaction/Handler] Expected a non-empty string for option 'containerSelector'.");
            }
            this._containers = new Dictionary();
            this._objectType = objectType;
            this._cache = new Dictionary();
            this._objects = new Dictionary();
            this._popoverCurrentObjectId = 0;
            this._popover = null;
            this._popoverContent = null;
            this._options = Core.extend({
                // selectors
                buttonSelector: '.reactButton',
                containerSelector: '',
                isButtonGroupNavigation: false,
                isSingleItem: false,
                // other stuff
                parameters: {
                    data: {}
                }
            }, options);
            this.initReactButtons(options, objectType);
            this.countButtons = new CountButtons(this._objectType, this._options);
            DomChangeListener.add('WoltLabSuite/Core/Ui/Reaction/Handler-' + objectType, this.initReactButtons.bind(this));
            UiCloseOverlay.add('WoltLabSuite/Core/Ui/Reaction/Handler', this._closePopover.bind(this));
        },
        /**
         * Initializes all applicable react buttons with the given selector.
         */
        initReactButtons: function () {
            var element, elements = elBySelAll(this._options.containerSelector), elementData, triggerChange = false, objectId;
            for (var i = 0, length = elements.length; i < length; i++) {
                element = elements[i];
                if (this._containers.has(DomUtil.identify(element))) {
                    continue;
                }
                objectId = ~~elData(element, 'object-id');
                elementData = {
                    reactButton: null,
                    objectId: objectId,
                    element: element
                };
                this._containers.set(DomUtil.identify(element), elementData);
                this._initReactButton(element, elementData);
                var objects = [];
                if (this._objects.has(objectId)) {
                    objects = this._objects.get(objectId);
                }
                objects.push(elementData);
                this._objects.set(objectId, objects);
                triggerChange = true;
            }
            if (triggerChange) {
                DomChangeListener.trigger();
            }
        },
        /**
         * Initializes a specific react button.
         */
        _initReactButton: function (element, elementData) {
            if (this._options.isSingleItem) {
                elementData.reactButton = elBySel(this._options.buttonSelector);
            }
            else {
                elementData.reactButton = elBySel(this._options.buttonSelector, element);
            }
            if (elementData.reactButton === null || elementData.reactButton.length === 0) {
                // The element may have no react button. 
                return;
            }
            //noinspection JSUnresolvedVariable
            if (Object.keys(REACTION_TYPES).length === 1) {
                //noinspection JSUnresolvedVariable
                var reaction = REACTION_TYPES[Object.keys(REACTION_TYPES)[0]];
                elementData.reactButton.title = reaction.title;
                var textSpan = elBySel('.invisible', elementData.reactButton);
                textSpan.innerText = reaction.title;
            }
            elementData.reactButton.addEventListener(WCF_CLICK_EVENT, this._toggleReactPopover.bind(this, elementData.objectId, elementData.reactButton));
        },
        _updateReactButton: function (objectID, reactionTypeID) {
            this._objects.get(objectID).forEach(function (elementData) {
                if (elementData.reactButton !== null) {
                    if (reactionTypeID) {
                        elementData.reactButton.classList.add('active');
                        elData(elementData.reactButton, 'reaction-type-id', reactionTypeID);
                    }
                    else {
                        elData(elementData.reactButton, 'reaction-type-id', 0);
                        elementData.reactButton.classList.remove('active');
                    }
                }
            });
        },
        _markReactionAsActive: function () {
            var reactionTypeID = null;
            this._objects.get(this._popoverCurrentObjectId).forEach(function (element) {
                if (element.reactButton !== null) {
                    reactionTypeID = ~~elData(element.reactButton, 'reaction-type-id');
                }
            });
            if (reactionTypeID === null) {
                throw new Error("Unable to find react button for current popover.");
            }
            //  Clear the old active state.
            elBySelAll('.reactionTypeButton.active', this._getPopover(), function (element) {
                element.classList.remove('active');
            });
            var scrollableContainer = elBySel('.reactionPopoverContent', this._getPopover());
            if (reactionTypeID) {
                var reactionTypeButton = elBySel('.reactionTypeButton[data-reaction-type-id="' + reactionTypeID + '"]', this._getPopover());
                reactionTypeButton.classList.add('active');
                if (~~elData(reactionTypeButton, 'is-assignable') === 0) {
                    elShow(reactionTypeButton);
                }
                this._scrollReactionIntoView(scrollableContainer, reactionTypeButton);
            }
            else {
                // The "first" reaction is positioned as close as possible to the toggle button,
                // which means that we need to scroll the list to the bottom if the popover is
                // displayed above the toggle button.
                if (UiScreen.is('screen-xs')) {
                    if (this._getPopover().classList.contains('inverseOrder')) {
                        scrollableContainer.scrollTop = 0;
                    }
                    else {
                        scrollableContainer.scrollTop = scrollableContainer.scrollHeight - scrollableContainer.clientHeight;
                    }
                }
            }
        },
        _scrollReactionIntoView: function (scrollableContainer, reactionTypeButton) {
            // Do not scroll if the button is located in the upper 75%.
            if (reactionTypeButton.offsetTop < scrollableContainer.clientHeight * 0.75) {
                scrollableContainer.scrollTop = 0;
            }
            else {
                // `Element.scrollTop` permits arbitrary values and will always clamp them to
                // the maximum possible offset value. We can abuse this behavior by calculating
                // the values to place the selected reaction in the center of the popover,
                // regardless of the offset being out of range.
                scrollableContainer.scrollTop = reactionTypeButton.offsetTop + reactionTypeButton.clientHeight / 2 - scrollableContainer.clientHeight / 2;
            }
        },
        /**
         * Toggle the visibility of the react popover.
         *
         * @param       {int}           objectId
         * @param       {Element}       element
         * @param       {?Event}        event
         */
        _toggleReactPopover: function (objectId, element, event) {
            if (event !== null) {
                event.preventDefault();
                event.stopPropagation();
            }
            //noinspection JSUnresolvedVariable
            if (Object.keys(REACTION_TYPES).length === 1) {
                //noinspection JSUnresolvedVariable
                var reaction = REACTION_TYPES[Object.keys(REACTION_TYPES)[0]];
                this._popoverCurrentObjectId = objectId;
                this._react(reaction.reactionTypeID);
            }
            else {
                if (this._popoverCurrentObjectId === 0 || this._popoverCurrentObjectId !== objectId) {
                    this._openReactPopover(objectId, element);
                }
                else {
                    this._closePopover(objectId, element);
                }
            }
        },
        /**
         * Opens the react popover for a specific react button.
         *
         * @param       {int}	        objectId		objectId of the element
         * @param       {Element}	element 		container element
         */
        _openReactPopover: function (objectId, element) {
            if (this._popoverCurrentObjectId !== 0) {
                this._closePopover();
            }
            this._popoverCurrentObjectId = objectId;
            UiAlignment.set(this._getPopover(), element, {
                pointer: true,
                horizontal: (this._options.isButtonGroupNavigation) ? 'left' : 'center',
                vertical: UiScreen.is('screen-xs') ? 'bottom' : 'top'
            });
            if (this._options.isButtonGroupNavigation) {
                element.closest('nav').style.setProperty('opacity', '1', '');
            }
            var popover = this._getPopover();
            // The popover could be rendered below the input field on mobile, in which case
            // the "first" button is displayed at the bottom and thus farthest away. Reversing
            // the display order will restore the logic by placing the "first" button as close
            // to the react button as possible.
            var inverseOrder = popover.style.getPropertyValue('bottom') === 'auto';
            popover.classList[inverseOrder ? 'add' : 'remove']('inverseOrder');
            this._markReactionAsActive();
            this._rebuildOverflowIndicator();
            popover.classList.remove('forceHide');
            popover.classList.add('active');
        },
        /**
         * Returns the react popover element.
         *
         * @returns {Element}
         */
        _getPopover: function () {
            if (this._popover == null) {
                this._popover = elCreate('div');
                this._popover.className = 'reactionPopover forceHide';
                this._popoverContent = elCreate('div');
                this._popoverContent.className = 'reactionPopoverContent';
                var popoverContentHTML = elCreate('ul');
                popoverContentHTML.className = 'reactionTypeButtonList';
                var sortedReactionTypes = this._getSortedReactionTypes();
                for (var key in sortedReactionTypes) {
                    if (!sortedReactionTypes.hasOwnProperty(key))
                        continue;
                    var reactionType = sortedReactionTypes[key];
                    var reactionTypeItem = elCreate('li');
                    reactionTypeItem.className = 'reactionTypeButton jsTooltip';
                    elData(reactionTypeItem, 'reaction-type-id', reactionType.reactionTypeID);
                    elData(reactionTypeItem, 'title', reactionType.title);
                    elData(reactionTypeItem, 'is-assignable', ~~reactionType.isAssignable);
                    reactionTypeItem.title = reactionType.title;
                    var reactionTypeItemSpan = elCreate('span');
                    reactionTypeItemSpan.className = 'reactionTypeButtonTitle';
                    reactionTypeItemSpan.innerHTML = reactionType.title;
                    //noinspection JSUnresolvedVariable
                    reactionTypeItem.innerHTML = reactionType.renderedIcon;
                    reactionTypeItem.appendChild(reactionTypeItemSpan);
                    reactionTypeItem.addEventListener(WCF_CLICK_EVENT, this._react.bind(this, reactionType.reactionTypeID));
                    if (!reactionType.isAssignable) {
                        elHide(reactionTypeItem);
                    }
                    popoverContentHTML.appendChild(reactionTypeItem);
                }
                this._popoverContent.appendChild(popoverContentHTML);
                this._popoverContent.addEventListener('scroll', this._rebuildOverflowIndicator.bind(this), { passive: true });
                this._popover.appendChild(this._popoverContent);
                var pointer = elCreate('span');
                pointer.className = 'elementPointer';
                pointer.appendChild(elCreate('span'));
                this._popover.appendChild(pointer);
                document.body.appendChild(this._popover);
                DomChangeListener.trigger();
            }
            return this._popover;
        },
        _rebuildOverflowIndicator: function () {
            var hasTopOverflow = this._popoverContent.scrollTop > 0;
            this._popoverContent.classList[hasTopOverflow ? 'add' : 'remove']('overflowTop');
            var hasBottomOverflow = this._popoverContent.scrollTop + this._popoverContent.clientHeight < this._popoverContent.scrollHeight;
            this._popoverContent.classList[hasBottomOverflow ? 'add' : 'remove']('overflowBottom');
        },
        /**
         * Sort the reaction types by the showOrder field.
         *
         * @returns     {Array}         the reaction types sorted by showOrder
         */
        _getSortedReactionTypes: function () {
            var sortedReactionTypes = [];
            // convert our reaction type object to an array
            //noinspection JSUnresolvedVariable
            for (var key in REACTION_TYPES) {
                //noinspection JSUnresolvedVariable
                if (REACTION_TYPES.hasOwnProperty(key)) {
                    //noinspection JSUnresolvedVariable
                    sortedReactionTypes.push(REACTION_TYPES[key]);
                }
            }
            // sort the array
            sortedReactionTypes.sort(function (a, b) {
                //noinspection JSUnresolvedVariable
                return a.showOrder - b.showOrder;
            });
            return sortedReactionTypes;
        },
        /**
         * Closes the react popover.
         */
        _closePopover: function () {
            if (this._popoverCurrentObjectId !== 0) {
                this._getPopover().classList.remove('active');
                elBySelAll('.reactionTypeButton[data-is-assignable="0"]', this._getPopover(), elHide);
                if (this._options.isButtonGroupNavigation) {
                    this._objects.get(this._popoverCurrentObjectId).forEach(function (elementData) {
                        elementData.reactButton.closest('nav').style.cssText = "";
                    });
                }
                this._popoverCurrentObjectId = 0;
            }
        },
        /**
         * React with the given reactionTypeId on an object.
         *
         * @param       {init}          reactionTypeId
         */
        _react: function (reactionTypeId) {
            if (~~this._popoverCurrentObjectId === 0) {
                // Double clicking the reaction will cause the first click to go through, but
                // causes the second to fail because the overlay is already closing.
                return;
            }
            this._options.parameters.reactionTypeID = reactionTypeId;
            this._options.parameters.data.objectID = this._popoverCurrentObjectId;
            this._options.parameters.data.objectType = this._objectType;
            Ajax.api(this, {
                parameters: this._options.parameters
            });
            this._closePopover();
        },
        _ajaxSuccess: function (data) {
            //noinspection JSUnresolvedVariable
            this.countButtons.updateCountButtons(data.returnValues.objectID, data.returnValues.reactions);
            this._updateReactButton(data.returnValues.objectID, data.returnValues.reactionTypeID);
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'react',
                    className: '\\wcf\\data\\reaction\\ReactionAction'
                }
            };
        }
    };
    return UiReactionHandler;
});
