define(["require", "exports", "tslib", "../../Alignment", "../../CloseOverlay"], function (require, exports, tslib_1, Alignment, CloseOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerProvider = exports.getContainer = void 0;
    Alignment = (0, tslib_1.__importStar)(Alignment);
    CloseOverlay_1 = (0, tslib_1.__importDefault)(CloseOverlay_1);
    let container = undefined;
    const providers = new Set();
    const views = new Map();
    function initProvider(provider) {
        providers.add(provider);
        const button = provider.getPanelButton();
        prepareButton(button);
        button.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (button.classList.contains("open")) {
                close(provider);
            }
            else {
                open(provider);
            }
        });
    }
    function prepareButton(button) {
        const link = button.querySelector("a");
        link.setAttribute("role", "button");
        link.tabIndex = 0;
        link.setAttribute("aria-haspopup", "true");
        link.setAttribute("aria-expanded", "false");
    }
    function open(provider) {
        CloseOverlay_1.default.execute();
        const view = getView(provider);
        void view.open();
        const button = provider.getPanelButton();
        button.querySelector("a").setAttribute("aria-expanded", "true");
        button.classList.add("open");
        const element = view.getElement();
        Alignment.set(element, button, { horizontal: "right" });
    }
    function close(provider) {
        if (!views.has(provider)) {
            return;
        }
        const button = provider.getPanelButton();
        if (!button.classList.contains("open")) {
            return;
        }
        const view = getView(provider);
        view.close();
        button.classList.remove("open");
        button.querySelector("a").setAttribute("aria-expanded", "false");
    }
    function closeAll() {
        providers.forEach((provider) => close(provider));
    }
    function getView(provider) {
        if (!views.has(provider)) {
            const view = provider.getView();
            const element = view.getElement();
            getContainer().append(element);
            element.addEventListener("shouldClose", () => close(provider));
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
    exports.getContainer = getContainer;
    function registerProvider(provider) {
        if (providers.size === 0) {
            CloseOverlay_1.default.add("WoltLabSuite/Ui/User/Menu", () => closeAll());
        }
        initProvider(provider);
    }
    exports.registerProvider = registerProvider;
});
