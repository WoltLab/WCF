/**
 * Data handler for a radio button form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/RadioButton
 * @since	5.2
 */
define(['Core', './Field'], function (Core, FormBuilderField) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldRadioButton(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldRadioButton, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#getData
         */
        _getData: function () {
            var data = {};
            for (var i = 0, length = this._fields.length; i < length; i++) {
                if (this._fields[i].checked) {
                    data[this._fieldId] = this._fields[i].value;
                    break;
                }
            }
            return data;
        },
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_readField
         */
        _readField: function () {
            this._fields = elBySelAll('input[name=' + this._fieldId + ']');
        },
    });
    return FormBuilderFieldRadioButton;
});
