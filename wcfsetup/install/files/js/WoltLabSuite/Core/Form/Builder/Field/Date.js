/**
 * Data handler for a date form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Date
 * @since	5.2
 */
define(['Core', 'WoltLabSuite/Core/Date/Picker', './Field'], function (Core, DatePicker, FormBuilderField) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldDate(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldDate, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
         */
        _getData: function () {
            var data = {};
            data[this._fieldId] = DatePicker.getValue(this._field);
            return data;
        }
    });
    return FormBuilderFieldDate;
});
