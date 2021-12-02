define(["require", "exports", "tslib", "./View", "../../Alignment", "../../CloseOverlay"], function (require, exports, tslib_1, View_1, Alignment, CloseOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerProvider = void 0;
    View_1 = (0, tslib_1.__importDefault)(View_1);
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
    function registerProvider(provider) {
        if (providers.size === 0) {
            CloseOverlay_1.default.add("WoltLabSuite/Ui/User/Menu", () => {
                providers.forEach((provider) => close(provider));
            });
        }
        initProvider(provider);
    }
    exports.registerProvider = registerProvider;
});
