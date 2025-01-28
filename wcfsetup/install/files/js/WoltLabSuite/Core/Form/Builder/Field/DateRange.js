define(["require", "exports", "tslib", "./Field", "../../../Date/Picker"], function (require, exports, tslib_1, Field_1, Picker_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Picker_1 = tslib_1.__importDefault(Picker_1);
    class DateRange extends Field_1.default {
        #fromField;
        #toField;
        constructor(fieldId) {
            super(fieldId);
            this.#fromField = document.getElementById(this._fieldId + "_from");
            if (this.#fromField === null) {
                throw new Error("Unknown field with id '" + this._fieldId + "'.");
            }
            this.#toField = document.getElementById(this._fieldId + "_to");
            if (this.#toField === null) {
                throw new Error("Unknown field with id '" + this._fieldId + "'.");
            }
        }
        _getData() {
            return {
                [this._fieldId]: {
                    from: Picker_1.default.getValue(this.#fromField),
                    to: Picker_1.default.getValue(this.#toField),
                },
            };
        }
        _readField() { }
    }
    return DateRange;
});
