define(["require", "exports", "tslib", "./Data/Notification", "./View", "../../Alignment", "../../CloseOverlay"], function (require, exports, tslib_1, Notification_1, View_1, Alignment, CloseOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    View_1 = (0, tslib_1.__importDefault)(View_1);
    Alignment = (0, tslib_1.__importStar)(Alignment);
    CloseOverlay_1 = (0, tslib_1.__importDefault)(CloseOverlay_1);
    let container = undefined;
    const providers = new Set();
    const views = new Map();
    function init() {
        providers.forEach((provider) => {
            const button = document.getElementById(provider.getPanelButtonId());
            if (button === null) {
                throw new Error(`Cannot find a panel button with the id '${provider.getPanelButtonId()}'.`);
            }
            button.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                const view = getView(provider);
                void view.open();
                const element = view.getElement();
                Alignment.set(element, button, { horizontal: "right" });
            });
        });
        CloseOverlay_1.default.add("WoltLabSuite/Ui/User/Menu", () => {
            providers.forEach((provider) => {
                if (views.has(provider)) {
                    getView(provider).close();
                }
            });
        });
    }
    function getView(provider) {
        if (!views.has(provider)) {
            const view = new View_1.default(provider);
            getContainer().append(view.getElement());
            views.set(provider, view);
        }
        return views.get(provider);
    }
    function getContainer() {
        if (container === undefined) {
            container = document.createElement("div");
            container.classList.add("dropdownMenuContainer");
            document.body.append(container);
        }
        return container;
    }
    function setup() {
        if (providers.size === 0) {
            providers.add(new Notification_1.UserMenuDataNotification());
            init();
        }
    }
    exports.setup = setup;
});
