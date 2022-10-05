define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getPageOverlayContainer = exports.adoptPageOverlayContainer = void 0;
    const container = document.createElement("div");
    container.id = "pageOverlayContainer";
    document.body.append(container);
    function adoptPageOverlayContainer(element) {
        element.append(container);
    }
    exports.adoptPageOverlayContainer = adoptPageOverlayContainer;
    function getPageOverlayContainer() {
        return container;
    }
    exports.getPageOverlayContainer = getPageOverlayContainer;
});
