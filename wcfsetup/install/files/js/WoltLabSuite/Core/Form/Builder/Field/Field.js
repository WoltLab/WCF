/**
 * Data handler for a form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Field
 * @since 5.2
 */
define(["require", "exports", "tslib", "../../../Core"], function (require, exports, tslib_1, Core) {
    "use strict";
    Core = tslib_1.__importStar(Core);
    class Field {
        constructor(fieldId) {
            this.init(fieldId);
        }
        /**
         * Initializes the field.
         */
        init(fieldId) {
            this._fieldId = fieldId;
            this._readField();
        }
        /**
         * Returns the current data of the field or a promise returning the current data
         * of the field.
         *
         * @return	{Promise|data}
         */
        _getData() {
            throw new Error("Missing implementation of WoltLabSuite/Core/Form/Builder/Field/Field._getData!");
        }
        /**
         * Reads the field's HTML element.
         */
        _readField() {
            this._field = document.getElementById(this._fieldId);
            if (this._field === null) {
                throw new Error("Unknown field with id '" + this._fieldId + "'.");
            }
        }
        /**
         * Destroys the field.
         *
         * This function is useful for remove registered elements from other APIs like dialogs.
         */
        destroy() {
            // does nothinbg
        }
        /**
         * Returns a promise providing the current data of the field.
         */
        getData() {
            return Promise.resolve(this._getData());
        }
        /**
         * Returns the id of the field.
         */
        getId() {
            return this._fieldId;
        }
    }
    Core.enableLegacyInheritance(Field);
    return Field;
});
