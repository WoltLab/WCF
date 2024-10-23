/**
 * Controls the behavior of the user menus.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Alignment", "../../CloseOverlay", "../../../Event/Handler", "../../../Dom/Util", "../../Screen", "../../../Helper/PageOverlay"], function (require, exports, tslib_1, Alignment, CloseOverlay_1, EventHandler, Util_1, UiScreen, PageOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getUserMenuProviders = getUserMenuProviders;
    exports.updateCounter = updateCounter;
    exports.getContainer = getContainer;
    exports.registerProvider = registerProvider;
    Alignment = tslib_1.__importStar(Alignment);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    Util_1 = tslib_1.__importDefault(Util_1);
    UiScreen = tslib_1.__importStar(UiScreen);
    let container = undefined;
    const providers = new Set();
    const views = new Map();
    function initProvider(provider) {
        providers.add(provider);
        const button = provider.getPanelButton();
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
    function open(provider) {
        CloseOverlay_1.default.execute();
        const view = getView(provider);
        void view.open();
        const button = provider.getPanelButton();
        button.querySelector("a").setAttribute("aria-expanded", "true");
        button.classList.add("open");
        const element = view.getElement();
        setAlignment(element, button);
    }
    function setAlignment(element, referenceElement) {
        Alignment.set(element, referenceElement, { horizontal: "right" });
        if (window.getComputedStyle(element).position === "fixed" && Util_1.default.getFixedParent(referenceElement) !== null) {
            const { top, height } = referenceElement.getBoundingClientRect();
            element.style.setProperty("top", `${top + height}px`);
        }
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
    function getUserMenuProviders() {
        return providers;
    }
    /**
     * @since 6.1
     */
    function updateCounter(identifier, counter) {
        Array.from(providers)
            .filter((provider) => provider.getIdentifier() === identifier)
            .forEach((provider) => {
            provider.getPanelButton().dispatchEvent(new CustomEvent("updateCounter", {
                detail: { counter: counter },
            }));
        });
    }
    function getContainer() {
        if (container === undefined) {
            container = document.createElement("div");
            container.classList.add("dropdownMenuContainer");
            (0, PageOverlay_1.getPageOverlayContainer)().append(container);
        }
        return container;
    }
    function registerProvider(provider) {
        if (providers.size === 0) {
            CloseOverlay_1.default.add("WoltLabSuite/Ui/User/Menu", () => closeAll());
            EventHandler.add("com.woltlab.wcf.UserMenuMobile", "more", (data) => {
                providers.forEach((provider) => {
                    if (data.identifier === provider.getIdentifier()) {
                        open(provider);
                    }
                });
            });
            // Update the position of the user menu if the browser is
            // resized while the menu is visible.
            window.addEventListener("resize", () => {
                providers.forEach((provider) => {
                    const button = provider.getPanelButton();
                    if (button.classList.contains("open")) {
                        const view = getView(provider);
                        setAlignment(view.getElement(), button);
                    }
                });
            }, { passive: true });
            UiScreen.on("screen-md-down", {
                match() {
                    providers.forEach((provider) => {
                        const button = provider.getPanelButton();
                        if (button.classList.contains("open")) {
                            close(provider);
                        }
                    });
                },
                setup() {
                    providers.forEach((provider) => {
                        const button = provider.getPanelButton();
                        if (button.classList.contains("open")) {
                            close(provider);
                        }
                    });
                },
            });
        }
        initProvider(provider);
    }
});
