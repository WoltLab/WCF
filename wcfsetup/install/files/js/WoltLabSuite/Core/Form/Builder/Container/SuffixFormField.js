/**
 * Handles the dropdowns of form fields with a suffix.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Container/SuffixFormField
 * @since	5.2
 */
define(['EventHandler', 'Ui/SimpleDropdown'], function (EventHandler, UiSimpleDropdown) {
    "use strict";
    /**
     * @constructor
     */
    function PrefixSuffixFormFieldContainer(formId, suffixFieldId) {
        this._formId = formId;
        this._suffixField = elById(suffixFieldId);
        this._suffixDropdownMenu = UiSimpleDropdown.getDropdownMenu(suffixFieldId + '_dropdown');
        this._suffixDropdownToggle = elByClass('dropdownToggle', UiSimpleDropdown.getDropdown(suffixFieldId + '_dropdown'))[0];
        var listItems = this._suffixDropdownMenu.children;
        for (var i = 0, length = listItems.length; i < length; i++) {
            listItems[i].addEventListener('click', this._changeSuffixSelection.bind(this));
        }
        EventHandler.add('WoltLabSuite/Core/Form/Builder/Manager', 'afterUnregisterForm', this._destroyDropdown.bind(this));
    }
    ;
    PrefixSuffixFormFieldContainer.prototype = {
        /**
         * Handles changing the suffix selection.
         *
         * @param	{Event}		event
         */
        _changeSuffixSelection: function (event) {
            if (event.currentTarget.classList.contains('disabled')) {
                return;
            }
            var listItems = this._suffixDropdownMenu.children;
            for (var i = 0, length = listItems.length; i < length; i++) {
                if (listItems[i] === event.currentTarget) {
                    listItems[i].classList.add('active');
                }
                else {
                    listItems[i].classList.remove('active');
                }
            }
            this._suffixField.value = elData(event.currentTarget, 'value');
            this._suffixDropdownToggle.innerHTML = elData(event.currentTarget, 'label') + ' <span class="icon icon16 fa-caret-down pointer"></span>';
        },
        /**
         * Destorys the suffix dropdown if the parent form is unregistered.
         *
         * @param	{object}	data	event data
         */
        _destroyDropdown: function (data) {
            if (data.formId === this._formId) {
                UiSimpleDropdown.destroy(this._suffixDropdownMenu.id);
            }
        }
    };
    return PrefixSuffixFormFieldContainer;
});
