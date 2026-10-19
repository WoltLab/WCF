define(["require", "exports", "tslib", "./Field", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Field_1, Util_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class SimpleAcl extends Field_1.default {
        _getData() {
            const groupIds = Array.from(document.querySelectorAll(`input[name="${(0, Util_1.escapeAttributeSelector)(this._fieldId)}[group][]"]`)).map((input) => input.value);
            const usersIds = Array.from(document.querySelectorAll(`input[name="${(0, Util_1.escapeAttributeSelector)(this._fieldId)}[user][]"]`)).map((input) => input.value);
            const allowAll = document.querySelector(`input[name="${(0, Util_1.escapeAttributeSelector)(this._fieldId)}[allowAll]"]:checked`);
            return {
                [this._fieldId]: {
                    allowAll: allowAll?.value ?? "1",
                    group: groupIds,
                    user: usersIds,
                },
            };
        }
        _readField() {
            // does nothing
        }
    }
    return SimpleAcl;
});
