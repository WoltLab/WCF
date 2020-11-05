/**
 * Data handler for a button form builder field in an Ajax form.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Form/Builder/Field/Value
 * @since       5.4
 */
define(['Core', './Field'], function (Core, FormBuilderField) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldButton(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldButton, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
         */
        _getData: function () {
            var data = {};
            if (~~this._field.dataset.isClicked) {
                data[this._fieldId] = this._field.value;
            }
            return data;
        }
    });
    return FormBuilderFieldButton;
});
