define(["require", "exports", "tslib", "./Field", "../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Core = tslib_1.__importStar(Core);
    class SimpleAcl extends Field_1.default {
        _getData() {
            const groupIds = Array.from(document.querySelectorAll('input[name="' + this._fieldId + '[group][]"]')).map((input) => input.value);
            const usersIds = Array.from(document.querySelectorAll('input[name="' + this._fieldId + '[user][]"]')).map((input) => input.value);
            return {
                [this._fieldId]: {
                    group: groupIds,
                    user: usersIds,
                },
            };
        }
        _readField() {
            // does nothing
        }
    }
    Core.enableLegacyInheritance(SimpleAcl);
    return SimpleAcl;
});
