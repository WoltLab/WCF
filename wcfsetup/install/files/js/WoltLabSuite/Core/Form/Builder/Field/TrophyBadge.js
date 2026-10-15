define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class TrophyBadge extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: {
                    iconName: this.#getValue("iconName"),
                    iconColor: this.#getValue("iconColor"),
                    badgeColor: this.#getValue("badgeColor"),
                },
            };
        }
        _readField() { }
        #getValue(name) {
            const input = document.getElementById(`${this._fieldId}_${name}`);
            if (!(input instanceof HTMLInputElement)) {
                throw new Error(`Unknown input field '${name}' of the field '${this._fieldId}'.`);
            }
            return input.value;
        }
    }
    return TrophyBadge;
});
