define(["require", "exports", "tslib", "./Value"], function (require, exports, tslib_1, Value_1) {
    "use strict";
    Value_1 = tslib_1.__importDefault(Value_1);
    class GoogleMaps extends Value_1.default {
        _getData() {
            const map = document.getElementById(this._fieldId + "_map");
            return {
                [this._fieldId]: this._field.value,
                [this._fieldId + "_coordinates"]: `${map.lat},${map.lng}`,
            };
        }
    }
    return GoogleMaps;
});
