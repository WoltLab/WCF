/**
 * Handles the JavaScript part of the rating form field.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Controller/Rating
 * @since	5.2
 */
define(['Dictionary', 'Environment'], function (Dictionary, Environment) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldRating(fieldId, value, activeCssClasses, defaultCssClasses) {
        this.init(fieldId, value, activeCssClasses, defaultCssClasses);
    }
    ;
    FormBuilderFieldRating.prototype = {
        /**
         * Initializes the rating form field.
         *
         * @param	{string}	fieldId			id of the relevant form builder field
         * @param	{integer}	value			current value of the field
         * @param	{string[]}	activeCssClasses	CSS classes for the active state of rating elements
         * @param	{string[]}	defaultCssClasses	CSS classes for the default state of rating elements
         */
        init: function (fieldId, value, activeCssClasses, defaultCssClasses) {
            this._field = elBySel('#' + fieldId + 'Container');
            if (this._field === null) {
                throw new Error("Unknown field with id '" + fieldId + "'");
            }
            this._input = elCreate('input');
            this._input.id = fieldId;
            this._input.name = fieldId;
            this._input.type = 'hidden';
            this._input.value = value;
            this._field.appendChild(this._input);
            this._activeCssClasses = activeCssClasses;
            this._defaultCssClasses = defaultCssClasses;
            this._ratingElements = new Dictionary();
            var ratingList = elBySel('.ratingList', this._field);
            ratingList.addEventListener('mouseleave', this._restoreRating.bind(this));
            elBySelAll('li', ratingList, function (listItem) {
                if (listItem.classList.contains('ratingMetaButton')) {
                    listItem.addEventListener('click', this._metaButtonClick.bind(this));
                    listItem.addEventListener('mouseenter', this._restoreRating.bind(this));
                }
                else {
                    this._ratingElements.set(~~elData(listItem, 'rating'), listItem);
                    listItem.addEventListener('click', this._listItemClick.bind(this));
                    listItem.addEventListener('mouseenter', this._listItemMouseEnter.bind(this));
                    listItem.addEventListener('mouseleave', this._listItemMouseLeave.bind(this));
                }
            }.bind(this));
        },
        /**
         * Saves the rating associated with the clicked rating element.
         *
         * @param	{Event}		event	rating element `click` event
         */
        _listItemClick: function (event) {
            this._input.value = ~~elData(event.currentTarget, 'rating');
            if (Environment.platform() !== 'desktop') {
                this._restoreRating();
            }
        },
        /**
         * Updates the rating UI when hovering over a rating element.
         *
         * @param	{Event}		event	rating element `mouseenter` event
         */
        _listItemMouseEnter: function (event) {
            var currentRating = elData(event.currentTarget, 'rating');
            this._ratingElements.forEach(function (ratingElement, rating) {
                var icon = elByClass('icon', ratingElement)[0];
                this._toggleIcon(icon, ~~rating <= ~~currentRating);
            }.bind(this));
        },
        /**
         * Updates the rating UI when leaving a rating element by changing all rating elements
         * to their default state.
         */
        _listItemMouseLeave: function () {
            this._ratingElements.forEach(function (ratingElement) {
                var icon = elByClass('icon', ratingElement)[0];
                this._toggleIcon(icon, false);
            }.bind(this));
        },
        /**
         * Handles clicks on meta buttons.
         *
         * @param	{Event}		event	meta button `click` event
         */
        _metaButtonClick: function (event) {
            if (elData(event.currentTarget, 'action') === 'removeRating') {
                this._input.value = '';
                this._listItemMouseLeave();
            }
        },
        /**
         * Updates the rating UI by changing the rating elements to the stored rating state.
         */
        _restoreRating: function () {
            this._ratingElements.forEach(function (ratingElement, rating) {
                var icon = elByClass('icon', ratingElement)[0];
                this._toggleIcon(icon, ~~rating <= ~~this._input.value);
            }.bind(this));
        },
        /**
         * Toggles the state of the given icon based on the given state parameter.
         *
         * @param	{HTMLElement}	icon		toggled icon
         * @param	{boolean}	active		is `true` if icon will be changed to `active` state, otherwise changed to `default` state
         */
        _toggleIcon: function (icon, active) {
            active = active || false;
            if (active) {
                for (var i = 0; i < this._defaultCssClasses.length; i++) {
                    icon.classList.remove(this._defaultCssClasses[i]);
                }
                for (var i = 0; i < this._activeCssClasses.length; i++) {
                    icon.classList.add(this._activeCssClasses[i]);
                }
            }
            else {
                for (var i = 0; i < this._activeCssClasses.length; i++) {
                    icon.classList.remove(this._activeCssClasses[i]);
                }
                for (var i = 0; i < this._defaultCssClasses.length; i++) {
                    icon.classList.add(this._defaultCssClasses[i]);
                }
            }
        }
    };
    return FormBuilderFieldRating;
});
