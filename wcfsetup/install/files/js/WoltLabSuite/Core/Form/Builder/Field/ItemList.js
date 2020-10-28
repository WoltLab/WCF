/**
 * Data handler for an item list form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/ItemList
 * @since	5.2
 */
define(['Core', './Field', 'WoltLabSuite/Core/Ui/ItemList/Static'], function (Core, FormBuilderField, UiItemListStatic) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldItemList(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldItemList, FormBuilderField, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#_getData
         */
        _getData: function () {
            var data = {};
            data[this._fieldId] = [];
            var values = UiItemListStatic.getValues(this._fieldId);
            for (var i = 0, length = values.length; i < length; i++) {
                if (values[i].objectId) {
                    data[this._fieldId][values[i].objectId] = values[i].value;
                }
                else {
                    data[this._fieldId].push(values[i].value);
                }
            }
            return data;
        }
    });
    return FormBuilderFieldItemList;
});
