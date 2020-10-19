/**
 * Data handler for a tag form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Tag
 * @since	5.2
 */
define(['Core', './Field', 'WoltLabSuite/Core/Ui/ItemList'], function (Core, FormBuilderField, UiItemList) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldTag(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldTag, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
         */
        _getData: function () {
            var data = {};
            data[this._fieldId] = [];
            var values = UiItemList.getValues(this._fieldId);
            for (var i = 0, length = values.length; i < length; i++) {
                data[this._fieldId].push(values[i].value);
            }
            return data;
        }
    });
    return FormBuilderFieldTag;
});
