/**
 * Data handler for a form builder field in an Ajax form that stores its value via a checkbox being
 * checked or not.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Checked
 * @since	5.2
 */
define(['Core', './Field'], function (Core, FormBuilderField) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldInput(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldInput, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
         */
        _getData: function () {
            var data = {};
            data[this._fieldId] = ~~this._field.checked;
            return data;
        }
    });
    return FormBuilderFieldInput;
});
