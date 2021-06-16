define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AbstractUserAction = void 0;
    class AbstractUserAction {
        constructor(button, userId, userDataElement) {
            this.button = button;
            this.userId = userId;
            this.userData = userDataElement;
            this.init();
        }
    }
    exports.AbstractUserAction = AbstractUserAction;
    exports.default = AbstractUserAction;
});
