define(["require", "exports", "tslib", "./Field", "../../../Date/Picker"], function (require, exports, tslib_1, Field_1, Picker_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Picker_1 = tslib_1.__importDefault(Picker_1);
    class Date extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: Picker_1.default.getValue(this._field),
            };
        }
    }
    return Date;
});
