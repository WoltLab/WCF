define(["require", "exports", "tslib", "./Field", "../../../Date/Picker", "../../../Core"], function (require, exports, tslib_1, Field_1, Picker_1, Core) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Picker_1 = tslib_1.__importDefault(Picker_1);
    Core = tslib_1.__importStar(Core);
    class Date extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: Picker_1.default.getValue(this._field),
            };
        }
    }
    Core.enableLegacyInheritance(Date);
    return Date;
});
